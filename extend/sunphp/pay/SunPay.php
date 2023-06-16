<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 14:14:49
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 16:43:00
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\pay;

defined('SUN_IN') or exit('Sunphp Access Denied');

use app\admin\model\CoreAccount;
use app\admin\model\CorePay;


class SunPay{

    /* 微信V2支付 (不推荐)*/
    public static function wechatV2($uniacid=''){

        if(empty($uniacid)){
            $account=request()->middleware('account');
        }else{
            $account=CoreAccount::where('id',$uniacid)->where('is_delete',0)->find();
            if(empty($account)){
                echo '平台不存在';
                die();
            }
        }

        //获取支付参数
        $params=CorePay::where('acid',$account['id'])->find();
        if(empty($params)||empty($params['wx_mchid'])||empty($params['wx_switch'])){
            $params=CorePay::where('acid',0)->find();
        }

        if(empty($params)||empty($params['wx_mchid'])||empty($params['wx_switch'])){
            echo '支付参数未配置';
            die();
        }

        // $module=app('http')->getName();
        $domain=request()->domain();
        $notify_url=$domain.'/payment/wechat/notifyV2.php';

        //公众号参数
        $config = [
            'app_id' => $account['appid'],
            'mch_id' => $params['wx_mchid'],
            'key' => $params['wx_apikey'],//V2秘钥
            'pay_acid'=>$params['acid'],//实际使用的配置平台id
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径
            // 'cert_path'          => 'cert.pem', // 绝对路径！
            // 'key_path'           => 'key',      // 绝对路径！

            'notify_url'         => $notify_url,     // 你也可以在下单时单独设置来想覆盖它
        ];

        $pay=new Wxgzh($config);
        return $pay;
    }

    protected static function getConfig(){
        return [
            'alipay' => [
                'default' => []
            ],
            'wechat' => [
                'default' => []
            ],
            'unipay' => [
                'default' => []
            ],
            'logger' => [
                'enable' => false,
                'file' => './logs/pay.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'timeout' => 30,
                'connect_timeout' => 30,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
        ];
    }

    /* 支付宝支付 */
    public static function alipay($uniacid=''){

        if(empty($uniacid)){
            $account=request()->middleware('account');
        }else{
            $account=CoreAccount::where('id',$uniacid)->where('is_delete',0)->find();
            if(empty($account)){
                echo '平台不存在';
                die();
            }
        }

        //获取支付参数
        $params=CorePay::where('acid',$account['id'])->find();
        if(empty($params)||empty($params['ali_appid'])||empty($params['ali_switch'])){
            $params=CorePay::where('acid',0)->find();
        }

        if(empty($params)||empty($params['ali_appid'])||empty($params['ali_switch'])){
            echo '支付参数未配置';
            die();
        }

        $domain=request()->domain();
        $return_url=$domain.'/payment/alipay/return.php';
        $notify_url=$domain.'/payment/alipay/notify.php';

        //异步通知返回，同步通知不返回
        //必须使用系统订单表格
        // $passback_params=urlencode('acid=1');

        $config=self::getConfig();
        $config['alipay']['default']=[
            // 必填-支付宝分配的 app_id
            'app_id' => $params['ali_appid'],
            // 必填-应用私钥 字符串或路径
            // 在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
            'app_secret_cert' => $params['ali_appkey'],
            // 必填-应用公钥证书 路径
            // 设置应用私钥后，即可下载得到以下3个证书
            'app_public_cert_path' => root_path().'attachment/'.$params['ali_app_cert'],
            // 必填-支付宝公钥证书 路径
            'alipay_public_cert_path' => root_path().'attachment/'.$params['ali_public_cert'],
            // 必填-支付宝根证书 路径
            'alipay_root_cert_path' => root_path().'attachment/'.$params['ali_root_cert'],
            'return_url' => $return_url,
            'notify_url' => $notify_url,
            // 选填-第三方应用授权token
            'app_auth_token' => '',
            'pay_acid'=>$params['acid'],//实际使用的配置平台id
            // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
            'service_provider_id' => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
            // 'mode' => Pay::MODE_NORMAL,
        ];


        $pay=new Alipay($config);
        return $pay;
    }

    /* 微信支付API-V3 （推荐）*/
    public static function wechat($uniacid=''){

        if(empty($uniacid)){
            $account=request()->middleware('account');
        }else{
            $account=CoreAccount::where('id',$uniacid)->where('is_delete',0)->find();
            if(empty($account)){
                echo '平台不存在';
                die();
            }
        }

        //获取支付参数
        $params=CorePay::where('acid',$account['id'])->find();
        if(empty($params)||empty($params['wx_mchid'])||empty($params['wx_switch'])){
            $params=CorePay::where('acid',0)->find();
        }

        if(empty($params)||empty($params['wx_mchid'])||empty($params['wx_switch'])){
            echo '支付参数未配置';
            die();
        }

        $module=app('http')->getName();
        $domain=request()->domain();
        //记录使用的是哪个支付参数，V3必须先解密
        $notify_url=$domain.'/payment/wechat/notify.php/'.$params['acid'];

        $config=self::getConfig();
        $config['wechat']['default']=[
            // 必填-商户号，服务商模式下为服务商商户号
            // 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
            'mch_id' => $params['wx_mchid'],
            // 必填-商户秘钥
            // 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
            'mch_secret_key' => $params['wx_apikey'],
            // 必填-商户私钥 字符串或路径
            // 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_key.pem
            'mch_secret_cert' => root_path().'attachment/'.$params['wx_cert'],
            // 必填-商户公钥证书路径
            // 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
            // 文件名形如：apiclient_cert.pem
            'mch_public_cert_path' => root_path().'attachment/'.$params['wx_public_cert'],
            // 必填-微信回调url
            // 不能有参数，如?号，空格等，否则会无法正确回调
            'notify_url' => $notify_url,
            // 选填-公众号 的 app_id
            // 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
            'mp_app_id' => $account['appid'],
            // 选填-小程序 的 app_id
            'mini_app_id' => $account['appid'],
            // 选填-app 的 app_id
            'app_id' => '',
            // 选填-合单 app_id
            'combine_app_id' => '',
            // 选填-合单商户号
            'combine_mch_id' => '',
            // 选填-服务商模式下，子公众号 的 app_id
            'sub_mp_app_id' => '',
            // 选填-服务商模式下，子 app 的 app_id
            'sub_app_id' => '',
            // 选填-服务商模式下，子小程序 的 app_id
            'sub_mini_app_id' => '',
            // 选填-服务商模式下，子商户id
            'pay_acid'=>$params['acid'],//实际使用的配置平台id
            'sub_mch_id' => '',
            // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
            'wechat_public_cert_path' => [
                // '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
            ],
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
            // 'mode' => Pay::MODE_NORMAL,
        ];

        $pay=new Wechat($config);
        return $pay;
    }


    /* 银联支付 */
    public static function unipay($uniacid=''){

        if(empty($uniacid)){
            $account=request()->middleware('account');
        }else{
            $account=CoreAccount::where('id',$uniacid)->where('is_delete',0)->find();
            if(empty($account)){
                echo '平台不存在';
                die();
            }
        }

        //获取支付参数
        $params=CorePay::where('acid',$account['id'])->find();
        if(empty($params)||empty($params['uni_mchid'])||empty($params['uni_switch'])){
            $params=CorePay::where('acid',0)->find();
        }

        if(empty($params)||empty($params['uni_mchid'])||empty($params['uni_switch'])){
            echo '支付参数未配置';
            die();
        }

        $module=app('http')->getName();
        $domain=request()->domain();
        $return_url=$domain.'/payment/unipay/return.php';
        $notify_url=$domain.'/payment/unipay/notify.php';

        $config=self::getConfig();
        $config['unipay']['default']=[
            // 必填-商户号
            'mch_id' => '777290058167151',
            // 必填-商户公私钥
            'mch_cert_path' => __DIR__.'/Cert/unipayAppCert.pfx',
            // 必填-商户公私钥密码
            'mch_cert_password' => '000000',
            // 必填-银联公钥证书路径
            'unipay_public_cert_path' => __DIR__.'/Cert/unipayCertPublicKey.cer',
            'pay_acid'=>$params['acid'],//实际使用的配置平台id
            // 必填
            'return_url' => $return_url,
            // 必填
            'notify_url' => $notify_url,
            ];

        $pay=new Unipay($config);
        return $pay;
    }



}