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
    private static $_instance = null;
    private static $_session_name = 'PHPSESSID';
    private static $_save_path = '';

    /**
     * @return \SessionHandler
     */
    private static function _init()
    {
        if(self::$_instance == null)
        {
            $config = Config::get(Config::CONFIG,'session');
            if(isset($config['session_name']))
                self::$_session_name = $config['session_name'];
            if(isset($config['save_path']))
                self::$_save_path = $config['save_path'];
            if(isset($config['class']) && class_exists($config['class'])) {
                $class = $config['class'];
                self::$_instance = new $class();
            }else{
                //self::$_instance = new SessionRedis();
                self::$_instance = new SessionLocal();
            }
            self::$_instance->open(self::$_save_path,self::$_session_name);
        }
        return self::$_instance;
    }

    public static function session_id()
    {
        $config = Config::get(Config::CONFIG,'session');
        $session_name = isset($config['session_name']) ? $config['session_name'] : 'PHPSESSID';
        if(!isset($_COOKIE[$session_name]))
        {
            $_COOKIE[$session_name] = randStr('session_',26);
        }
        return $_COOKIE[$session_name];
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

    public static function del($key)
    {
        $session = self::_init()->read(self::session_id())?:'';
        $session = json_decode($session,true);
        unset($session[$key]);
        return self::_init()->write(self::session_id(),json_encode($session));
    }

    /**
     * 删除本次会话
     * @return bool
     */
    public static function destroy()
    {
        return self::_init()->destroy(self::session_id());
    }

    public static function close()
    {
        return self::_init()->close();
    }
}