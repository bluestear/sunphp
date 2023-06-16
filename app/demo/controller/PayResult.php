<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:46:26
 * @LastEditors: light
 * @LastEditTime: 2023-04-28 16:46:22
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\demo\controller;

use think\facade\Log;

class PayResult{

    /* 支付结果异步通知 */
    public function notify(){
        // 模块内支付回调，post参数如下
        $post=request()->post();

        Log::write('模块内支付回调notify通知');
        Log::write($post);

        /* 处理好业务逻辑，并且标记是否已经处理过 */

        // $post=[
        //     'from'=>'notify',
        //     'result'=>'success',
        //     'type'=>'wechat',
        //     'acid'=>'平台的id',
        //     'module'=>'支付的模块标识',
        //     'tid'=>'统一支付订单号out_trade_no',
        //     'fee'=>'订单总金额，单位元'
        // ];
        //无需返回值，处理业务逻辑既可！
    }


    /* 支付结果同步通知 */
    public function return(){
        // 模块内支付回调，get参数如下
        $get=request()->get();

        Log::write('模块内支付回调return通知');
        Log::write($get);

        // 请勿处理业务逻辑，仅做跳转既可
        return redirect(request()->domain());
    }



}