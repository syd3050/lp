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
    function close();
    function destroy(string $session_id);
    function gc(int $maxLifetime);

    /**
     * @param string $save_path    默认的保存路径
     * @param string $session_name 默认的参数名（PHPSESSID）
     * @return mixed
     */
    function open(string $save_path , string $session_name );

    /**
     * @param string $session_id
     * @return string|array
     */
    function read(string $session_id );

    /**
     * @param string $session_id
     * @param array  $session_data
     */
    function write(string $session_id , array $session_data );
    function get($key);
    function set($key,$value);
    function del($key);
}