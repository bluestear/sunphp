<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-17 10:00:17
 * @LastEditors: light
 * @LastEditTime: 2023-04-06 10:39:57
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\admin\controller;
use app\admin\model\CoreEmail;

class Email extends Base{

    protected $middleware=[\app\admin\middleware\AuthAdmin::class];


    public function get(){
        $s=CoreEmail::where('acid',0)->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$s);
    }
    public function update(){
        $post=$this->request->post();
        $s=CoreEmail::where('acid',0)->find();
        if(empty($s)){
            $post['acid']=0;
            CoreEmail::create($post,['acid','email_name','email_sender','email_code','email_smtp','email_sign']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }
}