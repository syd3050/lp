<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-12
 * Time: 下午1:50
 */

namespace core\session;

use core\cache\RedisDecorator;

class SessionRedis extends \SessionHandler
{
    /**
     * @var \Redis
     */
    protected $handler = null;

    protected $session_config = [
        'session_name'    => 'PHPSESSID',
        'max_lifetime'    => '3600',  //1 hours
        //GC 概率 = gc_probability/gc_divisor ，例如以下配置表明每1000次请求有1次机会清理垃圾，
        //就是将所有“未访问时长”超过maxLifetime的项目清理掉
        'gc_probability ' => 1,
        'gc_divisor'      => 1000,
    ];

    protected $redis_config  = [
        'host'         => '127.0.0.1',
        'port'         => 6379,
        'password'     => '',
        'select'       => 0,
        'expire'       => 3600, // key有效期(秒)
        'persistent'   => true, // 是否长连接
    ];

    public function __construct(array $config=[])
    {
        $redis_config = isset($config['redis']) ? $config['redis'] : [];
        $session_config = isset($config['session']) ? $config['session'] : [];
        $this->redis_config = array_merge($this->redis_config, $redis_config);
        $this->session_config = array_merge($this->session_config, $session_config);
    }

    /**
     *
     * @param  string $savePath
     * @param  mixed  $session_name
     * @return bool
     */
    public function open($savePath, $session_name)
    {
        $redis_config = $this->redis_config;
        unset($redis_config['expire']);
        $this->handler = new RedisDecorator($redis_config);
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close()
    {
        $this->gc($this->session_config['max_lifetime']);
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
        return (string) $this->handler->get($session_id);
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
        if ($this->redis_config['expire'] > 0) {
            $result = $this->handler->setex($session_id, $this->redis_config['expire'], $session_data);
        } else {
            $result = $this->handler->set($session_id, $session_data);
        }
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
     * @param  string $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}