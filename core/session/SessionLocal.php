<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-10
 * Time: 下午6:06
 */

namespace core\session;

use core\Cache;
use core\exception\ServerException;
use core\LocalCache;
use http\Exception\RuntimeException;

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
        $this->open('',$this->session_name);
    }

    function get($key)
    {
        // TODO: Implement get() method.
        $session_id = $this->getSession_id();
        $session = $this->read($session_id);
        $value = isset($session[$key]) ? $session[$key] : null;
        return $value;
    }

    function set($key, $value)
    {
        // TODO: Implement set() method.
        $session_id = $this->getSession_id();
        $this->write($session_id,[$key=>$value]);
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
        $session_data = $this->read($session_id);
        if(isset($session_data[$key])){
            unset($session_data[$key]);
        }
        $this->write($session_id,$session_data);
    }

    function close()
    {
        // TODO: Implement close() method.
    }

    function destroy(string $session_id)
    {
        // TODO: Implement destroy() method.
        LocalCache::del($this->session_name.'-'.$session_id);
    }

    function gc(int $maxLifetime)
    {
        // TODO: Implement gc() method.
    }

    function open(string $save_path, string $session_name)
    {
        // TODO: Implement open() method.
        LocalCache::set($session_name,[]);
    }

    function read(string $session_id)
    {
        // TODO: Implement read() method.
    }

    /**
     * @param string $session_id
     * @param array $session_data
     */
    function write(string $session_id, array $session_data)
    {
        // TODO: Implement write() method.
    }
}