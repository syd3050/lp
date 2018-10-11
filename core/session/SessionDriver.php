<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-11
 * Time: 下午2:02
 */

namespace core\session;


interface SessionDriver
{
    function clear();
    function gc(int $maxLifetime);
    function get($key);
    function set($key,$value);
    function del($key);
}