<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-29
 * Time: 下午9:56
 */

namespace core;


use core\exception\ConfigException;
use core\exception\ServerException;
use http\Exception\RuntimeException;

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
    /**
     *  容器绑定，用来装提供的实例或者 提供实例的回调函数
     * @var array
     */
    public $building = [];

    /**
     * 绑定到容器
     * @param $abstract
     * @param $concrete
     * @param bool $shared
     */
    public function bind($abstract, $concrete, $shared = false)
    {
        if(empty($concrete)){
            throw new \RuntimeException(__CLASS__."::".__FUNCTION__.".Parameter concrete is needed!");
        }
        if(!$concrete instanceOf \Closure){
            $concrete = function(Container $c) use($concrete){
                return $c->make($concrete);
            };
        }
        $this->building[$abstract] =  compact("concrete", "shared");
    }

    public function singleton($abstract, $concrete, $shared = true){
        $this->bind($abstract, $concrete, $shared);
    }

    /**
     * 生成实例
     * @param $abstract
     * @return mixed|object
     * @throws ServerException
     */
    public function make($abstract)
    {
        $concrete = $this->getConcrete($abstract);
        return $this->build($concrete);
    }

    /**
     * 是否可以创建服务实体
     * 1.具体类和抽象类一致，可以build
     * 2.具体类是一个闭包，可以build
     * @param $concrete
     * @param $abstract
     * @return bool
     */
    public function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof \Closure;
    }

    /**
     * 获取绑定的回调函数
     * 1.abstract已经绑定过，直接返回回调函数
     * 2.abstract没有绑定过，返回它本身
     * @param $abstract
     * @return mixed
     */
    public function getConcrete($abstract)
    {
        if(! isset($this->building[$abstract])){
            return $abstract;
        }
        return $this->building[$abstract]['concrete'];
    }

    /**
     * 根据实例具体名称实例具体对象
     * @param $concrete
     * @return mixed|object
     * @throws ServerException
     */
    public function build($concrete)
    {
        if($concrete instanceof \Closure){
            return $concrete($this);
        }
        try{
            $reflector = new \ReflectionClass($concrete);
            if( ! $reflector->isInstantiable()){
                //抛出异常
                throw new ServerException(__CLASS__."::".__FUNCTION__.'无法实例化'.$concrete);
            }
            $constructor = $reflector->getConstructor();
            if(is_null($constructor)){
                return new $concrete;
            }
            $dependencies = $constructor->getParameters();
            $instance = $this->getDependencies($dependencies);
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
        throw new ConfigException(__CLASS__."::".__FUNCTION__.".参数{$parameter->getName()}没有默认值");

    }

    //通过容器解决依赖
    public function resolvedClass(\ReflectionParameter $parameter)
    {
        return $this->make($parameter->getClass()->name);

    }

}