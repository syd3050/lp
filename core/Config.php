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
 * //获取整个config配置文件
 * Config::get('config');
 *
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
 * //获取整个配置文件
 * Config::getEnv();
 *
 * Config::getEnv('register')
 *
 * @package core
 */
class Config
{
    /* 对应配置文件的标识，如果添加新对配置文件，这里需要新增对应选项 */
    const CONFIG = 'config';
    const ROUTE = 'route';
    const HOOK = 'hook';
    const JOB = 'job';
    const SQL = 'sql';

    /**
     * 获取普通配置文件配置项
     * @param string $section 对应普通配置文件标识
     * @param string|null $key key为null时取整个配置文件
     * @return null
     */
    public static function get($section,$key=null)
    {
        $config = self::_load($section);
        if(empty($key))
            return $config;
        //为了方便开发，约定：如果本地有$key对应的文件，将直接取该文件内容覆盖
        $config = array_merge($config,self::getFromTmp($key));
        return self::_parse($config,$key);
    }

    /**
     * 为了方便开发，约定：如果本地有$key对应的文件，将直接取该文件内容
     * 这样将项目下载回来后，只需要在/app下新增对应的本地文件即可覆盖config.php,hook.php,job.php,route.php,sql.php等
     * 配置文件中对应的配置项，从而不需要直接修改这些文件，然后提交代码前又还原这些文件。
     * 例如，可以在/app下创建database.php配置文件，其内容可以为：
     return [
         'host' => '',
         'port' => '',
         'name' => '',
         'username' => '',
         'password' => '',
     ]
     * 这样框架将直接使用其配置项驱动数据库连接
     * @param $key
     * @return array
     */
    private static function getFromTmp($key)
    {
        if(empty($key))
            return [];
        $file = APP_PATH."$key.php";
        if(file_exists($file))
        {
            return [$key => include $file];
        }
        return [];
    }

    /**
     * 获取环境变量配置项
     * @param string|null $key key为null时取整个配置文件
     * @return null
     */
    public static function getEnv($key=null)
    {
        $config = self::_loadEnv();
        if(empty($key))
            return $config;
        return self::_parse($config,$key);
    }

    /**
     * 解析key并返回配置数组中对应的配置项
     * @param $config
     * @param $key
     * @return null
     */
    private static function _parse($config,$key)
    {
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

    /**
     * 加载对应文件的配置内容
     * @param $section
     * @return mixed
     */
    private static function _load($section)
    {
        $config = LocalCache::get($section);
        if(empty($config))
        {
            $config = include ROOT_PATH.'app'.DS."{$section}.php";
            LocalCache::set($section,$config);
        }
        return $config;
    }

    private static function _loadEnv()
    {
        $env = $GLOBALS['env'];
        $config = LocalCache::get("{$env}_config");
        if(empty($config))
        {
            $config = include ROOT_PATH.'app'.DS."env".DS."{$env}.php";
            LocalCache::set("{$env}_config",$config);
        }
        return $config;
    }

}