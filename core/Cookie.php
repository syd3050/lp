<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-18
 * Time: 下午9:21
 */

namespace core;

/**
 * Class Cookie
 * Set-Cookie: "name=value;domain=.domain.com;path=/;expires=Sat, 11 Jun 2016 11:29:42 GMT;HttpOnly;secure"
 * name:一个唯一的cookie名称，不区分大小写
 * domain:cookie有效域。这个值可以包含子域
 * path：cookie影响路径，浏览器跟会根据这项配置，向指定域中匹配的路径发送cookie
 * expires:有效时间，GMT时间格式
 * HttpOnly：不允许通过脚本访问修改cookie
 * secure: 安全标志，只有在使用https时才发送到服务器
 *
 * @package core
 */
class Cookie
{
    public static function add($name,$value,$expires='',$domain='',$path='')
    {
        if(empty($name) || empty($value)) {
            return false;
        }
        $str = "{$value};";
        if(!empty($domain)) {
            $str .= "domain={$domain};";
        }
        if(!empty($path)) {
            $str .= "path={$domain};";
        }
        if(!empty($expires)) {
            $str .= gmdate('D, d M Y H:i:s T',$expires).';';
        }
        $_COOKIE[$name] = $str;
        return true;
    }

    public static function del($name)
    {
        unset($_COOKIE[$name]);
    }
}