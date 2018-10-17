<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午6:11
 */
return [
    'log'  => [
        'type'  => 'file',  //or redis,memcached,db
        'level' => 'debug', //or debug,info,error,warning
        //如果用于该组件的属性配置，如果没有则会在这个配置文件中查找跟type类型对应对属性
        'config' => []
    ],

    'cache' => [
        'type'   => 'redis',  //or redis,memcached,db
        'config' => []
    ],

    'database' => [
        'host' => '',
        'port' => '',
        'name' => '',
        'username' => '',
        'password' => '',
    ],

    'redis'  => [
        'host'     => '127.0.0.1',
        'port'     => '6379',
        'password' => ''
    ],

    //Request类路径
    //'request' => 'app\\core\\Request',
    //Response类路径
    //'response'=> 'app\\core\\Response',
    /*
    Session配置
    'session' => [
        'type'  => '',
        //需要继承SessionHandler类
        'class' => 'app\\core\\Sessionxx',
        //配置session放置的位置，可配redis,mysql,文件路径等，例如需要单独配置redis：
        //'save_path'=>['host'=>'','port'=>'','password'=>'']
        //如果没有上述配置，将使用'class'指向的类中的默认配置，如果类中没有对应配置，将使用本文件中的对应配置
        'save_path'       => '',
        'session_name'    => 'PHPSESSID',
    ],
    */
];