<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-09 09:46:15
 * @LastEditors: light
 * @LastEditTime: 2023-02-28 14:54:56
 * @Description: SonLight Tech版权所有
 */
declare (strict_types = 1);

namespace app;

defined('SUN_IN') or exit('Sunphp Access Denied');

use think\Service;

/**
 * 应用服务类
 */
class AppService extends Service
{
    public function register()
    {
        // 服务注册
    }

    public function boot()
    {
        // 服务启动
    }
}
