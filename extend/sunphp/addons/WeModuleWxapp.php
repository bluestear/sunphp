<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-15 14:14:16
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 17:21:40
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);

defined('SUN_IN') or exit('Sunphp Access Denied');
use sunphp\addons\WeModuleBase;
use sunphp\pay\SunPay;

class WeModuleWxapp extends WeModuleBase{


    public function pay($arg){
		global $_W;
        // 小程序支付，返回参数
		$wechat=SunPay::wechat();
        $order = [
            'tid'=>$arg['tid'],
            'money'=>$arg['fee'],
            'title'=>$arg['title'],
            'openid'=>$arg['user'],
			'module'=>$_W['current_module']['name']//addons模块必须携带module参数
        ];
        $res=$wechat->mini($order);
		//返回数组
		return $res;
    }

}