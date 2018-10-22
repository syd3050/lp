<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-19
 * Time: 下午3:04
 */

namespace core\db;


use core\Config;
use core\Pool;

class DB
{

    private static $_config = [
        'type' => 'Mysql',
    ];

    public static function table($table)
    {
        $model = new DBModel($table);
        return $model;
    }

    /**
     * @param array $config
     * @return IDB
     */
    public static function getInstance($config = [])
    {
        $db_config = Config::get(Config::CONFIG,'database');
        $config = array_merge(self::$_config, $db_config, $config);
        $class = "core\\db\\".ucwords($config['type'])."Pool";
        return call_user_func([$class,'getInstance']);
    }
}