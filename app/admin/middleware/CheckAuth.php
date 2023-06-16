<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-24 11:39:45
 * @LastEditors: light
 * @LastEditTime: 2023-05-17 15:14:08
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\middleware;

use app\admin\model\CoreAccount;
use app\admin\model\CoreApp;
use app\admin\model\CoreBindapp;


// 用户前端+后端是否具有使用权限验证
class CheckAuth{
    public function handle($request,\Closure $next){
        $pathinfo=strtolower($request->pathinfo());

        $whitelist=[
            "/payment/wechat/notify.php",//APIV3版本
            "/payment/wechat/notifyV2.php",
            "/payment/alipay/notify.php",
            "/payment/alipay/return.php",
        ];

        //拦截回调通知payResult，严禁修改！！！
        if(preg_match("/payresult\/notify/",$pathinfo)||preg_match("/payresult\/return/",$pathinfo)){
            if(!in_array($request->root(),$whitelist)){
                return response('无访问权限');
            }
        }

        //admin模块不校验
        if(empty($pathinfo)||$pathinfo=='admin'||preg_match("/^admin\//",$pathinfo)
        ||$pathinfo=='payresult/notify'||$pathinfo=='payresult/return'){
            return $next($request);
        }

        $get=$request->get();
        if(empty($get['i'])){
            return response('页面参数错误');
        }
        //检查平台
        $account=CoreAccount::where('id',$get['i'])->where('is_delete',0)->find();
        if(empty($account)){
            return response('平台不存在');
        }

        //检查应用
        // 全局中间件获取不到应用
        // $identity=app('http')->getName();

        $identity=explode('/',$pathinfo)[0];
        $app=CoreApp::where('identity',$identity)->where('dir','app')->where('is_delete',0)->find();
        if(empty($app)){
            return response('应用不存在');
        }

        //检查平台是否绑定应用
        $can_use=CoreBindapp::alias('a')->join('core_supports b','a.sid=b.id')
        ->where(['a.acid'=>$account['id'],'b.app_id'=>$app['id']])->find();
        if(empty($can_use)){
            return response('平台未绑定应用');
        }

        $request->account=$account->toArray();
        $request->app=$app->toArray();

        return $next($request);

    }
}