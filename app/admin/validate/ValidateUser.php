<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-29 09:19:55
 * @LastEditors: light
 * @LastEditTime: 2023-03-29 10:01:51
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\admin\validate;

use think\Validate;

class ValidateUser extends  Validate{
    protected $rule=[
        'phone'=>'mobile'
    ];

    protected $message=[
        'phone'=>'手机号码格式错误'
    ];

}