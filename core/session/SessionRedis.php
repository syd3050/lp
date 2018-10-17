<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-12
 * Time: 下午1:50
 */

namespace core\session;

use core\cache\RedisDecorator;
use core\Config;

class SessionRedis extends \SessionHandler
{
    /**
     * @var \Redis
     */
    protected $handler = null;
    //1 hours
    const MAX_LIFETIME = 3600;

    protected static $session_config = [
        'session_name'    => 'PHPSESSID',
        'max_lifetime'    => self::MAX_LIFETIME,
        //GC 概率 = gc_probability/gc_divisor ，例如以下配置表明每1000次请求有1次机会清理垃圾，
        //就是将所有“未访问时长”超过maxLifetime的项目清理掉
        'gc_probability ' => 1,
        'gc_divisor'      => 1000,
    ];

    protected static $redis_config  = [];

    /**
     *
     * @param  string $savePath
     * @param  mixed  $session_name
     * @return bool
     */
    public function open($savePath, $session_name)
    {
        if(is_array($savePath) && isset($savePath['host']) && isset($savePath['port'])) {
            self::$redis_config = $savePath;
        }else{
            self::$redis_config = Config::get(Config::CONFIG,'redis');
        }
        self::$session_config['session_name'] = $session_name;
        $this->handler = new RedisDecorator(self::$redis_config);
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close()
    {
        $this->handler->close();
        $this->handler = null;
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param  string $session_id
     * @return string
     */
    public function read($session_id)
    {
        $data = (string) $this->handler->get($session_id);
        if(!empty($data))
        {
            //重置过期时间，使用这个机制实现自动垃圾回收，只要在过期前有访问，就会重置过期时间
            $this->handler->setex($session_id,self::$session_config['max_lifetime'],$data);
        }
        return $data;
    }

    /**
     * 写入Session
     * @access public
     * @param  string $session_id
     * @param  string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        $result = $this->handler->setex(
            $session_id,
            self::$session_config['max_lifetime'],
            $session_data
        );
        return $result ? true : false;
    }

    /**
     * 删除Session
     * @param  string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        return $this->handler->delete($session_id) > 0;
    }

    /**
     * Session 垃圾回收
     * 使用的是redis的setex过期机制维护，不需要垃圾回收
     * @param  string $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}