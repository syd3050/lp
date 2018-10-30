<?php
namespace app\service\provider;

use app\service\UserServiceImpl;
use core\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        // TODO: Implement register() method.
        $this->container->bind('userService',function (){
            return new UserServiceImpl();
        });
    }
}