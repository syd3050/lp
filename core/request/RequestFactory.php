<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-5
 * Time: 下午9:56
 */

namespace core\request;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestFactory implements RequestFactoryInterface
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
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        // TODO: Implement createRequest() method.
    }
}