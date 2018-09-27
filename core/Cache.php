<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-27
 * Time: 下午3:22
 */

namespace core;


use core\exception\ConfigException;
use core\exception\ServerException;

class Cache
{
    private static $_instance = null;

    private static function _init()
    {
        if(empty(self::$_instance))
        {
            $type = Config::get("cache.type");
            /*
             * 获取该类型对应的配置项:如果在type下面有对应的config数组，则优先使用config对应配置
             * 否则寻找对应对配置项，例如type=redis，那么寻找redis为key的配置项
             */
            $config = empty($type['config']) ? Config::get($type) : $type['config'];
            $class = "core\\cache\\".ucwords($type)."Cache";
            if(!class_exists($class))
                throw new ConfigException("缓存配置错误");
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
        return self::$_instance->$name($arguments);
    }
}