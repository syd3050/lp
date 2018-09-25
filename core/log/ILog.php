<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午7:28
 */

namespace core\log;


interface ILog
{
    const INFO    = 'info';
    const DEBUG   = 'debug';
    const ERROR   = 'error';
    const WARNING = 'warning';

    const LEVEL_ARR = [self::INFO,self::DEBUG,self::ERROR,self::WARNING];

    public function write($message);

}