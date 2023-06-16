<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:46:26
 * @LastEditors: light
 * @LastEditTime: 2023-05-05 13:52:16
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\demo\controller;
use sunphp\email\SunEmail;

/* 前端默认入口示例 */
class Email {


    public function send(){

        // 图片上传
        $params=[
            'email'=>'1457466046@qq.com',//用户邮箱
            'name'=>'用户昵称',
            'title'=>'邮件标题',
            'content'=>'邮件正文内容'
        ];
		$res = SunEmail::send($params);
        if($res){
            echo "邮件发送成功";
        }
    }

}