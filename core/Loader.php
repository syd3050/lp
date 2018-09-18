<?php
namespace core;


class Loader
{
	public static $map = [];

	// 自动加载
    public static function autoload($class)
    {
        if ($file = self::findFile($class)) {
            include_once $file;
            return true;
        }
        return false;
    }
	
	/**
     * 查找文件
     * @param $class
     * @return bool
     */
    private static function findFile($class)
    {
        if (!empty(self::$map[$class])) {
            // 类库映射
            return self::$map[$class];
        }
        //替换为当前系统路径分隔符
	    $class = strtr($class, '\\', DS);
        $file = $class . '.php';
        if (is_file($file)) {
            self::$map[$class] = $file;
            return $file;
        }

        return self::$map[$class] = false;
    }

     // 注册自动加载机制
    public static function register($autoload = '')
    {
        // 注册系统自动加载
        spl_autoload_register($autoload ?: 'core\\Loader::autoload', true, true);
    }
}
