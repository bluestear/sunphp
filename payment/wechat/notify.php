<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-28 09:52:45
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 17:06:46
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


/* 微信支付APIV3版本——回调通知 */

$app = new App();
//必须手动初始化，加载配置，才能调用log对象
$app->initialize();
// dump($app->config);

$log = $app->log;


$request = $app->request;
$notify_url=$request->domain() . $request->url();

$log->write('微信支付V3-notify通知' . $notify_url);
$log->write($request->post());

// 禁止使用$_POST
$data = $request->post();
if ($data['event_type'] != 'TRANSACTION.SUCCESS' ) {
    exit('fail');
};

//配置信息
$array_url=explode('/',$notify_url);
// 必须分开写，end的参数必须是引用
$pay_acid=end($array_url);
$wechat_config = CorePay::where('acid', $pay_acid)->find();
if (empty($wechat_config)) {
    $log->write("wechat_config_fail");
    exit('fail');
}

$config = [];
$config['wechat']['default'] = [
    // 必填-商户号，服务商模式下为服务商商户号
    // 可在 https://pay.weixin.qq.com/ 账户中心->商户信息 查看
    'mch_id' => $wechat_config['wx_mchid'],
    // 必填-商户秘钥
    // 即 API v3 密钥(32字节，形如md5值)，可在 账户中心->API安全 中设置
    'mch_secret_key' => $wechat_config['wx_apikey'],
    // 必填-商户私钥 字符串或路径
    // 即 API证书 PRIVATE KEY，可在 账户中心->API安全->申请API证书 里获得
    // 文件名形如：apiclient_key.pem
    'mch_secret_cert' => root_path().'attachment/'.$wechat_config['wx_cert'],
    // 必填-商户公钥证书路径
    // 即 API证书 CERTIFICATE，可在 账户中心->API安全->申请API证书 里获得
    // 文件名形如：apiclient_cert.pem
    'mch_public_cert_path' => root_path().'attachment/'.$wechat_config['wx_public_cert'],
    // 必填-微信回调url
    // 不能有参数，如?号，空格等，否则会无法正确回调
    // 'notify_url' => $notify_url,
    // 选填-公众号 的 app_id
    // 可在 mp.weixin.qq.com 设置与开发->基本配置->开发者ID(AppID) 查看
    'mp_app_id' => '',
    // 选填-小程序 的 app_id
    'mini_app_id' => '',
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
    // 'pay_acid'=>$params['acid'],//实际使用的配置平台id
    'sub_mch_id' => '',
    // 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数
    'wechat_public_cert_path' => [
        // '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__.'/Cert/wechatPublicKey.crt',
    ],
    // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
    // 'mode' => Pay::MODE_NORMAL,
];

Pay::config($config);
//校验参数
$result = Pay::wechat()->callback();

//获取解密数据
$items=$result->all();
$ciphertext=$items['resource']['ciphertext'];

// $log->write('解密数据');
// $log->write($ciphertext);



if(empty($ciphertext['out_trade_no']) || empty($ciphertext['amount'])){
    exit('fail');
}

//订单信息
$order = CoreOrder::where('order_id', $ciphertext['out_trade_no'])->find();
if (empty($order) || $order['type'] != 0||($order['money']*100)!=$ciphertext['amount']['total']) {
    exit('fail');
}


//通知模块支付成功
$module=CoreApp::where([
    'identity'=>$order['module'],
    'is_delete'=>0
])->find();

if(empty($module)){
    exit('fail');
}

$notify_post = [
    'from' => 'notify',
    'result' => 'success',
    'type' => 'wechat',
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
    // app路由
    $request->setPathinfo('PayResult/notify');
    $request->withPost($notify_post);
    $http = $app->http;
    $http->name($order['module']); //指定模块
    $response = $http->run($request);
}

//不能输出响应，否则后面代码无法执行
// $response->send();
// $http->end($response);

// 更新订单type为1
CoreOrder::update(['type'=>1],['id'=>$order['id']]);


return Pay::wechat()->success();
