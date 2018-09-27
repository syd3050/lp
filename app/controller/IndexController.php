<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-25
 * Time: 下午3:27
 */
namespace app\controller;

use core\BaseController;

class IndexController extends BaseController
{

    public function index()
    {
        return ['a'=>1,'b'=>3];
    }

    public function a()
    {
        //json_encode()
    }
}