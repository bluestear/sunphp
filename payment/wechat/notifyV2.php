<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-28 09:52:45
 * @LastEditors: light
 * @LastEditTime: 2023-05-26 16:33:24
 * @Description: SonLight Tech版权所有
 */

namespace think;

define('SUN_IN', true);

require __DIR__ . '/../../vendor/autoload.php';

use app\admin\model\CoreApp;
use app\admin\model\CoreOrder;
use think\App;
use app\admin\model\CorePay;

/* 微信支付notify通知，不推荐使用 */

$app = new App();
//必须手动初始化，加载配置，才能调用log对象
$app->initialize();
// dump($app->config);

$log = $app->log;

$xml = file_get_contents('php://input');
$simplexml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
$data = json_decode(json_encode($simplexml), true);
if(empty($data)){
    echo '参数错误';
    die();
}

$request=$app->request;
$log->write('微信支付V2通知' . $request->domain() . $request->url());
// $log->write($request->post());
$log->write($data);


$notify = false;





//支付成功，校验签名
// $attach = json_decode($data['attach'], true);
if ($data['result_code']=='SUCCESS'&&$data['return_code']=='SUCCESS') {

    //订单信息
    $order = CoreOrder::where('order_id', $data['out_trade_no'])->find();
    if (empty($order) || $order['type'] != 0||$order['money']!=floatval($data['total_fee']/100)) {
        exit('fail');
    }

    $wx_apikey=CorePay::where('acid',$order['acid'])->value('wx_apikey');
    if(!empty($wx_apikey)){
        ksort($data);
        $stringA = '';
        foreach ($data as $key => $val) {
            if ($val != '' && $key != 'sign') {
                $stringA .= "{$key}={$val}&";
            }
        }
        $sign = strtoupper(md5($stringA . "key={$wx_apikey}"));
        if($data['sign']==$sign){
            //通知模块支付成功
            $request->setPathinfo('PayResult/notify');
            $notify_post=[
                'from'=>'notify',
                'result'=>'success',
                'type'=>'wechat',
                'acid'=>$order['acid'],
                'module'=>$order['module'],
                'tid'=>$order['tid'],
                'fee'=>$order['money'],
                'openid'=>$data['openid']
            ];


            //通知模块支付成功
            $module=CoreApp::where([
                'identity'=>$order['module'],
                'is_delete'=>0
            ])->find();

            if(empty($module)){
                exit('fail');
            }

            if($module['dir']=='addons'){
                // addons路由
                // 兼容常用方法，如message(),load()等等
                require_once root_path().'extend/sunphp/addons/payresult.php';

            }else{
                $request->withPost($notify_post);
                $http=$app->http;
                $http->name($order['module']);//指定模块
                $response=$http->run($request);
            }

            // $log->write($notify_post);
            $notify=true;

            // 更新订单type为1
            CoreOrder::update(['type'=>1],['id'=>$order['id']]);

            //不能输出响应，否则后面代码无法执行
            // $response->send();
            // $http->end($response);

        }else{
            $log->write('微信支付签名校验失败');
        }
    }
}


if ($notify) {
    $result = [
        'return_code' => 'SUCCESS',
        'return_msg' => 'OK'
    ];

} else {
    // echo 'fail';
    $result = [
        'return_code' => 'FAIL',
        'return_msg' => '微信支付失败'
    ];
}


$xml = "<xml>";
foreach ($result as $key => $val) {
    if (is_numeric($val)) {
        $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
    } else {
        $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
    }
}
$xml .= "</xml>";

// 必须输出xml字符串
echo $xml;