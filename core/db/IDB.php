<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-22
 * Time: 下午2:06
 */

namespace core\db;


interface IDB
{
    public function query($sql,$timeout = -1);
    public function execute($sql,$params=[],$timeout=-1);
}