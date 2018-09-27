<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-9-26
 * Time: 上午10:29
 */

namespace core;


use core\exception\ConfigException;
use core\exception\ServerException;

class Route
{
    protected $default_controller;
    protected $default_action;
    protected $controller;
    protected $action;
    public $params=[];
    protected $config;
    protected $path;
    protected $method;

    /**
     * Route constructor.
     * @param Request $request
     * @throws ConfigException
     */
    public function __construct($request)
    {
        //必须先加载路由配置文件
        $this->loadConfig();
        //$uri = explode('/',$request->uri);
        //array_shift($uri);
        //$this->path = implode('/',$uri);
        //$this->method = $request->method;
    }

    protected function parseRoute()
    {
        foreach ($this->config as $pattern => $value)
        {
            if(preg_match("#$pattern#",$this->path,$matches))
            {
                //这时候matches剩下的就是$1,$2等参数了
                array_shift($matches);
                $params_num = count($matches);
                //$this->params = $matches;
                $real = explode('/',$value);
                $num = count($real);
                if($num < 2)
                    throw new ConfigException("route.php中{$pattern}=>{$value}配置错误");
                $i = 2;
                while ($i < $num)
                {
                    //默认第3个参数以$n开始
                    if(strpos($real[$i],'$') === 0)
                    {
                        //$1 or $n,找到对应对参数
                        $pos = intval(trim($real[$i])) - 1;
                        if($pos >= 0 && $pos < $params_num)
                        {
                            $this->params[] = $matches[$pos];
                        }
                    }
                    $i++;
                }
                $this->controller = $real[0];
                $this->action = $real[1];
                break;
            }
        }
        //没有匹配，则按照默认控制器和方法处理
        if(empty($this->controller))
        {
            $this->controller = empty($uri[0]) ? $this->default_controller : $uri[0];
            $this->action = empty($uri[1]) ? $this->default_action : $uri[1];
        }
    }

    protected function loadConfig()
    {
        /*
        $config = Cache::get("route.conf");
        if(empty($config))
        {
            $config = include ROOT_PATH.'app'.DS.'route.php';
            Cache::set("route.conf",json_encode($config));
        }else{
            $config = json_decode($config,true);
        }
        */
        $config = include ROOT_PATH.'app'.DS.'route.php';
        //Cache::set("route.conf",json_encode($config));
        if(empty($config['default_controller']))
            throw new ConfigException("route.php中缺少default_controller配置");
        if(empty($config['default_action']))
            throw new ConfigException("route.php中缺少default_action配置");
        $this->default_controller = $config['default_controller'];
        $this->default_action = $config['default_action'];
        unset($config['default_controller']);
        unset($config['default_action']);
        $this->config = $config;
    }

    public function dispatch()
    {
        //$this->parseRoute();
        $this->controller = "Index";
        $this->action = "index";
        $controller = 'app\\controller\\'.$this->controller.'Controller';
        if(!class_exists($controller))
            throw new ServerException("Controller {$controller} 不存在！ \n");
        $class = new $controller();
        if(!method_exists($class,$this->action))
            throw new ServerException("$class 中方法 {$this->action} 不存在 !\n");
        //if(strtolower($this->method) == 'get')
            //$this->params = array_merge($this->params,$_GET);
        return call_user_func_array(array($class,$this->action),$this->params);
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getActon()
    {
        return $this->action;
    }



}