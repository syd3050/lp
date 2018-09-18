<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午10:50
 */

defined('APP_PATH') or define('APP_PATH', ROOT_PATH . 'app' . DS);
defined('CORE_PATH') or define('CORE_PATH', ROOT_PATH . 'core' . DS);
defined('LOG_PATH') or define('LOG_PATH', ROOT_PATH . 'log' . DS);
defined('START_TIME') or define('START_TIME', microtime(true));
defined('START_MEM') or define('START_MEM', memory_get_usage());

if(DEBUG){
    error_reporting(E_ALL | E_STRICT);
}else{
    ini_set('display_errors', 0);
    error_reporting(0);
}

set_error_handler('error_handler');

function error_handler($code, $msg, $file, $line) {
    //将参数添加到数组中
    $errorArray = compact('code', 'msg', 'file', 'line');
    //把错误信息输出的错误日志文件中
    $traces = debug_backtrace();
    if(DEBUG)
    {
        echo "\n";
        var_dump(['error'=>$errorArray,'error_trace'=>$traces]);
    } else
    {
        try{
            $handle = new \core\error\FileErrorHandle(
                new \core\BaseError($errorArray,$traces)
            );
            $handle->handle();
        }catch (\core\exception\ServerException $exception) {

        }
    }
}

// 自定义异常处理函数
set_exception_handler('exception_handler');

function exception_handler(Exception $e)
{
    if($e->getCode() == 404 && !DEBUG)
    {
        exit("HTTP/1.0 404 Not Found");
    }

    if(DEBUG)
    {
        echo "\n";
        var_dump($e);
    } else
    {
        try{
            $handle = new \core\error\FileErrorHandle(
                new \core\BaseException($e)
            );
            $handle->handle();
        }catch (\core\exception\ServerException $exception) {
            exit($exception->getMessage());
        }
    }
}
