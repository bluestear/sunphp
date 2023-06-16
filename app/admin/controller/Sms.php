<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-17 10:00:17
 * @LastEditors: light
 * @LastEditTime: 2023-04-06 10:57:13
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\admin\controller;
use app\admin\model\CoreSms;

class Sms extends Base{

    protected $middleware=[\app\admin\middleware\AuthAdmin::class];


    public function get(){
        $s=CoreSms::where('acid',0)->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$s);
    }
    public function update(){
        $post=$this->request->post();
        $s=CoreSms::where('acid',0)->find();
        if(empty($s)){
            $post['acid']=0;
            CoreSms::create($post,['acid','type','ali_sms','tencent_sms']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }
}