<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-28 09:52:45
 * @LastEditors: light
 * @LastEditTime: 2023-03-14 10:12:00
 * @Description: SonLight Tech版权所有
 */
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ Sunphp应用入口文件 ]
namespace think;


define('SUN_IN', true);

require __DIR__ . '/vendor/autoload.php';

// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
