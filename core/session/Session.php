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
     * @var bool
     */
    private static $_init = false;
    /**
     * @var \SessionHandler
     */
    private static $_instance = null;

    private static function _init()
    {
        /*
        if(empty(self::$_instance))
        {
            $config = Config::get(Config::CONFIG,'session');
            if(isset($config['class']) && class_exists($config['class']))
            {
                $class = $config['class'];
                self::$_instance = new $class($config);
            }
            if(self::$_instance != null)
                session_set_save_handler(self::$_instance);
        }
        */
        if(!self::$_init)
        {
            self::$_instance = new SessionRedis();
            $connected = self::$_instance->open('','');
            if(!$connected)
                throw new ConfigException("Can not connect redis,check the configuration please");
            self::session_id();
            self::$_init = true;
        }

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
        if(!self::$_init)
            self::_init();
        $session = self::$_instance->read(self::session_id())?:'';
        $session = json_decode($session,true);
        $r = isset($session[$key]) ? $session[$key] : null;
        return $r;
    }

    public static function set($key,$value)
    {
        if(!self::$_init)
            self::_init();
        $session = self::$_instance->read(self::session_id())?:'';
        $session = json_decode($session,true);
        $session[$key] = $value;
        return self::$_instance->write(self::session_id(),json_encode($session));
    }
}