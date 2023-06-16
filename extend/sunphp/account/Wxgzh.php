<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 15:00:20
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 14:55:53
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\account;

defined('SUN_IN') or exit('Sunphp Access Denied');

use config;
use EasyWeChat\Factory;

class Wxgzh {

    protected $config;

    public function __construct($config=[])
    {
        $this->config=$config;
    }

    public function getApp(){
        $config=$this->config;
        $app = Factory::officialAccount($config);
        return $app;
    }

    /* 获取wechat对象类里面的方法 */
    public function __call($name, $arguments)
    {
        $config=$this->config;
        $app = Factory::officialAccount($config);
        return $$app->$name(...$arguments);
    }

    public function login($scope='snsapi_base',$acid='',$target_url=''){
        $uniacid=request()->get('i',$acid);

        // 判断登录
        //来自app/index.php页面，session无法获取
        // 手动初始化session
        if(request()->baseUrl()=='/app/index.php'){
            app()->session->setId(cookie(config('session.name')));
            app()->session->init();
        }


        $userinfo=session('wechat_user_'.$uniacid);
        if(!empty($userinfo)){
            return $userinfo;
        }



        $config=$this->config;
        $config['oauth']=[
            // 'scopes'   => ['snsapi_userinfo'],
            'scopes'=>[$scope],
            'callback' => '/index.php/admin/sunphp/callback'
        ];

        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;

        //携带state参数
        $oauth->withState($uniacid);

        $redirectUrl = $oauth->redirect();

        $domain=request()->domain();
        $url=request()->url();

        //页面跳转session无法写入
        // session('target_url_'.$uniacid,$domain.$url);
        if(empty($target_url)){
            $target_url=urlencode($domain.$url);
        }

        $auth_url=$domain.'/index.php/admin/sunphp/wx?open_url='
        .urlencode($redirectUrl).'&i='.$uniacid.'&t='.$target_url.'&scope='.$scope;
        header("Location: {$auth_url}");
        die();
    }

    public function userinfo(){
        $config=$this->config;
        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;

        $code = request()->get('code');
        $user = $oauth->userFromCode($code);
        return $user->toArray();
    }

    public function sendTplNotice($openid,$template_id,$data,$url='',$miniprogram=''){
        $config=$this->config;
        $app = Factory::officialAccount($config);
        return $app->template_message->send([
            'touser' => $openid,
            'template_id' => $template_id,
            'url' => $url,
            'miniprogram' => $miniprogram,
            'data' => $data
        ]);
    }

    public function getAccessToken(){
        $config=$this->config;
        $app = Factory::officialAccount($config);
        return $app->user->getAccessToken();
    }

    public function getJssdkConfig(){
        $config=$this->config;
        $app = Factory::officialAccount($config);
        // json为false返回数组，反之json字符串
        return $app->jssdk->buildConfig($APIs=[], $debug = false, $beta = false, $json = false, $openTagList = []);
    }

    public function fansQueryInfo($openId){
        $config=$this->config;
        $app = Factory::officialAccount($config);
        return $app->user->get($openId);
    }


}