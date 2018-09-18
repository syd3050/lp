<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午4:44
 */

namespace core\error;


interface IErrorHandle
{

    /**
     * 返回处理后的error
     * @return mixed
     */
    public function handle();
}