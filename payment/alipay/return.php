<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-28 09:52:45
 * @LastEditors: light
 * @LastEditTime: 2023-05-26 16:35:26
 * @Description: SonLight Tech版权所有
 */

namespace think;

define('SUN_IN', true);

require __DIR__ . '/../../vendor/autoload.php';

use app\admin\model\CoreApp;
use app\admin\model\CoreOrder;
use think\App;
use app\admin\model\CorePay;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Contract\HttpClientInterface;


$app = new App();
//必须手动初始化，加载配置，才能调用log对象
$app->initialize();
// dump($app->config);

$log = $app->log;


$request = $app->request;
$log->write('支付宝return通知' . $request->domain() . $request->url());
$log->write($request->get());


$data = $_GET;
if (empty($data['out_trade_no']) || empty($data['total_amount'])) {
    exit('fail');
};
//订单信息
// 同步回调可以多次返回，不校验type
$order = CoreOrder::where('order_id', $data['out_trade_no'])->find();
if (empty($order)||$order['money']!=$data['total_amount']) {
    exit('fail');
}
//配置信息
$alipay_config = CorePay::where('acid', $order['pay_acid'])->find();
if (empty($alipay_config)) {
    exit('fail');
}

$config = [];
$config['alipay']['default'] = [
    // 必填-支付宝分配的 app_id
    'app_id' => $alipay_config['ali_appid'],
    // 必填-应用私钥 字符串或路径
    // 在 https://open.alipay.com/develop/manage 《应用详情->开发设置->接口加签方式》中设置
    'app_secret_cert' => $alipay_config['ali_appkey'],
    // 必填-应用公钥证书 路径
    // 设置应用私钥后，即可下载得到以下3个证书
    'app_public_cert_path' => root_path() .'attachment/'. $alipay_config['ali_app_cert'],
    // 必填-支付宝公钥证书 路径
    'alipay_public_cert_path' => root_path() .'attachment/'. $alipay_config['ali_public_cert'],
    // 必填-支付宝根证书 路径
    'alipay_root_cert_path' => root_path() .'attachment/'. $alipay_config['ali_root_cert'],
    'return_url' => '',
    'notify_url' => '',
    // 选填-第三方应用授权token
    'app_auth_token' => '',
    // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
    'service_provider_id' => '',
    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
    // 'mode' => Pay::MODE_NORMAL,
];

Pay::config($config);
//校验参数
$result = Pay::alipay()->callback();

//通知模块支付成功
$module=CoreApp::where([
    'identity'=>$order['module'],
    'is_delete'=>0
])->find();

if(empty($module)){
    exit('fail');
}

//通知模块支付成功
$request->setPathinfo('PayResult/return');
$notify_post = [
    'from' => 'notify',
    'result' => 'success',
    'type' => 'alipay',
    'acid' => $order['acid'],
    'module' => $order['module'],
    'tid' => $order['tid'],
    'fee' => $order['money']
];

if($module['dir']=='addons'){
    // addons路由
    // 兼容常用方法，如message(),load()等等
    require_once root_path().'extend/sunphp/addons/payresult.php';

}else{

    $request->withPost($notify_post);
    $http = $app->http;
    $http->name($order['module']); //指定模块
    $response = $http->run($request);

}

//不能输出响应，否则后面代码无法执行
$response->send();
$http->end($response);


// return Pay::alipay()->success();
