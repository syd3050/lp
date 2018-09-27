<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-25
 * Time: 下午4:24
 */

namespace core\exception;


use core\BaseException;
use Throwable;

class ConfigException extends BaseException
{

    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}