<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-12
 * Time: 下午1:50
 */

namespace core\session;


class SessionRedis implements SessionDriver
{
    public function __construct()
    {
        ini_set("session.save_handler",'redis');
        ini_set("session.save_path", "tcp://127.0.0.1:6379");
        if(!isset($_SESSION))
            session_start();
    }

    function clear()
    {
        // TODO: Implement clear() method.
    }

    function gc(int $maxLifetime)
    {
        // TODO: Implement gc() method.
    }

    function get($key)
    {
        // TODO: Implement get() method.
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    function set($key, $value)
    {
        // TODO: Implement set() method.
        $_SESSION[$key] = $value;
    }

    function del($key)
    {
        // TODO: Implement del() method.
    }
}