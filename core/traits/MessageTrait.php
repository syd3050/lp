<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-6
 * Time: 上午10:27
 */
namespace core\traits;

use Psr\Http\Message\StreamInterface;

/**
 * Trait implementing functionality common to requests and responses.
 */
trait MessageTrait
{
    /** @var array Map of all registered headers, as original name => array of values */
    private $headers = [];
    /** @var string */
    private $protocol = '1.1';
    /** @var StreamInterface */
    private $stream;


    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    /**
     * Retrieves all message header values.
     *
     * The keys represent the header name as it will be sent over the wire, and
     * each value is an array of strings associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * 是否存在参数中header头对应的值，header头大小写不敏感.
     *
     * @param string $header Header头，大小写不敏感
     * @return bool  存在返回true,否则返回false.
     */
    public function hasHeader($header)
    {
        return isset($this->header[strtolower($header)]);
    }

    /**
     * 返回参数中header头对应的值，header头大小写不敏感
     * 如果不存在，返回空数组
     *
     * @param  string   $header Header头，大小写不敏感.
     * @return string[] header头对应的值.
     */
    public function getHeader($header)
    {
        $header = strtolower($header);
        if (!isset($this->headers[$header])) {
            return [];
        }
        return $this->headers[$header];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * This method returns all of the header values of the given
     * case-insensitive header name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return
     * an empty string.
     *
     * @param string $header Case-insensitive header field name.
     * @return string A string of values as provided for the given header
     *    concatenated together using a comma. If the header does not appear in
     *    the message, this method MUST return an empty string.
     */
    public function getHeaderLine($header)
    {
        return implode(', ', $this->getHeader($header));
    }

    /**
     * 返回一个新的实例化对象，其包含旧有的header头及值被替换为参数指定的header头和值
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that has the
     * new and/or updated header and value.
     *
     * @param string $header  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader($header, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $value = trimItem($value);
        $new = clone $this;
        $new->headers[strtolower($header)] = $value;
        return $new;
    }

    /**
     * 返回一个新的实例化对象，其包含旧有的header头及值，加上以参数指定的header头和值。
     *
     * 如果指定的header头已存在，其旧有的值不变，新的值添加到旧的值后面。如果header头不存在则新增。
     * 这个方法不会改变原有实例，而是返回一个包含新值的实例
     *
     * @param string          $header Header头名称，大小写不敏感
     * @param string|string[] $value  Header值，可为数组.
     * @return static
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader($header, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        $value = trimItem($value);
        $normalized = strtolower($header);
        //旧有的实例值不变，所以克隆
        $new = clone $this;
        if (isset($new->headers[$normalized])) {
            $new->headers[$normalized] = array_merge($this->headers[$normalized], $value);
        } else {
            $new->headers[$normalized] = $value;
        }
        return $new;
    }

    /**
     * Return an instance without the specified header.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return an instance that removes
     * the named header.
     *
     * @param string $header Case-insensitive header field name to remove.
     * @return static
     */
    public function withoutHeader($header)
    {
        $normalized = strtolower($header);
        $new = clone $this;
        if (!isset($this->headers[$normalized])) {
            return $new;
        }
        unset($new->headers[$normalized]);
        return $new;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody()
    {
        if (!$this->stream) {
            $this->stream = stream_for('');
        }
        return $this->stream;
    }

    /**
     * Return an instance with the specified message body.
     *
     * The body MUST be a StreamInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamInterface $body Body.
     * @return static
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->stream) {
            return $this;
        }
        $new = clone $this;
        $new->stream = $body;
        return $new;
    }

    /**
     * 重置所有header头信息
     *
     * @param array $headers
     */
    private function setHeaders(array $headers)
    {
        $this->headers = [];
        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            //去掉header值两边的空格
            $value = trimItem($value);
            $normalized = strtolower($header);
            if (isset($this->headers[$normalized])) {
                $this->headers[$normalized] = array_merge($this->headers[$normalized], $value);
            } else {
                $this->headers[$header] = $value;
            }
        }
    }

}