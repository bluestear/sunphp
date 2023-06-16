<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:46:26
 * @LastEditors: light
 * @LastEditTime: 2023-05-05 17:45:30
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\demo\controller;
use sunphp\sms\SunSms;

/* 短信示例 */
class Sms {

    /* 发送一条短信——阿里云短信 */
    public function sendAliSms(){
        $args=[
            "phoneNumbers" => "+8618871715453",
            "templateCode" => "SMS_140665220",
            "templateParam" => "{\"code\":\"8888\"}",
            // "signName" => "自定义签名（可选）"
        ];
        /* 第二个参数：1表示阿里云，2表示腾讯云 */
        $res=SunSms::send($args,1);
        if($res){
            echo "发送阿里云短信成功";
        }else{
            echo "发送阿里云短信失败";
        }
    }


    /* 发送一条短信——腾讯云短信 */
    public function sendTencentSms(){
        $args=[
            "PhoneNumberSet" => ["+8618871715453"],// * 示例如：+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号*/
            "TemplateId" => "1786569",//模板ID
            "TemplateParamSet" => ['123456'],/* 模板参数: 模板参数的个数需要与 TemplateId 对应模板的变量个数保持一致，若无模板参数，则设置为空*/
            // "SmsSdkAppId" => "SDKAppID（可选）",
            // "SignName" => "自定义签名（可选）",
            // "SenderId" => "国内短信无需填写（可选）"
        ];

        /* 第二个参数：1表示阿里云，2表示腾讯云 */
        $res=SunSms::send($args,2);
        if($res){
            echo "发送腾讯云短信成功";
        }else{
            echo "发送腾讯云短信失败";
        }
    }


}