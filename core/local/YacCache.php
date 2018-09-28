<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-28
 * Time: 下午4:29
 */

namespace core\local;


class YacCache implements LocalCacheDriver
{
    private $_config = ['prefix' => ''];
    /**
     * @var null|\Yac
     */
    private $_yac = null;

    public function __construct($config=[])
    {
        $this->_config = array_merge($this->_config,$config);
        $this->_yac = new \Yac($this->_config);
    }

    public function get($key, $default = '')
    {
        // TODO: Implement get() method.
        $result = $this->_yac->get($key);
        return empty($result) ? $default : $result;
    }

    public function set($key, $value)
    {
        // TODO: Implement set() method.
        return $this->_yac->set($key, $value);
    }
}