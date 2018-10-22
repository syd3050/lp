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

    public function _init()
    {
        $min = $this->_config['min'];
        while ($this->_count <= $min) {
            $instance = $this->create();
            $this->backToPool($instance);
            $this->_count++;
        }
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
            return $this->_connections->pop($timeout);
        }
        $instance = $this->create();
        $this->_count++;
        return $instance;
    }

    protected function backToPool($instance)
    {
        $this->_connections->push($instance);
    }

    protected function poolSize()
    {
        return $this->_connections->length();
    }

    public function gc()
    {
        //5分钟检测一次
        swoole_timer_tick(300000, function () {
            $list = [];
            //请求连接数还比较多，暂不回收空闲连接
            if ($this->_connections->length() < intval($this->_config['max'] * 0.5)) {
                return ;
            }
            $min = $this->_config['min'];
            $free_ttl = $this->_config['free_ttl'];
            while (true) {
                if (!$this->_connections->isEmpty()) {
                    $obj = $this->_connections->pop(0.001);
                    $last_used_time = $obj['last_used_time'];
                    //回收
                    if ($this->_count > $min && (time() - $last_used_time > $free_ttl)) {
                        $this->_count--;
                    } else {
                        array_push($list, $obj);
                    }
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->_connections->push($item);
            }
            unset($list);
        });
    }
}