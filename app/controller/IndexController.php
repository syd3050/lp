<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-25
 * Time: 下午3:27
 */
namespace app\controller;

use app\model\User;
use core\BaseController;
use core\Container;
use core\db\DB;
use core\NContainer;
use core\session\Session;


class IndexController extends BaseController
{
    protected $_connections = null;

    public function index()
    {
        Session::set('count',0);
        return ['count init'=>0];
    }

    public function ct()
    {
        $container = Container::getContainer();

        //$container->bind('user','app\\model\\User');
        /*
        $container->singleton('user',function (){
           return new User(randStr());
        });
        */
        $container->singleton('user','app\\model\\User');
        $user = $container->make('user',[randStr()]);
        $user2 = $container->make('user');
        return ['name'=>$user->getName().','.$user2->getName()];
    }

    public function backToPool($db)
    {
        $this->_connections->push([
            'obj'=>$db,'last_access'=>time()
        ]);
    }

    protected function save($data)
    {
        if(empty($data))
            return false;
        //批量保存
        if(dimension($data) > 1) {
            $one = $data[key($data)];
            $keys = array_keys($one);
            $columns = '('. implode(',',$keys) . ')';
            $columns_num = count($keys);
            $values = [];
            $tokens = array_fill(0,$columns_num,'?');
            $str = '('. implode(',',$tokens). ')';
            $values_str = '';
            foreach ($data as $item) {
                $values = array_merge($values,array_values($item));
                $values_str .= $str.',';
            }
            $values_str = rtrim($values_str,',');
        }else{
            $keys = array_keys($data);
            $columns = '('. implode(',',$keys) . ')';
            $columns_num = count($keys);
            $values = array_values($data);
            $values_str = array_fill(0,$columns_num,'?');
            $values_str = '('. implode(',',$values_str) . ')';
        }

        $sql = "INSERT INTO ta {$columns} VALUES {$values_str};";
        $db = $this->_connections->pop()['obj'];
        $stmt = $db->prepare($sql);
        if ($stmt == false)
        {

            $r = false;
        }
        else {
            $r = $stmt->execute($values, -1);
        }
        return $r;
    }

    public function db()
    {
        /*
        $this->_connections = new Channel(3);
        go(function() {
            $db = new \Swoole\Coroutine\Mysql();
            $db->connect([
                'host' => '127.0.0.1',
                'port' => 3306,
                'user' => 'root',
                'password'  => '111111',
                'database'  => 'test',
                'charset'   => 'utf8',
                'fetch_mode'=>true,
            ]);
            $this->backToPool($db);
        }); */
        $r = DB::table('ta')->save([
           'name'=>'lisa','value'=>1,'create_time'=>date('Y-m-d H:i:s')
        ]);
        //$r = DB::init();
        /*
        $r = $this->save([
            ['name'=>'lisa2','value'=>2,'create_time'=>date('Y-m-d H:i:s')],
            ['name'=>'lisa3','value'=>3,'create_time'=>date('Y-m-d H:i:s')],
        ]);
        */
        return ['r'=>$r];
    }

    public function a()
    {

        $count = Session::get('count');
        empty($count) && $count = 0;
        $count++;
        Session::set('count',$count);

        return ['cout'=>$count];
        /*
        $data = [5=>'a',2=>'b',7=>'c',1=>'d',8=>'e',9=>'f',10=>'g'];
        $data = array_flip($data);
        $heap = new ArrayMinHeap();
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
        */
    }
}