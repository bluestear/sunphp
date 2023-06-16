<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:46:26
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 15:34:17
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\demo\controller;
use sunphp\account\SunAccount;

/* 前端默认入口示例 */
class Wx {

    /* 默认入口 */
    public function index(){
        return "我的demo应用前端入口！";
    }

    /* 微信公众号登录 */
    public function login(){
        $account=SunAccount::create();
        $userinfo=$account->login();
        dump($userinfo);
    }

    /* 微信公众号发送模板消息 */
    public function sendTplNotice(){
        //用户id
        $openid='用户的openid';
        //模板id
        $template_id='公众号模板id';
        //模板数据，根据模板自定义格式
        $data = array(
			'first' => array(
				'value' => '标题',
				'color' => '#000000'
			),
			'keyword1' => array(
				'value' => '第一段话',
				'color' => '#000000'
			),
			'keyword2' => array(
				'value' => '第二段话',
				'color' => '#000000'
			),
			'keyword3' => array(
				'value' => '第三段话',
				'color' => '#000000'
			),
			'remark' => array(
				'value' => '备注消息',
				'color' => '#000000'
			),
		);
        $url="";// 点击模板跳转网址
        $miniprogram=[
            // 'appid' => '跳转小程序的appid',
            // 'pagepath' => '跳转小程序的页面',
        ];
        $account=SunAccount::create();
        $result=$account->sendTplNotice($openid,$template_id,$data,$url,$miniprogram);
        dump($result);
    }


    /* 获取网页jssdk */
    public function getJssdkConfig(){
        $account=SunAccount::create();
        $jssdk=$account->getJssdkConfig();
        dump($jssdk);
    }

    /* 获取粉丝信息 */
    public function fansQueryInfo(){
        $account=SunAccount::create();
        $openid='用户的openid信息';
        $fans=$account->fansQueryInfo($openid);
        // 通过subscribe==1判断是否关注
        dump($fans);
    }

    /* 获取easywechat的app对象，从而使用全部的接口功能 */
    /* 5.x文档地址：https://easywechat.com/5.x/basic-services/media.html */
    public function getApp(){
        $app=SunAccount::create()->getApp();

        // 自行编写功能，如：图片上传
        // $path="图片地址"
        // $app->media->uploadImage($path);
        // ...

    }



}