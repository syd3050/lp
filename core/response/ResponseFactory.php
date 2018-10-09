<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-9
 * Time: 下午9:50
 */

namespace core\response;


use core\Config;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactory implements ResponseFactoryInterface
{

    /**
     * Create a new response.
     *
     * @param int $code HTTP status code; defaults to 200
     * @param string $reasonPhrase Reason phrase to associate with status code
     *     in generated response; if none is provided implementations MAY use
     *     the defaults as suggested in the HTTP specification.
     *
     * @return ResponseInterface
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        // TODO: Implement createResponse() method.
        $response_class = Config::get(Config::CONFIG,'response');
        if(class_exists($response_class))
        {
            return new $response_class($code, $reasonPhrase);
        }
        return new Response($code,$reasonPhrase);
    }
}