<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-15 14:14:16
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 14:55:15
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);

namespace sunphp\addons;

defined('SUN_IN') or exit('Sunphp Access Denied');
use think\facade\View;


class WeModuleBase{

    public function createMobileUrl($do,$arg=[]){
        global $_W,$_GPC;
        $module=$_W['current_module']['name'];

        if(empty($arg['a'])){
            $a='site';
        }else{
            $a=$arg['a'];
        }
        $str='index.php?i='.$_GPC['i'].'&c=entry&a='.$a.'&m='.$module.'&do='.$do;

        // 其他自定义参数
        foreach($arg as $k=>$v){
            if($k!='a'){
                $str.='&'.$k.'='.$v;
            }
        }
        return $str;
    }

    public function createWebUrl($do,$arg=[])
    {
        global $_W,$_GPC;
        $module=$_W['current_module']['name'];
        $str='index.php?i='.$_GPC['i'].'&c=site&a=entry&m='.$module.'&do='.$do;
         // 其他自定义参数
         foreach($arg as $k=>$v){
            if($k!='a'){
                $str.='&'.$k.'='.$v;
            }
        }
        return $str;
    }

    public function template($arg){
        global $_W;
        $_W['sunphp_tpl_file']=$arg;
        return root_path().'extend/sunphp/addons/template.php';
    }

    public function result($errno, $message, $data){
        $result = array(
            'errno' => $errno,//0成功，非0错误
            'message' => $message,
            'data' => $data,
        );
        header('Content-Type:application/json');
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function pay($arg){
        // php调用支付方法，跳转到支付页面
        //构造支付请求中的参数
        // $params = array(
        //     'tid' => $order['tid'],      //充值模块中的订单号，此号码用于业务模块中区分订单，交易的识别码
        //     'ordersn' => $order['tradeno'],  //收银台中显示的订单号
        //     'title' => $order['des'],          //收银台中显示的标题
        //     'fee' => $order['money'],      //收银台中显示需要支付的金额,只能大于 0
        //     'user' => '',     //付款用户, 付款的用户名(选填项)
        // );

        global $_W;
        $arg['module']=$_W['current_module']['name'];

        $tpl_file= root_path().'view/sunphp/pay/show.html';
        View::assign($arg);

        $template_view=View::fetch($tpl_file);
        echo $template_view;
        die();
    }



}