<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-26
 * Time: 下午2:11
 */

namespace core\request;

use core\traits\MessageTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
    use MessageTrait;

    const HEADER_FORWARDED           = 0b00001;           // When using RFC 7239
    const HEADER_X_FORWARDED_FOR     = 0b00010;
    const HEADER_X_FORWARDED_HOST    = 0b00100;
    const HEADER_X_FORWARDED_PROTO   = 0b01000;
    const HEADER_X_FORWARDED_PORT    = 0b10000;
    const HEADER_X_FORWARDED_ALL     = 0b11110;     // All "X-Forwarded-*" headers
    const HEADER_X_FORWARDED_AWS_ELB = 0b11010; // AWS ELB doesn't send X-Forwarded-Host

    const METHOD_HEAD    = 'HEAD';
    const METHOD_GET     = 'GET';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_PATCH   = 'PATCH';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_PURGE   = 'PURGE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';

    public $header;
    public $params = [];

    /** @var string */
    private $method;
    /** @var null|string */
    private $requestTarget;
    /** @var UriInterface */
    private $uri;

    /**
     * @param string                               $method  HTTP method
     * @param UriInterface                         $uri     URI
     * @param array                                $headers Request headers
     * @param string|null|resource|StreamInterface $body    Request body
     * @param string                               $version Protocol version
     */
    public function __construct(
        $method,
        $uri,
        array $headers = [],
        $body = null,
        $version = '1.1'
    ) {
        if (!($uri instanceof UriInterface))
            throw new \InvalidArgumentException("Parameter uri must be an instance of UriInterface");
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->setHeaders($headers);
        $this->protocol = $version;
        //如果初始化Header头中没有Host，从Uri中获取host和port并置于Header头数组首位
        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }
        if ($body !== '' && $body !== null) {
            $this->stream = stream_for($body);
        }
    }

    /**
     * 获取request的请求路径，实际上是path+queryString，例如，对于
     * $url = 'http://username:password@hostname:9090/path?arg=value#anchor';
     * 将返回'/path?arg=value#anchor'
     *
     * @return string 没有返回"/"
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }
        /**
         * 获得Uri的path,例如，对于
         * $url = 'http://username:password@hostname:9090/path?arg=value#anchor';
         * 有 $path = '/path';
         */
        $path = $this->uri->getPath();
        if ($path == '') {
            $path = '/';
        }
        /**
         * 将请求字符串拼接path得到请求路径，例如，对于上述$url，为arg=value#anchor的部分
         * 这时 $path = '/path?arg=value#anchor';
         */
        if ($this->uri->getQuery() != '') {
            $path .= '?' . $this->uri->getQuery();
        }
        return $path;
    }

    /**
     * 返回一个新实例，其requestTarget为指定的值
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        /**
         * 不允许有空白字符
         * \s 匹配任何空白字符，包括空格、制表符、换页符等等。等价于 [ \f\n\r\t\v]。
         */
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException(
                'Invalid request target provided; cannot contain whitespace'
            );
        }
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    /**
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 返回一个新实例，其method为指定的值
     *
     * @param string $method Case-sensitive method.
     * @return static
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    /**
     * 返回request相关的URI实例
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * 返回一个新实例，其uri为参数指定的URI
     *
     * 按照接口要求，本方法必须用参数指定的URI中的host Header头更新新实例的header头，
     *
     * 设置$preserveHost为true就不必更新header的host值
     * 如果指定的URI中不存在host Header，则不必更新
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri
     * @param bool $preserveHost 为true就不必更新header的host值。
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }
        $new = clone $this;
        $new->uri = $uri;
        if (!$preserveHost) {
            $new->updateHostFromUri();
        }
        return $new;
    }

    private function updateHostFromUri()
    {
        $host = $this->uri->getHost();
        if ($host == '') {
            return;
        }
        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }
        // Host MUST be the first header:http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headers = ['host' => [$host]] + $this->headers;
    }

}