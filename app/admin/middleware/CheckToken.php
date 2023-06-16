<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-24 11:39:45
 * @LastEditors: light
 * @LastEditTime: 2023-04-20 16:56:02
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\middleware;

use app\admin\model\CoreUser;
use sunphp\jwt\SunJwt;

class CheckToken{
    public function handle($request,\Closure $next){

        $pathinfo=strtolower($request->pathinfo());
        $whitelist=[
            'index/index',
            'system/global',
            'system/install',
            'user/login',
            'user/register',
            'token/refresh'
        ];

        //无需拦截的路由
        if(in_array($pathinfo,$whitelist)||empty($pathinfo)||preg_match("/^sunphp\//",$pathinfo)){
            return $next($request);
        }

        $token=$request->header('token');
        $session_id=$request->post('session_id','');

        if(empty($session_id)||empty($token)){
			return jsonResult(402, "用户未登录", []);
			die();
        }


        try {
			$res = SunJwt::verifyJwt($token, $session_id);
			if ($res['status'] == 1000) {
                //检查用户是否存在
                $user=CoreUser::where('session_id',$session_id)->where('is_delete',0)->find();
                if(empty($user)){
			        return jsonResult(402, "用户不存在", []);
				    die();
                }

                //保存在middleware中
                $request->user=$user->toArray();

                return $next($request);
			} else if ($res['status'] == 1003) {
				return jsonResult(403, "登录已过期", []);
				die();
			} else {
				return jsonResult(401, "签名错误", $res);
				die();
			}
		} catch (\Exception $e) {
			return jsonResult(402, "登录已失效", []);
			die();
		}

    }
}