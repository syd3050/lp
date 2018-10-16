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
    private static $instance = null;
    /**
     * @var \SplQueue
     */
    protected $pool;
    protected $config = array(
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
        if(self::$instance == null)
            self::$instance = new RedisPool($config);
        return self::$instance;
    }

    private function __clone(){}

    private function __construct($config = array())
    {
        $this->config = array_merge($this->config,$config);
        $this->pool = new \SplQueue();
        $this->lock = $this->_getConnection();
        $this->_init_pool();
    }

    private function _init_pool()
    {
        $nums = $this->config['init'];
        $max = 100;
        $times = 0;
        while ($nums && $times<$max)
        {
            $redis = $this->_getConnection();
            if($redis != null)
            {
                $this->put($redis);
                $nums--;
            }
            $times++;
        }
        if($nums != 0)
            throw new ServerException("Can not connect redis.");
    }

    private function _getConnection()
    {
        if (extension_loaded('redis')) {
            $redis = new \Redis();
            $connect = empty($this->config['persistent']) ? 'connect' : 'pconnect';
            $connected = $redis->$connect(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
        } else
            throw new ConfigException('Redis should be installed.');
        if($connected)
            return $redis;
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
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['timeout']
                );
            }
            return $redis;
        }catch (\Exception $exception)
        {
            if($this->busy == $this->config['max'])
                throw new ServerException("Redis 连接数已达上限！");
            //无空闲连接，创建新连接
            $redis = $this->_getConnection();
            $random = randStr($this->lock_key,10);
            while (true) {
                if($this->_lock($random))
                    break;
            }
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