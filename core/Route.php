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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class Route
{
    protected $default_controller;
    protected $default_action;
    protected $controller;
    protected $action;
    protected $config;
    protected $path;
    protected $queryArray;
    protected $target;
    public $params=[];
    public $route;
    const CAA = 'c-a';
    const PAP = 'p-p';

    /**
     * Route constructor.
     * @param ServerRequestInterface $request
     * @throws ConfigException
     */
    public function __construct($request)
    {
        //必须先加载路由配置文件
        $this->loadConfig();
        $this->queryArray = $request->getQueryParams();
        $this->target = $request->getRequestTarget();
        /*
         * 1.requestTarget为:
         *      /index.php?r=controller/action&p1=v1&p2=v2
         *      /controller/action/v1/v2
         * 的形式
         * 2.queryParams为
         * array(3) {
            ["r"]=>
            string(8) "controller/action"
            ["p1"]=>
            string(3) "v1"
            ["p2"]=>
            string(3) "v2"
          }
          的形式
         */
        if(!isset($this->queryArray['r']))
        {
            $target = ltrim($this->target,'/');
            //没有/时取默认配置
            if(strpos($target,'/') === FALSE)
                $this->path = $this->default_controller.'/'.$this->default_action;
            else
                $this->path = $target;
        }else{
            $this->path = $this->queryArray['r'];
            unset($this->queryArray['r']);
        }
    }

    protected function loadConfig()
    {
        $route_config = Config::get(Config::ROUTE);
        if(empty($route_config['default_controller']))
            throw new ConfigException("route.php中缺少default_controller配置");
        if(empty($route_config['default_action']))
            throw new ConfigException("route.php中缺少default_action配置");
        $this->default_controller = ucwords($route_config['default_controller']);
        $this->default_action = $route_config['default_action'];
        unset($route_config['default_controller']);
        unset($route_config['default_action']);

        self::_parse_route_config($route_config);
    }

    /**
     * 解析route.php配置文件，将含正则表达式的路由处理为controller-action并缓存
     * 针对
     * 'item/\d+'    => 'Post/view/$1/5',
     * 'item/del/\d' => 'Operate/del/$1',
     * 这样的路由设置，$snapshot数组的结构是这样的
     * $snapshot = [
     *      'item' => [
     *          //c-a标识controller和action,针对的是'item/\d+'这个路由，其controller为Post,action为view
     *          'c-a' => ['Post','view'],
     *          //p-p标识参数params在uri中的位置pos
     *          'p-p' => ['$1','5'],
     *          'del' => [
     *              //针对的是'item/del/\d'这个路由，其controller为Operate,action为del
     *              'c-a' => ['Operate','del'],
     *              'p-p' => ['$1'],
     *          ],
     *      ]
     * ]
     *
     * @param $route_config
     */
    private static function _parse_route_config($route_config)
    {
        //dev_dump(['route_config'=>$route_config]);
        /* 将配置文件中所有非正则表达式的项作为全匹配路径全部进本地缓存 */
        if(empty(LocalCache::get('route_full_path')) && isset($route_config['direct-uri']))
        {
            $tmp = [];
            foreach ($route_config['direct-uri'] as $k=>$v)
            {
                //处理成[controller,action]的数组
                $tmp[$k] = explode('/',$v);
            }
            LocalCache::set('route_full_path',$tmp);
            //dev_dump(['route.full.path'=>$tmp]);
        }
        unset($route_config['direct-uri']);
        if(empty($route_config) || !empty(LocalCache::get('route_snapshot')))
            return;
        $snapshot = [];
        //对每一条规则解析
        foreach ($route_config as $pattern=>$real)
        {
            $patterns = explode('/',$pattern);
            $tmp = &$snapshot;
            //对这条规则中的每一个segment解析，'item/del/\d'有三个segment
            foreach ($patterns as $k=>$p)
            {
                $chs = str_split($p);
                //正则表达式segment
                if(in_array_ext(['.','*','(','\\','+','?','$'],$chs))
                    break;
                //前面没有规则出现过这个segment
                if(!isset($tmp[$p]))
                {
                    $arr = explode('/',$real);
                    $tmp[$p][self::CAA][] = ucwords(array_shift($arr));
                    $tmp[$p][self::CAA][] = array_shift($arr);
                    empty($arr) || $tmp[$p][self::PAP] = $arr;
                }
                $tmp = &$tmp[$p];
            }
        }
        LocalCache::set('route_snapshot',$snapshot);
        //dev_dump(['route.snapshot'=>$snapshot]);
    }

    protected function currentParse($routes)
    {
        $this->controller = ucwords(array_shift($routes));
        $this->action = array_shift($routes);
        //没有&符号的情况，默认为/user/login/ethan/111111的形式
        if(empty($this->queryArray))
        {
            $this->params = $routes;
        } else {
            //为index.php?r=user/login&name=ethan&password=111的形式
            $this->params = $this->queryArray;
        }
        return true;
    }

    /**
     * 解析当前uri
     * @return bool
     */
    protected function parseRoute()
    {
        $snapshot = LocalCache::get("route_snapshot");
        //dev_dump(['parseRoute:snapshot'=>$snapshot]);
        /**
         * 路由直接配置在route.php中，非正则表达式，
         * 直接解析得到controller+action即可，这时认为没有参数
         */
        if(isset($this->config['direct-uri'][$this->path]))
        {
            $c_a = $this->config['direct-uri'][$this->path];
            $route = LocalCache::get('route_full_path');
            list($this->controller,$this->action) = $route[$c_a];
            return true;
        }
        $routes = explode('/',$this->path);
        $first = $routes[0];
        /* 凡是不在快照中的，都即时解析 */
        if(!isset($snapshot[$first]))
            return $this->currentParse($routes);
        if(!empty($snapshot)) {
            $rTmp = $routes;
            foreach ($rTmp as $k => $token) {
                if (!isset($snapshot[$token]))
                    break;
                $snapshot = $snapshot[$token];
                unset($rTmp[$k]);
            }
            //取controller和action
            list($this->controller, $this->action) = $snapshot[self::CAA];
            foreach ($snapshot[self::PAP] as $k => $p) {
                if (substr($p, 0, 1) == '$') {
                    $index = intval(ltrim($p, '$'));
                    /**
                     * 取uri对应位置的值作为参数，其实只处理uri上带的参数即可，不支持
                     * 'item/abcc(\d+)' => 'Post/view/$1'这样的解析,$1将被替换为abccxx
                     */
                    $this->params[] = $rTmp[$index - 1];
                    continue;
                }
                $this->params[] = $p; //常量参数直接填入
            }
            return true;
        }
    }

    public function dispatch()
    {
        $this->parseRoute();
        $controller = 'app\\controller\\'.$this->controller.'Controller';
        if(!class_exists($controller))
            throw new ServerException("Controller {$controller} 不存在！ \n");
        $class = new $controller();
        if(!method_exists($class,$this->action))
            throw new ServerException("$class 中方法 {$this->action} 不存在 !\n");
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
