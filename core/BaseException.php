<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-17
 * Time: 下午9:35
 */

namespace core;


use core\exception\IExceptionHandle;
use Throwable;

class BaseException extends \Exception implements IExceptionHandle
{

    /**
     * 返回处理后的exception
     * @return mixed
     */
    public function handle()
    {
        // TODO: Implement handle() method.
        $data['exception']['msg'] = $this->getMessage();
        $data['exception']['code'] = $this->getCode();
        $data['exception']['trace'] = $this->getTrace();
        $data['exception']['file'] = $this->getFile();
        $data['exception']['line'] = $this->getLine();
        $exception = json_encode($data)."\n";
        return $exception;
    }

}