# lp

##依赖
1.swoole-4.0及以上
* 在编译swoole时增加--enable-async-redis

2.hiredis
 * 下载hiredis源码，执行以下命令编译:
 * sudo make
 * sudo make install
 * sudo ldconfig
 
 3.Yac组件
  * 下载源码，位置：http://pecl.php.net/package/yac
  * 编译：
  
    1.phpize
    
    2../configure --with-php-config=/usr/local/php/bin/php-config
    
    3.sudo make
    
    4.sudo make install
    
    5.修改php.ini文件
    
    extension = yac.so
    
    yac.enable = 1
    
    yac.keys_memory_size = 4M ; 4M can get 30K key slots, 32M can get 100K key slots
    
    yac.values_memory_size = 64M
    
    yac.compress_threshold = -1
    
    yac.enable_cli = 0 ; whether enable yac with cli, default 0
    
##使用
1.配置文件组件

1.1获取普通配置文件配置项使用实例：
 * //获取整个config配置文件
 * Config::get('config');
 *
 * //获取app/config.php文件中的log配置项信息
 * Config::get('config','log');
 *
 * //获取app/route.php文件中的default_controller配置项信息
 * Config::get('route','default_controller');
 *
 * //获取app/hook.php文件中的pre配置项信息
 * Config::get('hook','pre');

1.2.获取环境变量配置项使用实例
 * //根据当前环境获取对应的配置项，可能是来自app/env/dev.php，app/env/prod.php或者app/env/test.php，
 * //获取整个配置文件
 * Config::getEnv();
 *
 * Config::getEnv('register')