<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 15:00:20
 * @LastEditors: light
 * @LastEditTime: 2023-04-27 14:41:00
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\pay;

defined('SUN_IN') or exit('Sunphp Access Denied');

use Yansongda\Pay\Pay;
use Yansongda\Pay\Contract\HttpClientInterface;

class Unipay {

    protected $config;

    public function __construct($config=[])
    {
        $this->config=$config;
    }

    /* 手动添加更多配置 */
    public function config($params){
        // 以下参数可以手动加入
        // 'cert_path'          => 'cert.pem', // 绝对路径！
        // 'key_path'           => 'key',      // 绝对路径！
        foreach($params as $key=>$val){
            $this->config[$key]=$val;
        }
    }

    /* 支付宝H5支付 */
    public function wap($params){
        $config=$this->config;
        $app = Factory::payment($config);
        $attach=json_encode([
            'acid'=>$params['acid'],
            'module'=>$params['module'],//模块标识
            'pay_method'=>$params['pay_method']
        ]);
        $result = $app->order->unify([
            'body' => $params['body'],//支付页面显示的标题
            'out_trade_no' => $params['out_trade_no'],//时间+随机序列，不超过32位
            'total_fee' => $params['total_fee'],
            'attach'=>$attach,//自定义数据包，平台acid,module
            // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            // 'notify_url' => 'https://pay.weixin.qq.com/wxpay/pay.action', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $params['openid'],
        ]);
        return $result;
    }







}