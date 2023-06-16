<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 15:00:20
 * @LastEditors: light
 * @LastEditTime: 2023-04-28 16:34:16
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\pay;

defined('SUN_IN') or exit('Sunphp Access Denied');

use EasyWeChat\Factory;
use Ramsey\Uuid\Uuid;
use app\admin\model\CoreOrder;

class Wxgzh {

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

    public function getApp(){
        $config=$this->config;
        $app = Factory::payment($config);
        return $app;
    }

    public function getJssdkConfig(){
        $config=$this->config;
        $app = Factory::payment($config);
        return $app->jssdk;
    }

    /* 写入系统订单 */
    public function order($params){
        $config=$this->config;
        $str=Uuid::uuid1()->getHex()->toString();
        $order_id=date('YmdHis').strtoupper(substr($str,0,16));
        if(empty($params['module'])){
            $module=app('http')->getName();
        }else{
            $module=$params['module'];
        }
        $acid=request()->get('i');

        //记录系统订单
        $order=CoreOrder::create([
            'order_id'=>$order_id,
            'tid'=>$params['tid'],
            'money'=>$params['money'],
            'title'=>$params['title'],
            'module'=>$module,
            'acid'=>$acid,
            'pay_acid'=>$config['pay_acid'],
            'pay_method'=>'wechat',
            'type'=>0
        ]);
        if(!empty($order)){
            return $order_id;
        }
        die();
    }

    public function pay($params){
        $config=$this->config;
        $app = Factory::payment($config);
        // $attach=json_encode([
        //     'acid'=>$params['acid'],
        //     'module'=>$params['module'],//模块标识
        //     'pay_method'=>$params['pay_method']
        // ]);
        $order_id=$this->order($params);

        $result = $app->order->unify([
            'body' => $params['title'],//支付页面显示的标题
            'out_trade_no' => $order_id,//时间+随机序列，不超过32位
            'total_fee' => intval($params['money']*100),
            // 'attach'=>$attach,//自定义数据包，平台acid,module
            // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
            // 'notify_url' => 'https://pay.weixin.qq.com/wxpay/pay.action', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'trade_type' => 'JSAPI', // 请对应换成你的支付方式对应的值类型
            'openid' => $params['openid'],
        ]);
        return $result;
    }







}