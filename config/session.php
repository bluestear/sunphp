<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-09 09:46:15
 * @LastEditors: light
 * @LastEditTime: 2023-04-12 14:17:03
 * @Description: SonLight Tech版权所有
 */
// +----------------------------------------------------------------------
// | 会话设置
// +----------------------------------------------------------------------
defined('SUN_IN') or exit('Sunphp Access Denied');

return [
    // session name
    'name'           => 'SUNPHPSESSID',
    // SESSION_ID的提交变量,解决flash上传跨域
    'var_session_id' => '',
    // 驱动方式 支持file cache
    'type'           => 'file',
    // 存储连接标识 当type使用cache的时候有效
    'store'          => null,
    // 过期时间
    'expire'         => 1440,
    // 前缀
    'prefix'         => '',
];
