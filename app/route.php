<?php
/**
 * 路由配置信息，支持简单的正则表达式
 * User: syd
 * Date: 18-9-26
 * Time: 下午1:41
 */
return [
    /* 默认控制器 */
    'default_controller' => 'Index',
    /* 默认方法 */
    'default_action'     => 'index',
    /*
     * 右侧value的构成必须是controller/action/$1/$2/../$n的形式
     * 将类似item/123的路由解析到PostController对应的view方法，参数取123
     * 不支持'item/abcc(\d+)' => 'Post/view/$1'这样的解析,$1将被替换为abccxx,而不是数字
     */
    'item/\d+'           => 'Post/view/$1',
    /*
     * direct-uri项标识其包含的所有配置项都不含正则表达式，跟uri一一对应，例如
       'direct-uri'  => [
            'user/list' => 'User/all',
        ]
     */
    'direct-uri'  => [
        'user/list' => 'User/all',
    ],

];