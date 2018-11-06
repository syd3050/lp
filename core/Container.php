<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-29
 * Time: 下午9:56
 */

namespace core;

use core\exception\ServerException;

/**
 *
 * //实例化容器类
 * $app =  new Container();
* //向容器中填充Dog
* $app->bind('Dog','App\Dog');
* //填充People
* $app->bind('People', 'App\People');
* //通过容器实现依赖注入，完成类的实例化；
* $people = $app->make('People');
* //调用方法
* echo $people->putDog();
 *
 * Class Container
 * @package app\controller
 */
class Container
{
    private $_binds = [];
    private $_singleton_binds = [];
    private $_singleton = [];
    private $_instances = [];
    private static $_instance = null;

    private function __construct()
    {
    }

    public static function getContainer()
    {
        if(!self::$_instance instanceof Container) {
            self::$_instance = new Container();
        }
        return self::$_instance;
    }

    public function bind($name,$stringOrClosure)
    {
        if(empty($name) || empty($stringOrClosure))
            throw new ServerException(__CLASS__."::".__FUNCTION__.",params can not be null");
        $this->_binds[$name] = $stringOrClosure;
        return $this;
    }

    public function singleton($name,$stringOrClosure)
    {
        if(empty($name) || empty($stringOrClosure))
            throw new ServerException(__CLASS__."::".__FUNCTION__.",params can not be null");
        $this->_singleton_binds[$name] = $stringOrClosure;
        return $this;
    }

    public function instance($name,$instance)
    {
        if(empty($name) || empty($instance))
            throw new ServerException(__CLASS__."::".__FUNCTION__.",params can not be null");
        $this->_instances[$name] = $instance;
        return $this;
    }

    private function _make_singleton($name,$param=[])
    {
        if(!isset($this->_singleton_binds[$name]))
            return false;
        if(isset($this->_singleton[$name])) {
            return $this->_singleton[$name];
        }
        $stringOrClosure = $this->_singleton_binds[$name];
        return $this->_singleton[$name] = $this->_build($stringOrClosure,$param);
    }

    public function make($name,$param=[])
    {
        if(!is_array($param))
            $param = [$param];
        if(isset($this->_instances[$name]))
            return $this->_instances[$name];
        if(!empty($this->_binds[$name]))
            $instance = $this->_build($this->_binds[$name],$param);
        else
            $instance = $this->_make_singleton($name,$param);
        if(!$instance) {
            throw new ServerException(__CLASS__."::".__FUNCTION__.".Need to bind {$name} first.");
        }
        return $instance;
    }

    private function _build($class,$param=[])
    {
        if($class instanceof \Closure) {
            return $class($this);
        }
        try{
            $reflector = new \ReflectionClass($class);
            if( ! $reflector->isInstantiable()){
                //抛出异常
                throw new ServerException(__CLASS__."::".__FUNCTION__.'无法实例化'.$class);
            }
            $constructor = $reflector->getConstructor();
            if(is_null($constructor)){
                return new $class;
            }
            if(empty($param)) {
                $dependencies = $constructor->getParameters();
                $instance = $this->getDependencies($dependencies);
            }else{
                $instance = $param;
            }
            return $reflector->newInstanceArgs($instance);
        }catch (\Exception $e) {
            //抛出异常
            throw new ServerException(__CLASS__."::".__FUNCTION__.'message:'.$e->getMessage().',code:'.$e->getCode());
        }
    }

    //通过反射解决参数依赖
    public function getDependencies(array $dependencies)
    {
        $results = [];
        foreach( $dependencies as $dependency ){
            $results[] = is_null($dependency->getClass())
                ?$this->resolvedNonClass($dependency)
                :$this->resolvedClass($dependency);
        }
        return $results;
    }

    //解决一个没有类型提示依赖
    public function resolvedNonClass(\ReflectionParameter $parameter)
    {
        if($parameter->isDefaultValueAvailable()){
            return $parameter->getDefaultValue();
        }
        switch ($parameter->getType()) {
            case 'int':
                $value = 0;
                break;
            case 'array':
                $value = [];
                break;
            case 'string':
                $value = '';
                break;
            default:
                $value = null;
        }
        return $value;
    }

    //通过容器解决依赖
    public function resolvedClass(\ReflectionParameter $parameter)
    {
        return $this->make($parameter->getClass()->name);

    }

}