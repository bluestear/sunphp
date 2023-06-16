<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 14:14:49
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 14:03:56
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\account;

defined('SUN_IN') or exit('Sunphp Access Denied');

use app\admin\model\CoreAccount;

class SunAccount{


    public static function create($uniacid=''){

        if(empty($uniacid)){
            $account=request()->middleware('account');
        }else{
            $account=CoreAccount::where('id',$uniacid)->where('is_delete',0)->find();
            if(empty($account)){
                echo '平台不存在';
                die();
            }
        }

        //区分账号类型
        $sun_account='';

        switch(intval($account['type'])){
            case 1:
                //'微信公众号'
                $config = [
                    'app_id' => $account['appid'],
                    'secret' => $account['secret'],
                    'token' => 'sunphp-wechat-token',
                    // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
                    'response_type' => 'array',
                    // EncodingAESKey，兼容与安全模式下请一定要填写！！！
                    'aes_key' => ''
                ];
                $sun_account=new Wxgzh($config);
            break;
            case 2:
                //'微信小程序'
                $config = [
                    'app_id' => $account['appid'],
                    'secret' => $account['secret'],
                    'token' => 'sunphp-wxxcx-token',
                    // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
                    'response_type' => 'array',
                    // 'log' => [
                    //     'level' => 'debug',
                    //     'file' => __DIR__.'/wechat.log',
                    // ],
                ];
                $sun_account=new Wxxcx($config);
            break;
            case 3:
                //抖音小程序
            break;
            default:
            break;
        }


        return $sun_account;
    }



}