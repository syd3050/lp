<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-27
 * Time: 下午3:37
 */

namespace core\cache;


interface CacheDriver
{
    public function get($key);
    public function set($key,$value);
    public function del($key);
    public function clear();
}