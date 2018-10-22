<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-22
 * Time: 上午11:47
 */

namespace core\db;

use core\Pool;

final class MysqlPool extends Pool implements IDB
{
    /**
     * @var MysqlPool
     */
    private static $_instance = null;

    private $_config = array(
      'pool' => [
          'timeout' => 15,  //获取数据库实例超时时间
      ],
      'mysql' => [
          'host' => '127.0.0.1',
          'port' => 3306,
          'user' => 'user',
          'password'  => 'pass',
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
        // TODO: Implement create() method.
        $db = new \Swoole\Coroutine\Mysql();
        $db->connect($this->_config['mysql']);
        return $db;
    }

    /**
     * @param $sql
     * @param int $timeout 超时时间，$timeout如果小于或等于0，表示永不超时。在规定的时间内MySQL服务器未能返回数据，底层将返回false，设置错误码为110，并切断连接
     * @return mixed
     */
    public function query($sql,$timeout = -1)
    {
        $db = $this->getFromPool();
        $db->setDefer();
        $r = $db->query($sql, $timeout);
        if(!$r) {
            //重连
            if(!($db = $this->_reconnect($db)))
                return null;
        }
        $r = $db->recv();
        if(!$r) {
            //重连
            if(!($db = $this->_reconnect($db)))
                return null;
            $r = $db->recv();
        }
        $this->backToPool($db);
        return $r;
    }

    public function execute($sql,$params=[],$timeout=-1)
    {
        list($sql,$params) = $this->parseSql($sql,$params);
        /**
         * @var $db \Swoole\Coroutine\Mysql
         */
        $db = $this->getFromPool();
        /**
         * @var $stmt \Swoole\Coroutine\Mysql\Statement
         */
        $stmt = $db->prepare($sql);
        if($stmt == false) {
            //重连
            if(!($db = $this->_reconnect($db)))
                return null;
            $stmt = $db->prepare($sql);
        }
        $r = $stmt->execute($params,$timeout);
        $this->backToPool($db);
        return  $r;
    }

    /**
     * 重连
     * @param  \Swoole\Coroutine\Mysql $db
     * @return \Swoole\Coroutine\Mysql | bool
     */
    private function _reconnect($db)
    {
        if ($db->errno == 2006 or $db->errno == 2013)
        {
            $times = $this->poolSize() + 1;
            do{
                $db = $this->getFromPool();
                $times--;
            }while(!$db && $times);
            return $db;
        }
        return false;
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