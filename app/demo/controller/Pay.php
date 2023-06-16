<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:46:26
 * @LastEditors: light
 * @LastEditTime: 2023-05-04 09:10:38
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\demo\controller;

use sunphp\account\SunAccount;
use sunphp\pay\SunPay;
use think\facade\Log;

/* 前端默认入口示例 */
class Pay {

    /* 支付宝Web支付 */
    public function alipayWeb(){
        $alipay=SunPay::alipay();
        return $alipay->web([
            'tid' => date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money' => '0.01',//金额：元
            'title' => '支付宝web支付测试',
        ]);
    }

    /* 支付宝手机H5网页支付 */
    public function alipayH5(){
        $alipay=SunPay::alipay();
        // 自定义回调参数
        return $alipay->wap([
            'tid' =>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money' => '0.01',//金额：元
            'title' => '支付宝H5支付测试'
         ]);
    }

    /* 支付宝App支付 */
    public function alipayApp(){
        $alipay=SunPay::alipay();
        // 自定义回调参数
        return $alipay->app([
            'tid' =>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money' => '0.01',//金额：元
            'title' => '支付宝App支付测试'
         ]);
    }


    /* 支付宝小程序支付 */
    public function alipayMini(){
        $alipay=SunPay::alipay();
        // 自定义回调参数
        $result=$alipay->mini([
            'tid' =>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money' => '0.01',//金额：元
            'title' => '支付宝小程序支付测试',
            'buyer_id' => '2088622190161234',
         ]);
        return $result->get('trade_no');  // 支付宝交易号
    }


    /* 支付宝刷卡支付 */
    public function alipayPos(){
        $alipay=SunPay::alipay();
        // 自定义回调参数
        $result=$alipay->pos([
            'tid' =>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money' => '0.01',//金额：元
            'title' => '支付宝刷卡支付测试',
            'auth_code' => '2088622190161234',
         ]);
    }


    /* 支付宝扫码支付 */
    public function alipayScan(){
        $alipay=SunPay::alipay();
        // 自定义回调参数
        $result=$alipay->scan([
            'tid' =>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money' => '0.01',//金额：元
            'title' => '支付宝H5支付测试'
         ]);
        $qr_code = $result->qr_code; // 二维码 url
        return response($qr_code);
    }


    /* 支付账户转账，查询，退款，关闭，取消……操作方法参考V3版本文档既可 */
    /* 支付宝文档地址：https://pay.yansongda.cn/docs/v3/alipay/pay.html */
    /* 功能很多，一个账户转账演示示例 */
    public function alipayTransfer(){
        $alipay=SunPay::alipay();
        // 自定义回调参数
        $result = $alipay->transfer([
            'out_biz_no' => '202106051432',
            'trans_amount' => '0.01',
            'product_code' => 'TRANS_ACCOUNT_NO_PWD',
            'biz_scene' => 'DIRECT_TRANSFER',
            'payee_info' => [
                'identity' => 'ghdhjw7124@sandbox.com',
                'identity_type' => 'ALIPAY_LOGON_ID',
                'name' => '沙箱环境'
            ],
        ]);
    }


    /* 微信公众号支付API-V3 推荐 使用util.js文件自动发起支付*/
    public function wechat(){
        //确保用户已经登录
        $account=SunAccount::create();
        $userinfo=$account->login();

        $pay_data=[
            'tid'=>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money'=>0.01,//金额：元
            'title'=>'jsapi支付标题'
        ];
        return view('wechat',[
            'pay_data'=>json_encode($pay_data)
        ]);
    }

    public function wechatH5(){
        /* 微信通过referer来判断来源，不能进入页面就支付 */
        return view('wechatH5');
    }

    /* 微信手机网站H5支付API-V3 */
    /* 微信通过referer来判断来源，不能进入页面就支付 */
    public function wechatWap(){
        $wechat=SunPay::wechat();
        $order = [
            'tid'=>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money'=>0.02,//金额：元
            'title'=>'微信H5网站支付标题'
        ];
        $result=$wechat->wap($order)->all();
        $h5_url=$result['h5_url'];
        return redirect($h5_url);
    }


    /* 微信APP支付API-V3 */
    public function wechatApp(){
        $wechat=SunPay::wechat();
        $order = [
            'tid'=>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money'=>0.02,//金额：元
            'title'=>'微信APP支付标题'
        ];
        return $wechat->app($order);
    }

     /* 微信扫码支付API-V3 */
     public function wechatScan(){
        $wechat=SunPay::wechat();
        $order = [
            'tid'=>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'money'=>0.02,//金额：元
            'title'=>'微信扫码支付标题'
        ];
        $result = $wechat->scan($order);
        $code_url = $result->code_url; // 二维码 url
        return view('wechatScan',[
            'code_url'=>$code_url
        ]);
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



    /* 微信账户转账，查询，退款，关闭，取消……操作方法参考V3版本文档既可 */
    /* 微信文档地址：https://pay.yansongda.cn/docs/v3/alipay/pay.html */
    /* 功能很多，一个账户转账演示示例 */
    public function wechatTransfer(){
        $wechat=SunPay::wechat();
        // 自定义回调参数
        $order = [
            'out_batch_no' => time().'',
            'batch_name' => 'subject-测试',
            'batch_remark' => 'test',
            'total_amount' => 1,
            'total_num' => 1,
            'transfer_detail_list' => [
                [
                    'out_detail_no' => time().'-1',
                    'transfer_amount' => 1,
                    'transfer_remark' => 'test',
                    'openid' => 'MYE42l80oelYMDE34nYD456Xoy',
                    // 'user_name' => '名字'  // 明文传参即可，sdk 会自动加密
                ],
            ],
        ];
        $result = $wechat->transfer($order);
    }









    /* 微信公众号支付API-V2 不推荐 使用util.js文件自动发起支付*/
    /* 不推荐 */
    /* 不推荐 */
    /* 不推荐 */
    // public function wechatV2(){
    //     //确保用户已经登录
    //     $account=SunAccount::create();
    //     $userinfo=$account->login();

    //     $pay_data=[
    //         'tid'=>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),//模块内订单号
    //         'money'=>0.01,//金额：元
    //         'title'=>'jsapi支付标题'
    //     ];
    //     return view('wechatV2',[
    //         'pay_data'=>json_encode($pay_data)
    //     ]);
    // }


    /* 微信公众号jssdk支付——（不推荐）手动发起支付 */
    /* public function jssdkPay(){
        //确保用户已经登录
        $account=SunAccount::create();
        $userinfo=$account->login();

        $sunpay=SunPay::wechat();
        $jssdk=$sunpay->getJssdkConfig();

        $params=[
            'acid'=>request()->get('i'),
            'module'=>'demo',//模块标识
            'pay_method'=>'wechat',//支付方式wechat,alipay
            'body'=>'支付标题',
            'out_trade_no'=>date('YmdHis').'_'.mt_rand(1000000000, 9999999999),
            'total_fee'=>1,
            'openid'=>$userinfo['openid']
        ];
        $order=$sunpay->pay($params);

        $data=$jssdk->bridgeConfig($order['prepay_id'],false);
        //两种都可以，注意timeStamp大小写区别
        // $data=$jssdk->sdkConfig($order['prepay_id']);

        return view('jssdkpay',[
            'data'=>$data
        ]);
    } */




}