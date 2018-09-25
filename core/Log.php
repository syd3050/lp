<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-25
 * Time: 下午3:35
 */
namespace core;

use core\exception\ConfigException;
use core\exception\ServerException;
use core\log\ILog;

/**
 * Class Log
 *
 * @method void info($msg)    static
 * @method void debug($msg)   static
 * @method void warning($msg) static
 * @method void error($msg)   static
 *
 * @package core
 */
class Log
{
    private static $_driver = null;
    private static $_dirver_type = null;

    private static function _init($method)
    {
        empty(self::$_dirver_type) && self::$_dirver_type = Config::get('log.type');
        if(empty(self::$_dirver_type))
            throw new ConfigException("Log type can not be empty! \n");
        $class = "\\core\\\log\\".ucwords(self::$_dirver_type)."Log";
        if(!class_exists($class))
            throw new ConfigException("$class not exists! \n");
        self::$_driver = new $class($method);
    }

    public static function record($message, $type)
    {
        empty(self::$_driver) && self::_init($type);


    }

    public static function __callStatic($method,$args)
    {
        if(!in_array($method,ILog::LEVEL_ARR))
            throw new ServerException("{$method} not exists.");
        call_user_func_array("\\core\\Log::record",[$args,$method]);
    }

}