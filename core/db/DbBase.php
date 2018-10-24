<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-23
 * Time: 下午1:55
 */

namespace core\db;


use core\Pool;

abstract class DbBase
{
    /**
     * @var Pool
     */
    protected $_pool;
    protected $_table;
    protected $_pk = 'id';
    protected $_where = '';
    protected $_params = array();
    protected $_columns = ' * ';
    protected $_order_by = '';
    protected $_group_by = '';
    protected $_limit_offset = -1;
    protected $_limit_len = 0;
    protected $_having = '';
    protected $_type = '';
    const TIME_OUT = 10;

    public function pk($pk)
    {
        $this->_pk = $pk;
        return $this;
    }

    public function __construct(&$pool)
    {
        $this->_pool = $pool;
    }

    abstract protected function _parse_params($params);

    abstract protected function _buildSql();

    abstract protected function _reconnect($db);

    /**
     * @param array $conditions 条件命名数组
     * @param array $params     新值命名数组
     * @return bool
     */
    abstract public function update($conditions , $params);

    /***************************************************************
     * 直接执行SQL语句，SQL语句可带 ? 占位符，或者命名参数
     * 例如：
     * 1:
     * SQL: select * from ta where c1=? and c2=?
     * 参数: [5,8]
     * 2:
     * SQL: select * from ta where c1=:c1 and c2=:c2;
     * 参数:['c1'=>5,'c2'=>8]
     * 当参数不多时，推荐使用第一种?占位符
     *
     * @param string $sql    SQL语句可带 ? 占位符，或者命名参数
     * @param array $params  键为数字的数组或命名参数数组
     * @param float $timeout 超时时间，单位秒，可使用小数设为毫秒：0.001
     * @return array|bool|null
     * *************************************************************
     */
    abstract public function execute($sql,$params=[],$timeout=-1);

    /***************************************************************
     * @param string $sql  不允许带?占位符或者命名参数
     * @param int $timeout 超时时间，$timeout如果小于或等于0，表示永不超时。在规定的时间内MySQL服务器未能返回数据，底层将返回false，设置错误码为110，并切断连接
     * @return mixed
     * *************************************************************
     */
    abstract public function query($sql,$timeout = -1);

    abstract public function save($data);

    public function table($table)
    {
        $this->_table = $table;
        return $this;
    }

    public function columns($columns='')
    {
        if(!empty($columns))
            $this->_columns = $columns;
        return $this;
    }

    public function findById($id, $columns='')
    {
        return $this->columns($columns)->where("{$this->_pk}= ? ",[$id])->exec();
    }

    public function findOne($params, $columns='')
    {
        list($condition,$param_arr) = $this->_parse_params($params);
        return $this->columns($columns)->where($condition,$param_arr)->limit(1,0)->exec();
    }

    public function findAll($params, $columns='')
    {
        list($condition,$param_arr) = $this->_parse_params($params);
        return $this->columns($columns)->where($condition,$param_arr)->exec();
    }

    public function exec()
    {
        $sql = $this->_buildSql();
        return $this->execute($sql,$this->_params);
    }

    public function getSql()
    {
        return $this->_buildSql();
    }

    public function having($str)
    {
        $this->_having = $str;
    }

    public function orderBy($orderBy)
    {
        $this->_order_by = $orderBy;
        return $this;
    }

    public function groupBy($str)
    {
        $this->_group_by = $str;
        return $this;
    }

    public function limit($len,$offset = 0)
    {
        $this->_limit_offset = $offset;
        $this->_limit_len = $len;
        return $this;
    }

    public function select($columns)
    {
        $this->_columns = $columns;
        return $this;
    }

    public function where($condition,$params=[])
    {
        $this->_where .= $condition;
        $this->_params = array_merge($this->_params,$params);
        return $this;
    }

    public function params($params)
    {
        $this->_params = array_merge($this->_params,$params);
        return $this;
    }

}