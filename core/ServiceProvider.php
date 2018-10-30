<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-30
 * Time: 下午4:16
 */

namespace core;


abstract class ServiceProvider
{
    /**
     * @var Container $container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    abstract public function register();

}