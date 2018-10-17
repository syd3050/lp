<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-16
 * Time: 上午11:39
 */

namespace core\cache;


use core\exception\ConfigException;
use core\exception\ServerException;

final class RedisPool
{
    /**
     * @var \SplQueue
     */
    protected $pool;
    /**
     * @var array 保存多个池实例
     */
    protected static $pool_array = array();
    protected $pool_config = array();

    protected static $default_config = array(
        'host'       => '127.0.0.1',
        'port'       => '6379',
        'password'   => '',
        'timeout'    => 0,
        'persistent' => 1,
        'max'        => 512,  //最多512个连接
        'init'       => 8     //初始化建立8个连接
    );
    protected $lock;
    protected $lock_key = "stone_lock";
    protected $lock_timeout = 5; //5 seconds
    protected $busy = 0;

    public static function getInstance($config = array())
    {
        if(!isset($config['host']) || !isset($config['port']))
            $config = self::$default_config;
        $pool_key = $config['host'].':'.$config['port'];
        if(isset(self::$pool_array[$pool_key]))
            return self::$pool_array[$pool_key];
        return self::$pool_array[$pool_key] = new RedisPool($config);
    }

    private function __clone(){}

    private function __construct($config = array())
    {
        $this->pool = new \SplQueue();
        $this->pool_config = array_merge(self::$default_config,$config);
        $this->lock = $this->_getConnection();
        $this->_init_pool();
    }

    private function _init_pool()
    {
        $n = $this->pool_config['init'];
        $max = 100;
        $times = 0;
        while ($n && $times<$max)
        {
            $redis = $this->_getConnection();
            if($redis != null)
            {
                $this->put($redis);
                $n--;
            }
            $times++;
        }
        if($n != 0)
            throw new ServerException("Can not connect redis.");
    }

    private function _getConnection()
    {
        if (extension_loaded('redis')) {
            $redis = new \Redis();
            $connect = empty($this->pool_config['persistent']) ? 'connect' : 'pconnect';
            $connected = $redis->$connect(
                $this->pool_config['host'],
                $this->pool_config['port'],
                $this->pool_config['timeout']
            );
        } else
            throw new ConfigException('Redis should be installed.');
        if($connected)
        {
            if (!empty($this->pool_config['password']))
                $redis->auth($this->pool_config['password']);
            if (!empty($this->pool_config['select']))
                $redis->select($this->pool_config['select']);
            return $redis;
        }
        return null;
    }

    public function put($redis)
    {
        $this->pool->push($redis);
    }

    /**
     * 获得连接
     * @return mixed|null|\Redis
     * @throws ConfigException
     * @throws ServerException
     */
    public function get()
    {
        try{
            /**
             * 有空闲连接
             * @var \Redis
             */
            $redis =  $this->pool->pop();
            if($redis->ping() !== '+PONG')
            {
                $redis->connect(
                    $this->pool_config['host'],
                    $this->pool_config['port'],
                    $this->pool_config['timeout']
                );
            }
            return $redis;
        }catch (\Exception $exception)
        {
            if($this->busy == $this->pool_config['max'])
                throw new ServerException("Redis 连接数已达上限！");
            //无空闲连接，创建新连接
            $random = randStr($this->lock_key,10);
            while (true) {
                if($this->_lock($random))
                    break;
            }
            $redis = $this->_getConnection();
            $this->busy++;
            $this->_unlock($random);
            return $redis;
        }
    }

    private function _lock($random)
    {
        if($this->lock == null)
            throw new ServerException("Can not connect redis.");
        return $this->lock->setex($this->lock_key,$this->lock_timeout,$random);
    }

    private function _unlock($token)
    {
        if($this->lock == null)
            throw new ServerException("Can not connect redis.");
        //不能直接del，否则会把其他请求生成的锁删了，应只删自己的锁，推荐用 lua 代码执行删除，因为lua 执行具有原子性。
        $script = <<<EOT
if redis.call("get",KEYS[1]) == ARGV[1]
then
    return redis.call("del",KEYS[1])
else
    return 0
end    
EOT;
        //第一个是脚本，第二个是参数数组，第三个参数表示第二个参数中的前几个是key参数，剩下的都是附加参数
        return $this->lock->eval($script, [$this->lock_key, $token],1);
    }

}