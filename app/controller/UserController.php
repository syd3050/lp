<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-29
 * Time: 下午4:08
 */

namespace app\controller;


use core\BaseController;

class UserController extends BaseController
{
    public function all()
    {
        return [
            ['id'=>1,'name'=>'Peter','pwd'=>'jKwefui'],
            ['id'=>2,'name'=>'Jim','pwd'=>'rtrtj'],
        ];
    }
}