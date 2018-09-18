<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午5:08
 */

namespace core\error;


use core\exception\ServerException;

class SocketErrorHandle implements IErrorHandle
{
    /**
     * @var IErrorHandle
     */
    private $_handle;

    /**
     * SocketErrorHandle constructor.
     * @param $handle
     * @throws ServerException
     */
    public function __construct($handle)
    {
        if(empty($_handle))
        {
            throw new ServerException("Error is empty!");
        }
        $this->_handle = $handle;
    }

    public function handle()
    {
        // TODO: Implement handle() method.
        $error = $this->_handle->handle();
        $this->send($error);
        return $error;
    }

    /**
     * @param $error
     */
    private function send($error)
    {

    }
}