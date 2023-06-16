<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-20 09:30:50
 * @LastEditors: light
 * @LastEditTime: 2023-05-29 16:14:51
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);

namespace app\admin\controller;
use sunphp\account\SunAccount;
use sunphp\pay\SunPay;
use think\facade\Log;

class Sunphp extends Base{

    public function wx(){
        $get=$this->request->get();
        if(empty($get['open_url'])||empty($get['i'])||empty($get['t'])||empty($get['scope'])){
            return response('url链接错误');
        }
        session('target_url_'.$get['i'],$get['t']);
        if($get['scope']=='snsapi_base'){
            //静默授权跳转
            return redirect($get['open_url']);
        }else{
            return view('wx',[
                'open_url'=>$get['open_url']
            ]);
        }
    }

    public function callback(){
        $get=$this->request->get();
        $account=SunAccount::create($get['state']);
        $userinfo=$account->userinfo();
        $targetUrl = session('target_url_'.$get['state']);


        //snsapi_base静默授权校验
        if(!empty($userinfo['nickname'])&&!empty($userinfo['avatar'])){
            session('wechat_user_'.$get['state'],$userinfo['raw']);
            return redirect($targetUrl);
        }else{
            $account->login('snsapi_userinfo',$get['state'],urlencode($targetUrl));
        }
    }


    /* 微信API-v2支付（不推荐）jsapi支付util.js */
    public function payV2(){
        $get=$this->request->get();
        $acid=$get['i'];
        $account=SunAccount::create($acid);
        $userinfo=$account->login();

        $post=$this->request->post();
        $params=[
            'acid'=>$acid,
            'module'=>$post['module'],//模块标识
            'pay_method'=>$post['payMethod'],//支付方式wechat,alipay
            'tid' => $post['orderTid'],
            'money'=>$post['orderFee'],//单位是元
            'title' => $post['orderTitle'],
            'openid'=>$userinfo['openid']
        ];

        $sunpay=SunPay::wechatV2($acid);
        $order=$sunpay->pay($params);

        $data=$sunpay->getJssdkConfig()->bridgeConfig($order['prepay_id'],false);
        //两种都可以，注意timeStamp大小写区别
        // $data=$sunpay->getJssdkConfig()->sdkConfig($order['prepay_id']);

        return json($data);
    }


        /* 微信API-v3支付 jsapi支付util.js */
        public function pay(){
            $get=$this->request->get();
            $acid=$get['i'];
            $account=SunAccount::create($acid);
            $userinfo=$account->login();

            $post=$this->request->post();

            $params = [
                'acid'=>$acid,
                'module'=>$post['module'],//模块标识
                'pay_method'=>$post['payMethod'],
                'tid' => $post['orderTid'],
                'money'=>$post['orderFee'],//单位是元
                'title' => $post['orderTitle'],
                'openid' => $userinfo['openid']
                // 'attach'=>$attach
            ];
            $wechat=SunPay::wechat($acid);
            $data = $wechat->mp($params);
            return json($data);
        }


    /* 微信手机网站H5支付API-V3 */
    /* 微信通过referer来判断来源，不能进入页面就支付 */
    public function wechatWap(){
        $get=$this->request->get();
        $acid=$get['i'];


        $post=$this->request->post();

        $params = [
            'acid'=>$acid,
            'module'=>$post['module'],//模块标识
            'pay_method'=>$post['payMethod'],
            'tid' => $post['orderTid'],
            'money'=>$post['orderFee'],//单位是元
            'title' => $post['orderTitle']
        ];

        $wechat=SunPay::wechat($acid);
        $result=$wechat->wap($params)->all();
        $h5_url=$result['h5_url'];

        $data=[
            'h5_url'=>$h5_url
        ];
        return json($data);
    }


    /* 微信APP支付API-V3 */
    public function wechatApp(){
        $get=$this->request->get();
        $acid=$get['i'];


        $post=$this->request->post();

        $params = [
            'acid'=>$acid,
            'module'=>$post['module'],//模块标识
            'pay_method'=>$post['payMethod'],
            'tid' => $post['orderTid'],
            'money'=>$post['orderFee'],//单位是元
            'title' => $post['orderTitle']
        ];

        $wechat=SunPay::wechat($acid);
        return $wechat->app($params);
    }

     /* 微信扫码支付API-V3 */
     public function wechatScan(){
        $get=$this->request->get();
        $acid=$get['i'];


        $post=$this->request->post();

        $params = [
            'acid'=>$acid,
            'module'=>$post['module'],//模块标识
            'pay_method'=>$post['payMethod'],
            'tid' => $post['orderTid'],
            'money'=>$post['orderFee'],//单位是元
            'title' => $post['orderTitle']
        ];

        $wechat=SunPay::wechat($acid);

        $result = $wechat->scan($params);
        $code_url = $result->code_url; // 二维码 url

        $data=[
            'code_url'=>$code_url
        ];
        return json($data);

    }


    /* 支付宝Web支付 */
    public function alipayWeb(){
        $get=$this->request->get();
        $acid=$get['i'];

        $params = [
            'acid'=>$acid,
            'module'=>$get['module'],//模块标识
            'pay_method'=>$get['payMethod'],
            'tid' => $get['orderTid'],
            'money'=>$get['orderFee'],//单位是元
            'title' => $get['orderTitle']
        ];

        $alipay=SunPay::alipay($acid);
        return $alipay->web($params);
    }

    /* 支付宝手机H5网页支付 */
    public function alipayH5(){
        $get=$this->request->get();
        $acid=$get['i'];

        $params = [
            'acid'=>$acid,
            'module'=>$get['module'],//模块标识
            'pay_method'=>$get['payMethod'],
            'tid' => $get['orderTid'],
            'money'=>$get['orderFee'],//单位是元
            'title' => $get['orderTitle']
        ];

        $alipay=SunPay::alipay($acid);
        // 自定义回调参数
        return $alipay->wap($params);
    }



}