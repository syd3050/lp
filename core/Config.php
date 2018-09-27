<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-25
 * Time: 下午3:37
 */

namespace core;


class Config
{
    private static $_path = ROOT_PATH.'app'.DS.'config.php';
    private static $_map = [];

    public static function get($key)
    {
        self::$_map = self::_load(self::$_path);
        //Key不能以"."开头或结尾
        $key = trim($key,'.');
        //如果不包含"."，直接返回配置文件数组中key对应项的值
        if(strpos($key,'.') === false )
            return empty(self::$_map[$key]) ? null : self::$_map[$key];
        $arr = explode('.',$key);
        $config_item = self::$_map;
        foreach ($arr as $k=>$v)
        {
            if(empty($config_item[$v]))
                return null;
            $config_item = $config_item[$v];
        }
        return $config_item;
    }

    private static function _load($path)
    {
        if(empty(self::$_map))
            self::$_map = include $path;
        return self::$_map;
    }

}