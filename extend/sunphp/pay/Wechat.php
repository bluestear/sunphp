<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 15:00:20
 * @LastEditors: light
 * @LastEditTime: 2023-04-28 17:39:17
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\pay;

defined('SUN_IN') or exit('Sunphp Access Denied');

use app\admin\model\CoreOrder;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Contract\HttpClientInterface;
use Ramsey\Uuid\Uuid;

/* 微信支付APIV3（推荐） */
class Wechat {

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

    /* 获取wechat对象类里面的方法 */
    public function __call($name, $arguments)
    {
        $config=$this->config;
        Pay::config($config);
        return Pay::wechat()->$name(...$arguments);
    }

    /* 写入系统订单 */
    public function order($params){
        $config=$this->config;
        Pay::config($config);

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
            'pay_acid'=>$config['wechat']['default']['pay_acid'],//V3参数和V2不一样
            'pay_method'=>'wechat',
            'type'=>0
        ]);
        if(!empty($order)){
            return $order_id;
        }
        die();
    }

    /* 微信公众号支付V3 */
    public function mp($params){
        $order_id=$this->order($params);
        $order = [
            'out_trade_no' => $order_id,
            'description' => $params['title'],
            'amount' => [
                'total' =>intval($params['money']*100),//单位元，转换为分
            ],
            'payer' => [
                'openid' => $params['openid'],
            ]
        ];
        $result = Pay::wechat()->mp($order);
        return $result;
    }

    /* 微信手机H5网站支付V3 */
    public function wap($params){
        $order_id=$this->order($params);
        $order = [
            // '_type' => 'mini', // 如果是关联小程序H5，需要配置，默认是公众号
            'out_trade_no' => $order_id,
            'description' => $params['title'],
            'amount' => [
                'total' =>intval($params['money']*100),//单位元，转换为分
            ],
            'scene_info' => [
                'payer_client_ip' => request()->host(),
                'h5_info' => [
                    'type' => 'Wap',
                ]
            ]
        ];
        $result = Pay::wechat()->wap($order);
        return $result;
    }


     /* 微信APP支付V3 */
     public function app($params){
        $order_id=$this->order($params);
        $order = [
            'out_trade_no' => $order_id,
            'description' => $params['title'],
            'amount' => [
                'total' =>intval($params['money']*100),//单位元，转换为分
            ]
        ];
        $result = Pay::wechat()->app($order);
        return $result;
    }

     /* 微信扫码支付V3 */
     public function scan($params){
        $order_id=$this->order($params);
        $order = [
            'out_trade_no' => $order_id,
            'description' => $params['title'],
            'amount' => [
                'total' =>intval($params['money']*100),//单位元，转换为分
            ]
        ];
        $result = Pay::wechat()->scan($order);
        return $result;
    }

     /* 微信扫码支付V3 */
     public function mini($params){
        $order_id=$this->order($params);
        $order = [
            'out_trade_no' => $order_id,
            'description' => $params['title'],
            'amount' => [
                'total' =>intval($params['money']*100),//单位元，转换为分
                'currency' => 'CNY'
            ],
            'payer' => [
                'openid' => $params['openid'],
            ]
        ];
        $result = Pay::wechat()->mini($order);
        return $result;
    }









}