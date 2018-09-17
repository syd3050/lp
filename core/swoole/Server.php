<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:28
 */
namespace core\swoole;

use core\exception\ServerException;

class Server
{

    private $_config = [
        'host'  =>  '127.0.0.1',
        'port'  =>  9501,
    ];

    public function __construct($config=[])
    {
        empty($config) || $this->_config = array_merge($this->_config,$config);
    }

    protected function check()
    {
        if(empty($this->_config['host'])) {
            return [false,'缺少host'];
        }

        if(empty($this->_config['port'])) {
            return [false,'缺少port'];
        }

        return [true,null];
    }

    public function start()
    {
        list($r,$err) = $this->check();
        if(!$r) {
            throw new ServerException($err);
        }

        $http = new \swoole_http_server($this->_config['host'], $this->_config['port']);

        $http->on("start", function ($server) {
            echo "Swoole http server is started\n";
        });

        $http->on("request", function ($request, $response) {
            $response->header("Content-Type", "text/plain");
            $response->end("Hello World\n");
        });

        $http->start();
    }
}