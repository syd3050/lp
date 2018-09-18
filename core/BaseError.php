<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午3:48
 */
namespace core;

use core\error\IErrorHandle;

class BaseError implements IErrorHandle
{
    protected $errorMsg;
    protected $trace;

    public function __construct($errorMsg,$trace)
    {
        $this->errorMsg = $errorMsg;
        $this->trace = $trace;
    }

    /**
     * 返回处理后的error
     * @return mixed|string
     */
    public function handle()
    {
        // TODO: Implement handle() method.
        $error = json_encode([
            'error'=>[
                'error'=>$this->errorMsg,
                'error_trace'=>$this->trace
            ]])."\n";
        return $error;
    }
}