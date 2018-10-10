<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午10:50
 */

defined('APP_PATH')   or define('APP_PATH',   ROOT_PATH . 'app'  . DS);
defined('CORE_PATH')  or define('CORE_PATH',  ROOT_PATH . 'core' . DS);
defined('LOG_PATH')   or define('LOG_PATH',   ROOT_PATH . 'log'  . DS);
defined('CACHE_PATH') or define('CACHE_PATH', ROOT_PATH . 'cache'. DS);

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
        //var_dump(['error'=>$errorArray['msg']]);
        var_dump(['error'=>$errorArray,'error_trace'=>$traces]);
    } else
    {
        try{
            $handle = new \core\BaseError($errorArray,$traces);
            $error = $handle->handle();
            \core\Log::error($error);
        }catch (Exception $exception) {
            \core\Log::error($exception->getMessage());
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
        var_dump($e->getMessage());
    } else
    {
        try{
            $handle = new \core\BaseException($e);
            $exception_msg = $handle->handle();
            \core\Log::error($exception_msg);
        }catch (Exception $exception) {
            exit($exception->getMessage());
        }
    }
}
