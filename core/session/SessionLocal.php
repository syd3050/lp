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

    public function __construct($config=[])
    {
        if(isset($config['session_name']))
            $this->session_name = $config['session_name'];
    }

    public function get($key)
    {
        // TODO: Implement get() method.
        $session_id = $this->getSession_id();
        $session = LocalCache::get($this->session_name.'-'.$session_id);
        $value = isset($session[$key]) ? $session[$key] : null;
        return $value;
    }

    public function set($key, $value)
    {
        // TODO: Implement set() method.
        $session_id = $this->getSession_id();
        $session_data = LocalCache::get($this->session_name.'-'.$session_id);
        $session_data[$key] = $value;
        LocalCache::set($this->session_name.'-'.$session_id,$session_data);
    }

    private function getSession_id()
    {
        if(empty($_COOKIE[$this->session_name]))
            throw new ServerException("Cookie 不存在{$this->session_name}");
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