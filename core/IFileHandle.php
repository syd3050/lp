<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午7:08
 */
namespace core;

interface IFileHandle
{
    public function open();

    public function write();

    public function close();
}