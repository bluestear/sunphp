<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 15:00:20
 * @LastEditors: light
 * @LastEditTime: 2023-04-28 09:30:37
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\pay;

defined('SUN_IN') or exit('Sunphp Access Denied');

use app\admin\model\CoreOrder;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Contract\HttpClientInterface;
use Ramsey\Uuid\Uuid;

class Alipay {

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

    /* 获取alipay对象类里面的方法 */
    public function __call($name, $arguments)
    {
        $config=$this->config;
        Pay::config($config);
        return Pay::alipay()->$name(...$arguments);
    }

    /* 写入系统订单 */
    public function order($params){
        $config=$this->config;
        Pay::config($config);

        $str=Uuid::uuid1()->getHex()->toString();
        $order_id=date('YmdHis').strtoupper(substr($str,0,16));
        $module=app('http')->getName();
        $acid=request()->get('i');

        //记录系统订单
        $order=CoreOrder::create([
            'order_id'=>$order_id,
            'tid'=>$params['tid'],
            'money'=>$params['money'],
            'title'=>$params['title'],
            'module'=>$module,
            'acid'=>$acid,
            'pay_acid'=>$config['alipay']['default']['pay_acid'],
            'pay_method'=>'alipay',
            'type'=>0
        ]);
        if(!empty($order)){
            return $order_id;
        }
        die();
    }

    /* 支付宝H5支付 */
    public function wap($params){
        $order_id=$this->order($params);
        $data=[
            'out_trade_no' =>$order_id,
            'total_amount' => $params['money'],
            'subject' => $params['title']
        ];
        if(!empty($params['quit_url'])){
            $data['quit_url']=$params['quit_url'];
        }
        return Pay::alipay()->wap($data);

    }

    /* 电脑网页支付 */
    public function web($params){
        $order_id=$this->order($params);
        $data=[
            'out_trade_no' =>$order_id,
            'total_amount' => $params['money'],
            'subject' => $params['title']
        ];
        return Pay::alipay()->web($data);
    }

    /* APP支付 */
    public function app($params){
        $order_id=$this->order($params);
        $data=[
            'out_trade_no' =>$order_id,
            'total_amount' => $params['money'],
            'subject' => $params['title']
        ];
        return Pay::alipay()->app($data);
    }

    /* 支付宝小程序支付 */
    public function mini($params){
        $order_id=$this->order($params);
        $data=[
            'out_trade_no' =>$order_id,
            'total_amount' => $params['money'],
            'subject' => $params['title'],
            'buyer_id' => $params['buyer_id']
        ];
        return Pay::alipay()->mini($data);
    }

     /* 支付宝刷卡支付 */
     public function pos($params){
        $order_id=$this->order($params);
        $data=[
            'out_trade_no' =>$order_id,
            'total_amount' => $params['money'],
            'subject' => $params['title'],
            'auth_code' => $params['auth_code']
        ];
        return Pay::alipay()->pos($data);
    }

     /* 支付宝扫码支付 */
     public function scan($params){
        $order_id=$this->order($params);
        $data=[
            'out_trade_no' =>$order_id,
            'total_amount' => $params['money'],
            'subject' => $params['title']
        ];
        return Pay::alipay()->scan($data);
    }








}