<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午6:11
 */
return [
    'log'  => [
        'type'  => 'file',  //or cache,db
        'level' => 'debug', //or debug,info,error,warning
    ],

    'cache' => [
        'type'   => 'file',  //or redis,memcached,db
        //For cache has its own configuration,the below is for file cache.
        //If key 'config' is not exists or empty,kernel will look for a configuration
        //reference to its type in this file.
        'config' => [
            'path' => CACHE_PATH,
            //Kernel hold an array for cache,when this array's item number reaches
            //the max_record,Kernel will write the array to file on hardware.
            'max_record' => 1024,
        ]
    ],

    'redis'  => [

    ],
];