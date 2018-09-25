<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-18
 * Time: 下午7:10
 */
namespace core\log;

use core\exception\ConfigException;
use core\exception\ServerException;
use core\IFileHandle;

class FileLog implements ILog
{
    private static $_file = "";
    private static $_file_handle = null;

    private static $_level = ILog::DEBUG;


    public function __construct($level = ILog::DEBUG)
    {
        // TODO: Implement init() method.
        if(!in_array($level,ILog::LEVEL_ARR))
            throw new ConfigException("$level not exists.");
        self::$_level = $level;
        self::$_file = LOG_PATH."{$level}_".date('Y-m-d').".log";
        $this->_init();
    }

    private function _init()
    {
        self::$_file_handle = fopen(self::$_file, 'a');
        if(!self::$_file_handle)
        {
            throw new ServerException("File not exists!");
        }
    }

    public function write($message)
    {
        // TODO: Implement write() method.
        if(!self::$_file_handle)
            $this->_init();
        fwrite(self::$_file_handle, $message."\n");
        fclose(self::$_file_handle);
    }


}