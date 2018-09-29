<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-28
 * Time: 下午3:28
 */

namespace core;

use core\exception\ConfigException;
use core\exception\ServerException;

/**
 * Class LocalCache
 *
 * @method mixed get($key,$default='') static
 * @method mixed set($key,$value) static
 *
 * @package core
 */
class LocalCache
{
    //本机内存扩展组件,默认使用Yac
    private static $_config = [
        'type'   => 'yac',
        'config' => []
    ];

    private static $_instance = null;

    private static function _init()
    {
        if(empty(self::$_instance))
        {
            $type = self::$_config['type'];
            $config = empty($type['config']) ? [] : $type['config'];
            $class = "core\\local\\".ucwords($type)."Cache";
            //dev_dump(['LocalCache'=>$class]);
            if(!class_exists($class))
                throw new ConfigException("缓存配置错误,{$class}不存在");
            return new $class($config);
        }
        return self::$_instance;
    }

    public static function __callStatic($name, $arguments)
    {
        // TODO: Implement __callStatic() method.
        self::$_instance = self::_init();
        if(!is_callable([self::$_instance,$name]))
            throw new ServerException("方法$name 不存在");
        return call_user_func_array([self::$_instance,$name],$arguments);
    }
}