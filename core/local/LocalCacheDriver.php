<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-28
 * Time: 下午4:24
 */
namespace core\local;

interface LocalCacheDriver
{
    public function get($key,$default='');
    public function set($key,$value);
    public function del($key);
    public function clear();
}