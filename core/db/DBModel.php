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
}