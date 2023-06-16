<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-31 17:20:14
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 16:35:52
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

defined('SUN_IN') or exit('Sunphp Access Denied');

use sunphp\account\SunAccount;
use sunphp\cache\SunCache;

/* 注意这里的自定义类名 */
class WeFrameSession{

    public function result($errno, $message, $data){
        $result = array(
            'errno' => $errno,//0成功，非0错误
            'message' => $message,
            'data' => $data,
        );
        header('Content-Type:application/json');
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }


    // 通过code获取微信openid
    public function openid(){
        global $_GPC;
        $account=SunAccount::create();
        $session=$account->session($_GPC['code']);
        $sessionid=md5($_GPC['i'].'_'.$session['session_key']);
        $res=[
            'openid'=>$session['openid'],
            'sessionid'=>$sessionid,
        ];
        //缓存用户的信息
        SunCache::set($sessionid,$session,36000);

        return $this->result(0,'操作成功',$res);
    }


    // 微信用户信息
    public function userinfo(){
        // 获取sessionid
        global $_GPC;
        if(!empty($_GPC['state'])&&(strpos($_GPC['state'],'we7sid-')!==false)){
            $session=str_replace('we7sid-','',$_GPC['state']);
            $session_cache=SunCache::get($session);
            $account=SunAccount::create();
            $data=$account->decryptData($session_cache['session_key'], $_GPC['iv'], $_GPC['encryptedData']);
            return $this->result(0,'操作成功',$data);
        }
    }

    // util.checkSession
    public function check(){
        return $this->result(0,'操作成功',[]);
    }


}