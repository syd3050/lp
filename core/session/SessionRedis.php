<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-12
 * Time: 下午1:50
 */

namespace core\session;


use core\exception\ConfigException;

class SessionRedis extends \SessionHandler
{
    /**
     * @var \Redis
     */
    protected $handler = null;

    protected $config  = [
        'host'         => '127.0.0.1',
        'port'         => 6379,
        'password'     => '',
        'select'       => 0,
        'expire'       => 3600, // 有效期(秒)
        'persistent'   => true, // 是否长连接
        'prefix'       => '',
    ];

    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     *
     * @param  string $savePath
     * @param  mixed  $session_name
     * @return bool
     * @throws ConfigException
     */
    public function open($savePath, $session_name)
    {
        if (extension_loaded('redis')) {
            $this->handler = new \Redis;
            $connect = $this->config['persistent'] ? 'pconnect' : 'connect';
            $this->handler->$connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            if ('' != $this->config['password']) {
                $this->handler->auth($this->config['password']);
            }
            if (0 != $this->config['select']) {
                $this->handler->select($this->config['select']);
            }
        } else {
            throw new ConfigException('Redis should be installed.');
        }
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close()
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
        $this->handler->close();
        $this->handler = null;
        return true;
    }

    /**
     * 读取Session
     * @access public
     * @param  string $session_id
     * @return string
     */
    public function read($session_id)
    {
        return (string) $this->handler->get($this->config['prefix'] . $session_id);
    }

    /**
     * 写入Session
     * @access public
     * @param  string $session_id
     * @param  string $session_data
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        if ($this->config['expire'] > 0) {
            $result = $this->handler->setex($this->config['prefix'] . $session_id, $this->config['expire'], $session_data);
        } else {
            $result = $this->handler->set($this->config['prefix'] . $session_id, $session_data);
        }
        return $result ? true : false;
    }

    /**
     * 删除Session
     * @param  string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        return $this->handler->delete($this->config['prefix'] . $session_id) > 0;
    }

    /**
     * Session 垃圾回收
     * @param  string $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }
}