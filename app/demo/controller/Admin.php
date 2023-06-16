<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:11:22
 * @LastEditors: light
 * @LastEditTime: 2023-05-17 15:16:48
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\demo\controller;

use sunphp\core\SunHelper;

/* 管理后台登录验证示例 */
class Admin {

    //管理后台入口验证登录+权限
    protected $middleware=['check_login'];

    public function index(){
        return "我的demo示例应用后台入口！";
    }

    public function user(){
        /* 获取当前用户，可以使用的模块菜单列表 */
        $menus=SunHelper::getMenus();
        dump($menus);
        return "我的demo应用后台入口！";
    }

    public function msg(){
        return "我的demo应用后台入口！";
    }

    public function test(){
        return "我的demo应用后台入口！";
    }

    public function sun(){
        return "我的demo应用后台入口！";
    }


}