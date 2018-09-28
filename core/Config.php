<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-25
 * Time: 下午3:37
 */

namespace core;

/**
 * Class Config
 * 管理框架所有配置文件，使用Yac组件
 *
 * 1.获取普通配置文件配置项使用实例：
 * //获取app/config.php文件中的log配置项信息
 * Config::get('config','log');
 *
 * //获取app/route.php文件中的default_controller配置项信息
 * Config::get('route','default_controller');
 *
 * //获取app/hook.php文件中的pre配置项信息
 * Config::get('hook','pre');
 *
 * 2.获取环境变量配置项使用实例
 * //根据当前环境获取对应的配置项，可能是来自app/env/dev.php，app/env/prod.php或者app/env/test.php，
 * Config::getEnv('register')
 *
 * @package core
 */
class Config
{
    const CONFIG = 'config';
    const ROUTE = 'config';
    const HOOK = 'config';
    const CONFIG = 'config';
    /*
    private static $config_path = ROOT_PATH.'app'.DS.'config.php';
    private static $route_path = ROOT_PATH.'app'.DS.'route.php';
    private static $hook_path = ROOT_PATH.'app'.DS.'hook.php';
    */

    public static function get($key)
    {
        $config = self::_load(self::$_path);
        //Key不能以"."开头或结尾
        $key = trim($key,'.');
        //如果不包含"."，直接返回配置文件数组中key对应项的值
        if(strpos($key,'.') === false )
            return empty($config[$key]) ? null : $config[$key];
        $arr = explode('.',$key);
        $config_item = $config;
        foreach ($arr as $k=>$v)
        {
            if(empty($config_item[$v]))
                return null;
            $config_item = $config_item[$v];
        }
        return $config_item;
    }

    public static function getEnv($key)
    {

    }

    private static function _load($path)
    {
        $config = \Yac::get('config');
        if(empty($config))
        {
            $config = include $path;
            \Yac::set('config',$config);
        }
        return $config;
    }

}