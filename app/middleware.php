<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-09 09:46:15
 * @LastEditors: light
 * @LastEditTime: 2023-05-15 15:28:56
 * @Description: SonLight Tech版权所有
 */
// 全局中间件定义文件
defined('SUN_IN') or exit('Sunphp Access Denied');

return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    'check_auth',
    \think\middleware\SessionInit::class
];
