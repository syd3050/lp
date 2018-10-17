<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-10
 * Time: 下午6:06
 */

namespace core\session;

use core\exception\ServerException;
use core\LocalCache;

/**
 * 基于本地缓存的session
 * @package core\session
 */
class SessionLocal extends \SessionHandler
{
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

    /**
     *
     * @param  string $savePath
     * @param  mixed  $session_name
     * @return bool
     */
    public function open($savePath, $session_name)
    {
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



    public function get($key)
    {
        // TODO: Implement get() method.
        $session_id = $this->getSession_id();
        $session = LocalCache::get($this->session_name.'-'.$session_id);
        //更新访问时间
        if(isset($session[$key]['data']) && $this->isValid($session[$key]))
        {
            $session[$key]['last_visit'] = time();
            return $session[$key]['data'];
        }
        return null;
    }

    public function set($key, $value)
    {
        // TODO: Implement set() method.
        $session_id = $this->getSession_id();
        $session_data = LocalCache::get($this->session_name.'-'.$session_id);
        $session_data[$key] = ['data'=>$value, 'last_visit'=>time()];
        LocalCache::set($this->session_name.'-'.$session_id, $session_data);
    }

    private function isValid($cache)
    {
        if(isset($cache['last_visit']))
        {
            if($this->max_lifetime == -1)
                return true;
            if((time()-$cache['last_visit']) > $this->max_lifetime)
                return false;
            return true;
        }
        return false;
    }

    private function getSession_id()
    {
        if(empty($_COOKIE[$this->session_name]))
        {
            while (!empty(LocalCache::get($this->session_name.'-'.($sid = uuid($this->session_name)))))
            {
                usleep(100);
            }
            return $sid;
        }
        return $_COOKIE[$this->session_name];
    }

    function del($key)
    {
        // TODO: Implement del() method.
        $session_id = $this->getSession_id();
        $session_data = LocalCache::get($this->session_name.'-'.$session_id);
        if(isset($session_data[$key])){
            unset($session_data[$key]);
        }
        LocalCache::set($this->session_name.'-'.$session_id,$session_data);
    }

    public function clear()
    {
        // TODO: Implement clear() method.
        $session_id = $this->getSession_id();
        LocalCache::del($this->session_name.'-'.$session_id);
    }


}