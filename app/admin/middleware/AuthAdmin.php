<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-24 11:39:45
 * @LastEditors: light
 * @LastEditTime: 2023-04-17 11:13:24
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\middleware;

use app\admin\model\CoreUseaccount;
use app\admin\model\CoreUser;

class AuthAdmin{

    public function handle($request,\Closure $next){

        //权限1
        $user=$request->middleware('user');

        if($user['type']!=2){
            return jsonResult(404, '无系统管理员权限', []);
        }

        return $next($request);
    }
}