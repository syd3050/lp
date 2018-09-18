<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:25
 */
namespace core;

use core\swoole\Server;

class Bootstrap
{
    private $_config = [

    ];

    public function __construct($config = [])
    {
        empty($config) || $this->_config = array_merge($this->_config,$config);
    }

    public function run()
    {
        $swConfig = empty($this->_config['swoole']) ? [] : $this->_config['swoole'];
        $server = new Server($swConfig);
        $server->start();
    }
}