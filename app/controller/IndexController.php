<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-25
 * Time: 下午3:27
 */
namespace app\controller;

use core\BaseController;
use core\session\Session;

class IndexController extends BaseController
{

    public function index()
    {
        Session::set('count',0);
        var_dump(['index=>count:'=>Session::get('count')]);
        return ['count init'=>0];
    }

    public function a()
    {
        $count = Session::get('count');
        empty($count) && $count = 0;
        $count++;
        Session::set('count',$count);

        return ['cout'=>$count];
    }
}