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
     * @var SessionDriver
     */
    private static $_instance = null;

    private static function _init()
    {
        if(empty(self::$_instance))
        {
            $config = Config::get(Config::CONFIG,'session');
            if(isset($config['class']) && class_exists($config['class']))
            {
                $class = $config['class'];
                return self::$_instance = new $class($config);
            }else{
                $config = $config ?: [];
                return self::$_instance = new SessionLocal($config);
            }
        }
        return self::$_instance;
    }

    public static function get($key)
    {
        return self::_init()->get($key);
    }
}