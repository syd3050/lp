<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-15
 * Time: 上午10:45
 */

namespace core\cache;

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

    /**
     * @var RedisPool
     */
    private $_pool = null;

    public function __construct($config = [])
    {
        $config = array_merge(self::$_config, $config);
        $this->_pool = RedisPool::getInstance($config);
        $this->_redis = $this->_get_redis();
    }

    public function close()
    {
        if($this->_redis != null)
        {
            $this->_pool->put($this->_redis);
            $this->_redis = null;
        }
    }

    private function _get_redis()
    {
        if ($this->_redis == null)
            return $this->_pool->get();
        return $this->_redis;
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        $r = call_user_func_array([$this->_get_redis(),$name],$arguments);
        $this->close();
        return $r;
    }

}