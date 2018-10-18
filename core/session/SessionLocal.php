<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-10
 * Time: 下午6:06
 */

namespace core\session;

use core\exception\ServerException;
use core\LocalCache;
use core\swoole\Server;
use core\utils\ArrayMinHeap;

/**
 * 基于本地缓存的session
 * @package core\session
 */
class SessionLocal extends \SessionHandler
{
    //1 hours
    const MAX_LIFETIME = 3600;
    const MAX_REQUEST = 10000;
    /**
     * @var ArrayMinHeap
     */
    protected static $gc_heap = null;

    protected static $session_config = [
        'session_name'    => 'PHPSESSID',
        'max_lifetime'    => self::MAX_LIFETIME,
        //GC 概率 = gc_probability/gc_divisor ，例如以下配置表明每10000次请求有1次机会清理垃圾，
        //就是将所有“未访问时长”超过maxLifetime的项目清理掉
        'gc_probability ' => 1,
        //每多少次请求做一次垃圾清理
        'gc_divisor'      => self::MAX_REQUEST,
    ];

    /**
     *
     * @param  string $savePath
     * @param  mixed  $session_name
     * @return bool
     */
    public function open($savePath, $session_name)
    {
        self::$session_config['session_name'] = $session_name;
        if(self::$gc_heap == null) {
            self::$gc_heap = new ArrayMinHeap();
        }
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
        $session = LocalCache::get($session_id);
        if(Server::$request_num >= self::$session_config['gc_divisor']){
            $num = Server::$request_num;
            //重置
            Server::$request_num %= $num;
            $this->gc(self::$session_config['max_lifetime']);
        }
        //更新访问时间
        $session_data = ['t'=>time(),'d'=>$session['d']];
        LocalCache::set($session_id,$session_data);
        return $session['d'];
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
        //新增session
        if(empty(LocalCache::get($session_id))) {
            self::$gc_heap->insert([time()=>$session_id]);
        }
        //更新访问时间
        $session_data = ['t'=>time(),'d'=>$session_data];
        $r = LocalCache::set($session_id,$session_data);
        if(Server::$request_num >= self::$session_config['gc_divisor']){
            $num = Server::$request_num;
            //重置
            Server::$request_num %= $num;
            $this->gc(self::$session_config['max_lifetime']);
        }
        return $r;
    }

    /**
     * 删除Session
     * @param  string $session_id
     * @return bool
     */
    public function destroy($session_id)
    {
        return LocalCache::del($session_id);
    }

    public function close()
    {
        while (!self::$gc_heap->isEmpty()) {
            $oldest = self::$gc_heap->extract();
            $time = key($oldest);
            $session_id = $oldest[$time];
            LocalCache::del($session_id);
        }
    }

    /**
     * Session 垃圾回收
     * @param  string $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        //所有超过生存周期的都回收
        while (!self::$gc_heap->isEmpty() && (time()-key(self::$gc_heap->top()) >= $maxlifetime)) {
            $oldest = self::$gc_heap->extract();
            $time = key($oldest);
            $session_id = $oldest[$time];
            $last_time = LocalCache::get($session_id)['t'];
            //如果该项最近被访问过，重新加进来
            if($last_time > $time) {
                self::$gc_heap->insert([$last_time=>$session_id]);
            }else {
                //已过期，删除
                self::destroy($session_id);
            }

        }
        return true;
    }

}