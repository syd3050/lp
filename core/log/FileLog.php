<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午7:10
 */
namespace core\log;

use core\exception\ServerException;
use core\IFileHandle;

class FileLog implements IFileHandle
{
    private $_file = "";
    private $_file_handle = null;

    /**
     * 打开文件
     * @param string $path 文件路径
     * @throws ServerException
     */
    public function open($path)
    {
        // TODO: Implement open() method.
        $this->_file = LOG_PATH."error/".date('Y-m-d').".log";
        $this->_file_handle = fopen($this->_file, 'a');
        if(!$this->_file_handle) {
            throw new ServerException("File {$this->_file} not exists!");
        }
    }

    public function write()
    {
        // TODO: Implement write() method.
    }

    public function close()
    {
        // TODO: Implement close() method.
    }
}