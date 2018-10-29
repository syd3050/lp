<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-29
 * Time: 下午9:56
 */

namespace core;


use core\exception\ConfigException;

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
     * 注册一个绑定到容器
     */
    public function bind($abstract='Dog', $concrete = 'App\Dog', $shared = false)
    {
        if(is_null($concrete)){
            $concrete = $abstract;
        }

        if(!$concrete instanceOf \Closure){
            $concrete = $this->getClosure($abstract, $concrete);
            /**
            $concrete = function($c) use ('Dog', 'App\Dog') {
            return $c->make('App\Dog');
            }
             */
        }
        /**
        $this->building['Dog'] = [
        'concrete' => function($c) use ('Dog', 'App\Dog') {
        return $c->make('App\Dog');
        },
        'shared'   => false
        ];
         */
        $this->building[$abstract] =  compact("concrete", "shared");
    }

    //注册一个共享的绑定 单例
    public function singleton($abstract, $concrete, $shared = true){
        $this->bind($abstract, $concrete, $shared);
    }

    /**
     * 默认生成实例的回调闭包
     *
     * @param $abstract
     * @param $concrete
     * @return \Closure
     */
    public function getClosure($abstract='Dog', $concrete = 'App\Dog')
    {
        /**
        return function($c) use ('Dog', 'App\Dog') {
        return $c->make('App\Dog');
        }

         */
        return function($c) use($abstract, $concrete){
            $method = ($abstract == $concrete)? 'build' : 'make';

            return $c->$method($concrete);
        };
    }

    /**
     * 生成实例
     */
    public function make($abstract='App\Dog')
    {
        /**
         * $concrete = 'App\Dog';
         */
        $concrete = $this->getConcrete($abstract);

        if($this->isBuildable($concrete, $abstract)){
            //$object = new Dog(...);
            $object = $this->build($concrete);
        }else{
            $object = $this->make($concrete);
        }

        return $object;
    }

    /**
     * 获取绑定的回调函数
     */
    public function getConcrete($abstract='App\Dog')
    {
        if(! isset($this->building[$abstract])){
            return $abstract;
        }

        return $this->building[$abstract]['concrete'];
    }

    /**
     * 判断 是否 可以创建服务实体
     * 1.具体类和抽象类一致，可以build
     * 2.具体类是一个闭包，可以build
     */
    public function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof \Closure;
    }

    /**
     * 根据实例具体名称实例具体对象
     */
    public function build($concrete)
    {
        /**
         * 闭包则直接返回
         */
        if($concrete instanceof \Closure){
            return $concrete($this);
        }

        //创建反射对象
        $reflector = new \ReflectionClass($concrete);

        if( ! $reflector->isInstantiable()){
            //抛出异常
            throw new \Exception('无法实例化');
        }

        $constructor = $reflector->getConstructor();
        if(is_null($constructor)){
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();
        $instance = $this->getDependencies($dependencies);

        return $reflector->newInstanceArgs($instance);

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
        throw new ConfigException(__CLASS__."::".__FUNCTION__.".");

    }

    //通过容器解决依赖
    public function resolvedClass(\ReflectionParameter $parameter)
    {
        return $this->make($parameter->getClass()->name);

    }

}