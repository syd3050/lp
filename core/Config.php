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
        self::_load(self::$_path);
        //Key is not allow to start with or end with "."
        $key = trim($key,'.');
        //If key does not contain ".", return directly
        if(strpos($key,'.') == false )
            return self::$_map[$key];
        $arr = explode('.',$key);
        $config_item = self::$_map[$key];
        foreach ($arr as $k=>$v)
        {
            $config_item = $config_item[$v];
        }
        return $config_item;
    }

    private static function _load($path)
    {
        if(empty(self::$_map))
            self::$_map = require_once $path;
    }

}