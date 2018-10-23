<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-22
 * Time: 上午11:47
 */

namespace core\db\mysql;

use core\Pool;

final class MysqlPool extends Pool
{
    /**
     * @var MysqlPool
     */
    private static $_instance = null;

    private $_config = array(
      'pool' => [
          'timeout' => 15,  //获取数据库实例超时时间
      ],
      'db' => [
          'host' => '127.0.0.1',
          'port' => 3306,
          'user' => 'root',
          'password'  => '111111',
          'database'  => 'test',
          'charset'   => 'utf8',
          'fetch_mode'=>true,
      ]
    );

    private function __clone(){}

    private function __construct($config = [])
    {
        $this->_config = array_merge($this->_config,$config);
        parent::__construct($this->_config['pool']);
    }

    public static function getInstance($config = [])
    {
        if(!self::$_instance instanceof MysqlPool) {
            self::$_instance = new MysqlPool($config);
        }
        return self::$_instance;
    }

    protected function create()
    {
        go(function() {
            $db = new \Swoole\Coroutine\Mysql();
            $db->connect($this->_config['db']);
            $this->backToPool($db);
        });
    }

    /**
     * 将含有命名参数的SQL转换为 ? 占位符的SQL和对应次序的参数数组
     * 例如 :
     * $sql = select a from table_a where c2=:v2 and c1=:v1
     * $params = ['v1'=>123, 'v2'=>456]
     * 解析后返回：
     * ['select a from table_a where c2=? and c1=?', [456,123]]
     * @param $sql
     * @param $params
     * @return array [$sql,$param]
     */
    private function parseSql($sql,$params)
    {
        //不含命名参数则直接返回
        if(strpos($sql,':') === false) {
            return array($sql,$params);
        }
        $r_params = array();
        $pos_array = array();
        foreach ($params as $pk=>$param) {
            $pos_array[$param] = strpos($sql,":{$pk}");
            $sql = str_replace(":{$pk}",' ? ', $sql);
        }
        asort($pos_array);
        foreach ($pos_array as $k=>$pos) {
            $r_params[] = $k;
        }
        empty($r_params) && $r_params = $params;
        return array($sql,$r_params);
    }

}