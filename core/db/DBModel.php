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

    }

    public function getSql()
    {

    }

    private function _buildSql()
    {
        $sql = "SELECT {$this->_columns} FROM {$this->_table} WHERE {$this->_where} ";
        return $sql;
    }

    public function orderBy($orderBy)
    {
        $this->_order_by = $orderBy;
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