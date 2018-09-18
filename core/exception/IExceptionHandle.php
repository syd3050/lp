<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午5:35
 */

namespace core\exception;


interface IExceptionHandle
{
    /**
     * 返回处理后的exception
     * @return mixed
     */
    public function handle();
}