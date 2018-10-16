<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-15
 * Time: 上午10:45
 */

namespace core\cache;


use core\exception\ConfigException;

class RedisDecorator
{
    private static $_config = [
        'host'     => '127.0.0.1',
        'port'     => '6379',
        'password' => '',
        'timeout'  => 0,
        'select'   => 0,
    ];

    /**
     * @var null|\Swoole\Coroutine\Redis
     */
    private $_redis = null;

    public function __construct($config = [])
    {
        $config = array_merge(self::$_config, $config);
        if(class_exists("\\Swoole\\Coroutine\\Redis")) {
            $this->_redis = new \Swoole\Coroutine\Redis();
            $connected = $this->_redis->connect($config['host'], $config['port'],$config['timeout']);
        } else if (extension_loaded('redis')) {
            $this->_redis = new \Redis();
            $connect = empty($config['persistent']) ? 'connect' : 'pconnect';
            $connected = $this->_redis->$connect($config['host'], $config['port'], $config['timeout']);
        } else
            throw new ConfigException('Redis should be installed.');

        if(!$connected)
            throw new ConfigException('Failed to connect to Redis !');
        if (!empty($config['password']))
            $this->_redis->auth($config['password']);
        if (0 != $config['select'])
            $this->_redis->select($config['select']);
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        return call_user_func_array([$this->_redis,$name],$arguments);
    }

}