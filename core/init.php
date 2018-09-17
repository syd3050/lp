<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午10:50
 */

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('ROOT_PATH') or define('ROOT_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
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
    // 调试错误表
    $errCodes = array(
        E_ERROR   => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE   => 'PARSE',
        E_NOTICE  => 'NOTICE'
    );
    // 得到错误类型
    $errorArray['type'] = isset($errCodes[$code]) ? $errCodes[$code] : 'OTHER';

    // 显示跟踪信息
    $traces = debug_backtrace();

    foreach ($traces as $v)
    {
        if(!isset($v['file'])){
            $trace_info = '内存中发生错误!';
        }
        else
        {
            $v['file'] = str_replace(ROOT_PATH, "", $v['file']);
            if(isset($v['class'])){ // 类中的错误
                $trace_info = "{$v['file']}, {$v['line']}, {$v['class']}, {$v['function']}";
            }else if(isset($v['function'])){ // function中的错误
                $trace_info = "{$v['file']}, {$v['line']}, {$v['function']}";
            }else{
                $trace_info = "{$v['file']}, {$v['line']}";
            }
            // 判断发生错误的地方是否有参数
            if(isset($v['args']))
            {
                if(is_array($v['args']))
                {
                    foreach ($v['args'] as $key => $value)
                    {
                        if(is_object($value))
                        {
                            unset($v['args'][$key]); //删除引用
                            $v['args'][$key] = gettype($value)." is an object";
                        }
                        else if(is_array($value))
                        {
                            unset($v['args'][$key]); //删除引用
                            $v['args'][$key] = $key." is an array";
                        }
                    }
                    $trace_info .= '(' . implode(',', $v['args']) . ')';
                } else
                {
                    $trace_info .= "({$v['args']})";
                }
            } else if(isset($v['function']))
            {
                $trace_info .= "({$v['function']})";
            }
        }
        $errorArray['trace'][] = $trace_info;
    }
    // 将错误信息保存在全局变量中
    $GLOBALS['sys']['errorInfo'][] = $errorArray;

    // 或者可以选择把错误信息输出的错误日志文件中
    $handle = fopen(LOG_PATH.'/error/'.date('Y-m-d').'.log', 'a');
    foreach ($errorArray['trace'] as $v){
        fwrite($handle, $v."\n");
        fclose($handle);
    }
}

// 定义系统级异常的错误码
define('EXCEPTION_CORE', -100);   // 框架内核异常，如加载不存在的文件异常
define('EXCEPTION_CONFIG', -101); // 加载配置异常
define('EXCEPTION_DB', -102);     // 数据库语句异常
define('EXCEPTION_CACHE', -103);  // 链接CACHE异常
define('EXCEPTION_API', -104);    // 以PHP连接API接口异常

// 自定义异常处理函数
set_exception_handler('exception_handler');

// 格式化异常跟踪信息
function getTrace($traces){
    $result = [];
    foreach ($traces as $v){
        if(!isset($v['file'])){
            $trace_info = 'error not in file,maybe in memory!';
        }else{
            // 取得相对路径
            $v['file'] = str_replace(ROOT_PATH, "", $v['file']);

            if(isset($v['class'])){
                $trace_info = "{$v['file']}, {$v['line']}, {$v['class']}, {$v['function']}";
            }else if(isset($v['function'])){
                $trace_info = "{$v['file']}, {$v['line']}, {$v['function']}";
            }else{
                $trace_info = "{$v['file']}, {$v['line']}";
            }

            // 判断发生错误的地方是否有参数
            if(isset($v['args'])){
                if(is_array($v['args'])){
                    foreach ($v['args'] as $ki => $vi) {
                        // 判断数组元素是否对象
                        if(is_object($vi)){
                            unset($vi['args'][$ki]);
                            $v['args'][$ki] = gettype($vi)." obj";

                        }
                        // 判断元素是否数组
                        else if(is_array($vi)){
                            unset($v['args'][$ki]);
                            $v['args'][$ki] = $ki." array";
                        }
                    }
                    // 经过上面的处理数组剩下的元素都是基本类型的了
                    $trace_info .= '(' . implode(',', $v['args']) . ')';
                }else{
                    $trace_info .= '(' . $v['args'] . ')';
                }
            }else if(isset($v['function'])){
                $trace_info .= '()';
            }
        }
        $result[] = $trace_info;
    }
    return $result;
}

function exception_handler(Exception $e){
    if($e->getCode() == 404 && !DEBUG){
        header("HTTP/1.0 404 Not Found");
        exit;
    }

    $data = array();
    $data['_msg'] = $e->getMessage();
    $data['_code'] = $e->getCode();
    $data['_trace'] = getTrace($e->getTrace());
    $data['_file'] = $e->getFile();
    $data['_line'] = $e->getLine();

    // 调试直接显示
    if(DEBUG){
        echo "<pre>";
        var_dump($data);
    }else{
        $handle = fopen(LOG_PATH.'/error/'.date('Y-m-d').'.log', 'a');
        $output = '';
        foreach ($data as $k => $v){
            if($k != '_trace'){
                $output .= $k.": ".$v."\n";
            }else{
                if($v){
                    foreach ($v as $k2 => $v2){
                        $output .= "[tarce{$k2}]: ".$v2."\n";
                    }
                }
            }
        }
        fwrite($handle, $output);
        fclose($handle);
    }
}
