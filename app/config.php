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
    Session配置,Session驱动类需要实现SessionDriver接口，且构造函数需要有一个数组参数接受以下的配置信息
    'session' => [
        'type'  => '',
        'class' => 'app\\core\\Sessionxx'
    ],
    */
];