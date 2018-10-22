<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-19
 * Time: 下午3:04
 */

namespace core\db;


class DB
{

    public static function table($table)
    {
        $model = new DBModel($table);
        return $model;
    }
}