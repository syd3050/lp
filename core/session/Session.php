<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-11
 * Time: 下午2:15
 */

namespace core\session;


use core\Config;
use core\exception\ConfigException;
use core\Util;

class Session
{
    /**
     * @return \SessionHandler
     * @throws ConfigException
     */
    private static function _init()
    {
        $config = Config::get(Config::CONFIG,'session');
        if(isset($config['class']) && class_exists($config['class'])) {
            $class = $config['class'];
            $instance = new $class($config);
        }else{
            $instance = new SessionRedis();
        }
        $instance->open('','');
        self::session_id();
        return $instance;
    }

    public static function session_id()
    {
        if(!isset($_COOKIE['PHPSESSID']))
        {
            $_COOKIE['PHPSESSID'] = randStr('session_',26);
        }
        return $_COOKIE['PHPSESSID'];
    }

    public static function get($key)
    {
        $session = self::_init()->read(self::session_id())?:'';
        $session = json_decode($session,true);
        $r = isset($session[$key]) ? $session[$key] : null;
        return $r;
    }

    public static function set($key,$value)
    {
        $session = self::_init()->read(self::session_id())?:'';
        $session = json_decode($session,true);
        $session[$key] = $value;
        return self::_init()->write(self::session_id(),json_encode($session));
    }
}