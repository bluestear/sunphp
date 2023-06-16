<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-09 09:46:15
 * @LastEditors: light
 * @LastEditTime: 2023-04-16 11:49:44
 * @Description: SonLight Tech版权所有
 */

defined('SUN_IN') or exit('Sunphp Access Denied');

// 中间件配置
return [
    // 别名或分组
    'alias'    => [
        'check_login'=>[
            app\admin\middleware\CheckLogin::class
        ],
        'check_auth'=>[
            app\admin\middleware\CheckAuth::class
        ]
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [

    ],
];
