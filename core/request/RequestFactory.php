<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-5
 * Time: 下午9:56
 */

namespace core\request;

use core\Config;
use core\exception\ConfigException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class RequestFactory implements RequestFactoryInterface
{

    /**
     * Create a new request.
     *
     * @param string $method The HTTP method associated with the request.
     * @param UriInterface|string $uri The URI associated with the request. If
     *     the value is a string, the factory MUST create a UriInterface
     *     instance based on it.
     *
     * @return RequestInterface
     * @throws ConfigException
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        // TODO: Implement createRequest() method.
        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }
        $request_class = Config::get(Config::CONFIG,'request');
        if(class_exists($request_class))
        {
            return new $request_class($method, $uri);
        }
        //使用默认的request
        return new Request($method, $uri);
    }
}