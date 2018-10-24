<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-19
 * Time: 下午3:04
 */

namespace core\db;

use core\Pool;

class DB
{
    public static $pools = array();

    public static $tt = 0;

    private static $_config = [
        'type'  => 'mysql',
        'pool'  => 'core\\db\\mysql\\MysqlPool',
        'model' => 'core\\db\\mysql\\Mysql',
    ];

    /**
     * @param  string $table
     * @param  array  $config
     * @return DbBase
     */
    public static function table($table,$config=[])
    {
        /**
         * @var DbBase $model
         */
        $model = self::init($config);
        return $model->table($table);
    }

    /**
     * @param array $config
     * @return DbBase
     */
    public static function init($config = [])
    {
        $config = array_merge(self::$_config, $config);
        $type = ucwords($config['type']);
        if(!isset(self::$pools[$type])) {
            var_dump(['init'=>'type:'.$type.',not exitst']);
            self::$pools[$type]['pool'] = [1,2];
        }
        self::$tt++;
        return self::$tt;

        /**
         * 新的配置信息可能需要产生新的池
         */
        $config = array_merge(self::$_config, $config);
        $type = ucwords($config['type']);

        if(!isset(self::$pools[$type])) {
            //需要重新创建
            var_dump(['init'=>'type:'.$type.',not exitst']);
            self::$pools[$type]['pool'] = call_user_func($config['pool'].'::getInstance');
            self::$pools[$type]['pool']->init(__CLASS__);
            self::$pools[$type]['model'] = new $config['model'](self::$pools[$type]['pool']);
        }
        return  self::$pools[$type]['model'];
    }

    /**
     * @param string $type
     * @return Pool
     */
    public static function getPool($type = 'Mysql')
    {
        if(isset(self::$pools[$type])) {
            return self::$pools[$type]['pool'];
        }
        return null;
    }

}