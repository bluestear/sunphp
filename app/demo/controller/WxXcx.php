<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:46:26
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 15:39:12
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);

use app\admin\controller\Sunphp;
use sunphp\account\SunAccount;
use sunphp\pay\SunPay;

/* 微信小程序常见接口示例 */
class WxXcx {


    /* 获取用户的openid */
    public function getOpenid(){
        $code="小程序前端获取的code";
        $account=SunAccount::create();
        $data=$account->session($code);

        //获取到的数据格式如下
        // $data=[
        //     'openid'=>"获取到的用户openid",
        //     'session_key'=>"获取到的session值",
        // ];
    }

    /* 解密用户信息 */
    public function getUserinfo(){
        $session_key="之前获取的session_key";
        $iv="iv数据";
        $encryptedData="encryptedData数据";
        $account=SunAccount::create();
        $data=$account->decryptData($session_key, $iv, $encryptedData);
    }


     /* 微信小程序支付API-V3 */
     public function wechatMini(){
        $wechat=SunPay::wechat();
        $order = [
            'tid'=>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money'=>0.02,//金额：元
            'title'=>'微信小程序支付标题',
            'openid'=>'用户的openid'
        ];
        return $wechat->mini($order);
    }


     /* 获取easywechat的app对象，从而使用全部的接口功能 */
    /* 5.x文档地址：https://easywechat.com/5.x/mini-program/subscribe_message.html */
    public function getApp(){
        $app=SunAccount::create()->getApp();

        // 自行编写功能，如：发送小程序订阅消息
        // $data = [
        //     'template_id' => 'bDmywsp2oEHjwAadTGKkUJ-eJEiMiOf7H-dZ7wjdw80', // 所需下发的订阅模板id
        //     'touser' => 'oSyZp5OBNPBRhG-7BVgWxbiNZm',     // 接收者（用户）的 openid
        //     'page' => '',       // 点击模板卡片后的跳转页面，仅限本小程序内的页面。支持带参数,（示例index?foo=bar）。该字段不填则模板无跳转。
        //     'data' => [         // 模板内容，格式形如 { "key1": { "value": any }, "key2": { "value": any } }
        //         'date01' => [
        //             'value' => '2019-12-01',
        //         ],
        //         'number01' => [
        //             'value' => 10,
        //         ],
        //     ],
        // ];

        // $app->subscribe_message->send($data);

    }





}