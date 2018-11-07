<?php
namespace core\interfaces;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IRoute
{
    /**
     * @param ServerRequestInterface $request
     * @return array [controller,action,params]
     */
    public function parseRoute(ServerRequestInterface $request);

    /**
     * @param $controller
     * @param $action
     * @param $params
     * @return ResponseInterface
     */
    public function dispatch($controller,$action,$params);

    public function getController();

    public function getAction();

    public function getParams();
}