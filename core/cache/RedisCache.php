<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-27
 * Time: 下午3:36
 */

namespace core\cache;


/**
 * Class RedisCache
 *
 * 需要安装一个第三方的异步Redis库hiredis:
 * sudo make
 * sudo make install
 * sudo ldconfig
 * 需要在编译swoole时增加--enable-async-redis来开启此功能
 * 请勿同时使用异步回调和协程Redis
 *
 * @package core\cache
 */
class RedisCache implements CacheDriver
{

    /**
     * @var null|\Swoole\Coroutine\Redis
     */
    private static $_redis = null;

    public function __construct($config)
    {
        if(empty(self::$_redis))
        {
            self::$_redis = new RedisDecorator($config);
        }
    }

    public function get($key,$default='')
    {
        // TODO: Implement get() method.
        $rv = self::$_redis->get($key);
        return empty($rv) ? $default : $rv;
    }

    public function set($key,$value)
    {
        // TODO: Implement set() method.
        return self::$_redis->set($key,$value);
    }

    /**
     * @param string|string[] $keys
     * @return int
     */
    public function del($keys)
    {
        // TODO: Implement del() method.
        return self::$_redis->del($keys);
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }
}