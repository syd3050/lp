<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:28
 */
namespace core\swoole;

use core\exception\ServerException;
use core\Request;
use core\Route;

class Server
{
    public $host;
    public $contentType;
    public $request;

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
        $http->set([
            'reactor_num' => 2,   //reactor thread num
            'worker_num' => 2,    //worker process num
            'backlog' => 1024,     //listen backlog,最多同时有多少个待accept的连接
            'max_request' => 2000,  //处理完n次请求后结束运行,重新创建一个worker进程,防止worker进程内存溢出
            'dispatch_mode' => 1, //1平均分配，2按FD取模固定分配，3抢占式分配，默认为取模(dispatch=2)
            'open_cpu_affinity' => 1 , //启用CPU亲和设置
        ]);
        $http->on("start", function ($server) {
            echo "Swoole http server is started at ".$this->_config['host'].":".$this->_config['port']."\n";
        });
        $http->on("request", function ($request, $response) {
            /* */
            //填充server相关变量
            //$this->_build_server($request);
            $this->_parse($request);
            //路由解析
            $route = new Route($this->request);
            $result = $route->dispatch();

            $result = "hello";
            //$response->header("Content-Type", $this->contentType);
            $response->header("Content-Type", "text/plain");
            $response->end(json_encode($result));
        });

        $http->start();
        //$GLOBALS['server'] = $http;
    }

    private function _parse($request)
    {
        $this->request = new Request();
        $this->request->header = $request->header;
        $this->request->method = getV($request->server,'request_method');
        $params = [];
        empty($request->get) || $params = array_merge($params,$request->get);
        empty($request->post) || $params = array_merge($params,$request->post);
        $this->request->params = $params;
        $this->request->uri = getV($request->server,'request_uri');
        return $this->request;
    }

    private function _build_server($request)
    {
        $_GET  = empty($request->get) ? [] : $request->get;
        $_POST = empty($request->post) ? [] : $request->post;
        $this->host = getV($request->header,'host');
        $this->contentType = getV($request->header,'accept','text/plain');
        //填充$_SERVER数组
        $_SERVER['PHP_SELF'] = getV($request->server,'request_uri');
        $_SERVER['GATEWAY_INTERFACE'] = 'CGI/1.1';
        $_SERVER['SERVER_ADDR'] = $this->host;
        $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_ADDR'];
        //$_SERVER['SERVER_SOFTWARE'] = md5($_SERVER['SERVER_ADDR']);
        $_SERVER['SERVER_PROTOCOL'] = getV($request->server,'server_protocol');
        $_SERVER['REQUEST_METHOD']  = getV($request->server,'request_method');
        $_SERVER['REQUEST_TIME']    = getV($request->server,'request_time');
        $_SERVER['REQUEST_TIME_FLOAT'] = getV($request->server,'request_time_float');
        $_SERVER['QUERY_STRING'] = getV($request->server,'query_string');
        $_SERVER['DOCUMENT_ROOT'] = ROOT_PATH;
        $_SERVER['HTTP_ACCEPT'] = getV($request->header,'accept');
        $_SERVER['HTTP_ACCEPT_CHARSET']  = getV($request->header,'query_string');
        $_SERVER['HTTP_ACCEPT_ENCODING'] = getV($request->header,'accept-encoding');
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = getV($request->header,'accept-language');
        $_SERVER['HTTP_CONNECTION'] = getV($request->header,'connection');
        $_SERVER['HTTP_HOST'] = getV($request->header,'host');
        $_SERVER['HTTP_REFERER'] = getV($request->header,'referer');
        $_SERVER['HTTP_USER_AGENT'] = getV($request->header,'user-agent');
        $_SERVER['HTTPS'] = ''; //暂不支持
        $_SERVER['REMOTE_ADDR'] = getV($request->server,'remote_addr');
        $_SERVER['REMOTE_HOST'] = $_SERVER['REMOTE_ADDR'];
        $_SERVER['REMOTE_PORT'] = getV($request->server,'remote_port');
        $_SERVER['REMOTE_USER'] = getV($request->server,'remote_user');
        $_SERVER['REDIRECT_REMOTE_USER'] = getV($request->server,'redirect_remote_user');
        $_SERVER['SCRIPT_FILENAME'] = ROOT_PATH;
        $_SERVER['SERVER_PORT'] = getV($request->server,'server_port');
        $_SERVER['REQUEST_URI'] = getV($request->server,'request_uri');
        $_SERVER['PATH_INFO']   = getV($request->server,'path_info');
    }
}