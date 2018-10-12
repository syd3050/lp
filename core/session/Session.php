<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-11
 * Time: 下午2:15
 */

namespace core\session;


use core\Config;

class Session
{
    /**
     * @var bool
     */
    private static $_init = false;

    private static function _init()
    {
        if(!self::$_init)
        {
            $config = Config::get(Config::CONFIG,'session');
            /**
             * @var \SessionHandler
             */
            $instance = null;
            if(isset($config['class']) && class_exists($config['class']))
            {
                $class = $config['class'];
                $instance = new $class($config);
            }else{
                $config = $config ?: [];
                $instance = new SessionLocal($config);
            }
            session_set_save_handler($instance);
            self::$_init = true;
        }
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
    }

    public static function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public static function set($key,$value)
    {
        $_SESSION[$key] = $value;
    }
}