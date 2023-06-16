<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-24 11:39:45
 * @LastEditors: light
 * @LastEditTime: 2023-04-17 11:13:46
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\middleware;

use app\admin\model\CoreUseaccount;
use app\admin\model\CoreUser;

class AuthUser{

    public function handle($request,\Closure $next){

        //权限3
        $post=$request->post();
        $user=$request->middleware('user');

        if($user['type']!=2){
            $role=CoreUseaccount::where(['uid'=>$user['id'],'acid'=>$post['acid']])->value('role');
            if(empty($role)){
                //无使用者权限，并且跳转404页面
                return jsonResult(404, '无使用者权限', []);
            }
        }

        return $next($request);
    }
}