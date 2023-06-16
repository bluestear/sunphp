<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-09 09:46:15
 * @LastEditors: light
 * @LastEditTime: 2023-03-07 17:02:37
 * @Description: SonLight Tech版权所有
 */
defined('SUN_IN') or exit('Sunphp Access Denied');

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            // 'root' => app()->getRuntimePath() . 'storage',
            'root'       => app()->getRootPath() . 'attachment',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
    ],
];
