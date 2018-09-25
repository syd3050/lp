<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午3:51
 */

namespace core\error;

use core\exception\ServerException;
use core\Log;

class FileErrorHandle implements IErrorHandle
{
    private $_file = "";
    private $_file_handle = null;
    /**
     * @var IErrorHandle
     */
    private $_handle;

    /**
     * FileErrorHandle constructor.
     * @param null $handle
     * @throws ServerException
     */
    public function __construct($handle=null)
    {
        if(empty($handle))
        {
            throw new ServerException("Handle is empty!");
        }
        $this->_handle = $handle;
        $this->_file = LOG_PATH."error/".date('Y-m-d').".log";
        $this->_file_handle = fopen($this->_file, 'a');
        if(!$this->_file_handle) {
            throw new ServerException("File {$this->_file} not exists!");
        }
    }

    /**
     * 返回处理后的error
     * @return mixed
     */
    public function handle()
    {
        // TODO: Implement handle() method.
        $error = $this->_handle->handle();
        Log::error($error."\n");
        return $error;
    }


}