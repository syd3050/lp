<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-26
 * Time: 上午10:29
 */

namespace core;


class Route
{
    protected $controller;
    protected $action;
    public $params;

    public function __construct($request)
    {

    }

    public function dispatch()
    {
        return '';
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getActon()
    {
        return $this->action;
    }



}