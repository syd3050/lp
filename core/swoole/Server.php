<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:28
 */
namespace core\swoole;

use core\Config;
use core\db\DB;
use core\exception\ServerException;
use core\request\ServerRequestFactory;
use core\Route;

class Server
{
    public $host;
    public $contentType;
    public $request;
    /**
     * @var int 记录每一轮已经处理的请求数量
     */
    public static $request_num = 0;

    private $_config = [
        'host'     =>  '127.0.0.1',
        'port'     =>  9501,
        'HttpOnly' =>  false,
        'secure'   =>  false,
        'swoole' => [
            'reactor_num' => 2,   //reactor thread num
            'worker_num' => 2,    //worker process num
            'backlog' => 1024,     //listen backlog,最多同时有多少个待accept的连接
            'max_request' => 2000,  //处理完n次请求后结束运行,重新创建一个worker进程,防止worker进程内存溢出
            'dispatch_mode' => 1, //1平均分配，2按FD取模固定分配，3抢占式分配，默认为取模(dispatch=2)
            'open_cpu_affinity' => 1 , //启用CPU亲和设置
        ]
    ];

    /**
     *
     * @param array $config 构造函数配置参数优先级最高，配置文件中的server配置项其次，本类中的_config配置项最低
     */
    public function __construct($config=[])
    {
        $server = Config::get(Config::CONFIG,'server') ?: [];
        $config = array_merge($server,$config);
        empty($config) || $this->_config = array_merge($this->_config,$config);
    }

    protected function checkConfig()
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
        list($r,$err) = $this->checkConfig();
        if(!$r) {
            throw new ServerException($err);
        }
        $http = new \swoole_http_server($this->_config['host'], $this->_config['port']);
        $http->set($this->_config['swoole']);
        $http->on("start", function ($server) {
            echo "Swoole http server is started at ".$this->_config['host'].":".$this->_config['port']."\n";
        });
        $http->on("workerStart",function (){
            DB::init();
        });
        $http->on("request", function (\swoole_http_request $request, \swoole_http_response $response) {
            if(getV($request->server,'request_uri') == '/favicon.ico') {
                $response->header("Content-Type", "text/plain;charset=UTF-8");
                $response->end("");
                return;
            }
            self::$request_num++;
            //填充server相关变量
            $this->_build_global($request);
            $this->request = $this->_build_request($request);
            $psr_response = null;

            //路由解析
            $route = new Route();
            list($controller,$action,$params) = $route->parseRoute($this->request);
            $psr_response = $route->dispatch($controller,$action,$params);
            $result = $psr_response->getBody();
            $response->status($psr_response->getStatusCode());

            $header = $this->_set_header();
            foreach ($header as $k=>$v)
            {
                $response->header($k, $v);
            }

            $response->end(json_encode($result)."\n");
        });

        $http->start();
        //$GLOBALS['server'] = $http;
    }

    private function _build_request($request)
    {
        $method = getV($request->server,'request_method','GET');
        $uri = getUriFromGlobals();
        $serverRequest= (new ServerRequestFactory())
            ->createServerRequest($method,$uri,$_SERVER);
        return $serverRequest
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
            ->withUploadedFiles(normalizeFiles($_FILES));
    }

    private function _set_header()
    {
        $cookie = '';
        foreach ($_COOKIE as $k=>$v){
            $last = strpos($v,';') != (strlen($v)-1) ? ';' : '';
            $cookie .= "$k=$v{$last}";
        }
        $_REQUEST['header']['Set-Cookie'] = $cookie;
        if(!isset($_REQUEST['header']['Content-Type']))
            $_REQUEST['header']['Content-Type'] = "text/plain;charset=UTF-8";
        return $_REQUEST['header'];
    }

    private function _build_global($request)
    {
        /* 获取环境变量以标识当前所属环境，默认为生产环境 */
        $GLOBALS['env'] = getV($request->header,ENV_KEY,DEFAULT_ENV);
        $_GET  = empty($request->get) ? [] : $request->get;
        $_POST = empty($request->post) ? [] : $request->post;
        $_COOKIE = empty($request->cookie) ? [] : $request->cookie;
        $_FILES = empty($request->files) ? [] : $request->files;

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