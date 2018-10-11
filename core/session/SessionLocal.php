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
class SessionLocal implements SessionDriver
{
    private $session_name = 'PHPSESSID';
    private $max_lifetime = 3600;

    public function __construct($config=[])
    {
        if(isset($config['session_name']))
            $this->session_name = $config['session_name'];
        if(isset($config['max_lifetime']))
            $this->max_lifetime = intval($config['max_lifetime']);
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

    public function gc(int $maxLifetime)
    {
        // TODO: Implement gc() method.
    }

}