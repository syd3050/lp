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
    private $_prefix = 'swoole_local';
    /**
     * @var null|\Yac
     */
    private $_yac = null;

    public function __construct($config=[])
    {
        isset($config['prefix']) && $this->_prefix = $config['prefix'];
        $this->_yac = new \Yac($this->_prefix);
    }

    public function get($key, $default = '')
    {
        // TODO: Implement get() method.
        $result = $this->_yac->get($key);
        //dev_dump(['yac-key'=>$key,'yac-value'=>$result]);
        return empty($result) ? $default : $result;
    }

    public function set($key, $value)
    {
        // TODO: Implement set() method.
        return $this->_yac->set($key, $value);
    }

    public function del($key)
    {
        // TODO: Implement clear() method.
        return $this->_yac->delete($key);
    }

    public function clear()
    {
        // TODO: Implement clear() method.
        return $this->_yac->flush();
    }
}