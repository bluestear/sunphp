<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-14 14:16:33
 * @LastEditors: light
 * @LastEditTime: 2023-04-06 10:57:20
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\CoreStorage;

class Storage extends Base{

    protected $middleware=[\app\admin\middleware\AuthAdmin::class];

    public function update(){
        $post=$this->request->post();

        $s=CoreStorage::where('acid',0)->find();
        if(empty($s)){
            $post['acid']=0;
            CoreStorage::create($post,['acid','suffix','img_size','video_size','file_size']);
        }else{
            // CoreStorage::update($post,['acid'=>0]);
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function get(){
        $s=CoreStorage::where('acid',0)->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$s);
    }

    public function oss(){
        $post=$this->request->post();
        $s=CoreStorage::where('acid',0)->find();
        if(empty($s)){
            $post['acid']=0;
            CoreStorage::create($post,['acid','type','ali_oss','tencent_cos','qiniu']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }

}