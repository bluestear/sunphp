<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-05 10:32:44
 * @LastEditors: light
 * @LastEditTime: 2023-05-05 15:24:21
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\sms;

defined('SUN_IN') or exit('Sunphp Access Denied');


use app\admin\model\CoreSms;



class SunSms {


    // 发送一个短信
    public static function send($args,$from=1){

        $get=request()->get();
        if(empty($get['i'])){
            $sms=CoreSms::where('acid',0)->find();
        }else{
            $sms=CoreSms::where('acid',$get['i'])->find();
            if(empty($sms)||$sms['type']==1){
                $sms=CoreSms::where('acid',0)->find();
            }
        }

        if(empty($sms)){
            echo "短信未配置";
            return false;
        }

        if($sms['type']==1){
            echo "短信已关闭";
            return false;
        }
        /* from：1阿里云，2腾讯云 */
        switch($from){
            case 1:
                $ali=new AliSms($sms['ali_sms']);
                return $ali->send($args);
            break;
            case 2:
                $tencent=new TencentSms($sms['tencent_sms']);
                return $tencent->send($args);
            break;
            default:
            break;
        }

        return false;
    }


}


