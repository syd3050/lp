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
        /*
        $count = Session::get('count');
        empty($count) && $count = 0;
        $count++;
        Session::set('count',$count);

        return ['cout'=>$count];
        */
        $data = [5=>'a',2=>'b',7=>'c',1=>'d',8=>'e',9=>'f',10=>'g'];
        $heap = new \SplMinHeap();
        foreach ($data as $k=>$v)
        {
            $heap->insert([$k=>$v]);
        }
        $r = '';
        $total = $heap->count();
        while ($total){
            $r .= ','.json_encode($heap->current());
            $heap->next();
            $total--;
        }
        return ['heap'=>ltrim($r)];
    }
}