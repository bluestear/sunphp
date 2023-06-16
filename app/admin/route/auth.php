<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-04-05 11:24:24
 * @LastEditors: light
 * @LastEditTime: 2023-05-17 10:03:57
 * @Description: SonLight Tech版权所有
 */

use think\facade\Route;

//简短访问自动跳转到完整路由
Route::rule('','index/index');
Route::rule('index/index','index/index');



//分组路由自带层级关系，支持嵌套
//权限1：系统管理员




//权限2：系统管理员+平台管理员
Route::group('account',function(){
    Route::rule('adduser','account/adduser');
    Route::rule('deluser','account/deluser');
    Route::rule('update','account/update');
    Route::rule('userList','account/userList');

})->middleware([\app\admin\middleware\AuthAccount::class]);

Route::group('menu',function(){
    Route::rule('userList','menu/userList');
    Route::rule('setList','menu/setList');
})->middleware([\app\admin\middleware\AuthAccount::class]);




//权限3：系统管理员+平台管理员+操作员
Route::group('account',function(){
    Route::rule('myapp','account/myapp');
    Route::rule('module','account/module');
    Route::rule('top','account/top');
    Route::rule('getPay','account/getPay');
    Route::rule('updatePay','account/updatePay');
    Route::rule('getStorage','account/getStorage');
    Route::rule('updateStorage','account/updateStorage');
    Route::rule('getSms','account/getSms');
    Route::rule('updateSms','account/updateSms');
    Route::rule('getEmail','account/getEmail');
    Route::rule('updateEmail','account/updateEmail');

})->middleware([\app\admin\middleware\AuthUser::class]);
