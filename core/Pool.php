<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-19
 * Time: 下午4:34
 */

namespace core;

use core\exception\ConfigException;
use core\exception\ServerException;
use \Swoole\Coroutine\Channel;

/**
 * 通用的连接池
 */
abstract class Pool
{
    private $_config = array(
        'min'     => 5,
        'max'     => 30,
        'timeout' => 3,
        'free_ttl'=> 3600,  //空闲时间超过1个小时的连接将被回收
        'gc_rate' => 0.8,   //池中空闲连接数超过max*_gc_rate时，才会真正回收
    );

    private $_count = 0;
    private $_connections = null;

    protected abstract function create();

    public function __construct($config)
    {
        $this->_config = array_merge($this->_config, $config);
        if($this->_config['max'] <= $this->_config['min'])
            throw new ConfigException("Configuration error,max could not less than min!");
        $this->_connections = new Channel($this->_config['max']);
    }

    protected function _init()
    {
        $min = $this->_config['min'];
        while ($this->_count <= $min) {
            $instance = $this->create();
            $this->backToPool($instance);
            $this->_count++;
        }
        $this->gc();
        return $this;
    }

    protected function getFromPool($timeout = 0)
    {
        /**
         * 1.连接池不为空，直接从连接池取连接实例返回;
         * 2.连接池为空，且已建立的连接总数超过最大限制，阻塞等待其他地方释放连接，超时返回false
         */
        if(!$this->_connections->isEmpty() || $this->_count >= $this->_config['max']) {
            /*
             * 指定超时时间，浮点型，单位为秒，最小粒度为毫秒，在规定时间内没有生产者push数据，将返回false
             * $timeout参数在4.0.3或更高版本可用
             */
            if($timeout <= 0)
                $timeout = $this->_config['timeout'];
            $r = $this->_connections->pop($timeout);
            if($r) {
                $r = $r['obj'];
            }
            return $r;
        }
        $instance = $this->create();
        $this->_count++;
        return $instance;
    }

    protected function backToPool($instance)
    {
        $this->_connections->push([
            'obj'=>$instance,'last_access'=>time()
        ]);
    }

    protected function poolSize()
    {
        return $this->_connections->length();
    }

    private function gc()
    {
        //5分钟检测一次
        swoole_timer_tick(300000, function () {
            $list = [];
            /* 池中空闲连接数超过max*_gc_rate时，才会真正回收 */
            if ($this->_connections->length() <= intval($this->_config['max'] * $this->_config['_gc_rate'])) {
                return ;
            }
            $min = $this->_config['min'];
            $free_ttl = $this->_config['free_ttl'];
            while (true) {
                if($this->_connections->isEmpty())
                    break;
                $obj = $this->_connections->pop(0.01);
                //超时，认为池为空，瞬间才有连接归池,这种情况直接退出循环
                if(!$obj)
                    break;
                $last_access = $obj['last_access'];
                //回收
                if ($this->_count > $min && (time() - $last_access > $free_ttl)) {
                    $this->_count--;
                } else {
                    array_push($list, $obj);
                }
            }
            foreach ($list as $item) {
                $this->_connections->push($item);
            }
            unset($list);
        });
    }
}