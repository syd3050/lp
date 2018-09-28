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
     */
    'item/\d+'           => 'Post/view/$1'
];