<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-17 09:38:53
 * @LastEditors: light
 * @LastEditTime: 2023-05-25 18:14:49
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\admin\controller;
use app\admin\model\CorePay;

class Pay extends Base{

    protected $middleware=[\app\admin\middleware\AuthAdmin::class];

    public function get(){
        $s=CorePay::where('acid',0)->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$s);
    }
    public function update(){
        $post=$this->request->post();
        $s=CorePay::where('acid',0)->find();
        if(empty($s)){
            $post['acid']=0;
            CorePay::create($post,['acid','wx_switch','ali_switch','wx_mchid','wx_apikey','wx_cert','wx_public_cert','ali_appid','ali_appkey','ali_app_cert','ali_public_cert','ali_root_cert']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }
}