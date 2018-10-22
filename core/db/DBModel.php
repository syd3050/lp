<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-19
 * Time: 下午3:27
 */

namespace core\db;


class DBModel
{
    private $_table;
    private $_pk = 'id';
    private $_where = '';
    private $_params = '';
    private $_columns = ' * ';
    private $_order_by = '';
    private $_group_by = '';
    private $_limit_offset = -1;
    private $_limit_len = 0;
    private $_having = '';

    public function __construct($table='')
    {
        $this->_table = $table;
    }

    public function findById($id)
    {

    }

    public function findOne($params)
    {

    }

    public function find($params)
    {

    }

    public function update($params)
    {

    }

    public function exec()
    {
        $sql = $this->_buildSql();
        $db = DB::getInstance();
        return $db->execute($sql,$this->_params);
    }

    public function getSql()
    {
        return $this->_buildSql();
    }

    private function _buildSql()
    {
        $sql = "SELECT {$this->_columns} FROM {$this->_table} WHERE {$this->_where} ";
        if(!empty($this->_group_by)) {
            $sql .= "GROUP BY {$this->_group_by} ";
        }
        if(empty($this->_having)) {
            $sql .= "HAVING {$this->_having} ";
        }
        if(!empty($this->_order_by)) {
            $sql .= "ORDER BY {$this->_order_by} ";
        }
        if($this->_limit_offset != -1) {
            $sql .= "LIMIT {$this->_limit_offset},{$this->_limit_len} ";
        }
        return $sql;
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

    public function where($condition)
    {
        $this->_where = $condition;
        return $this;
    }

    public function params($params)
    {
        $this->_params = $params;
        return $this;
    }
}