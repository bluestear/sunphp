<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-09 09:46:15
 * @LastEditors: light
 * @LastEditTime: 2023-05-30 15:43:42
 * @Description: SonLight Tech版权所有
 */

// +----------------------------------------------------------------------
// | 缓存设置
// +----------------------------------------------------------------------
defined('SUN_IN') or exit('Sunphp Access Denied');

return [
    // 默认缓存驱动
    'default' => env('cache.driver', 'file'),

    // 缓存连接方式配置
    'stores'  => [
        'file' => [
            // 驱动方式
            'type'       => 'File',
            // 缓存保存目录
            'path'       => '',
            // 缓存前缀
            'prefix'     => '',
            // 缓存有效期 0表示永久缓存
            'expire'     => 0,
            // 缓存标签前缀
            'tag_prefix' => 'tag:',
            // 序列化机制 例如 ['serialize', 'unserialize']
            'serialize'  => [],
        ],
        // 更多的缓存连接
        'redis'    => [
            'type'       => 'redis',
            'host'       => env('redis.host','127.0.0.1'),
            'port'       => env('redis.port',6379),
            'password'   => env('redis.password',''),
            // 缓存前缀
            'prefix'     => env('redis.prefix',''),
            // 缓存有效期 0表示永久缓存
            'expire'     => env('redis.expire',0),
        ],
    ],
];
