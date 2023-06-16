<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-29 09:19:55
 * @LastEditors: light
 * @LastEditTime: 2023-04-11 17:14:22
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\admin\validate;

use think\Validate;

class ValidateCommon extends  Validate{
    protected $rule=[
        'url'=>'url'
    ];

    protected $message=[
        'url'=>'url地址错误'
    ];

}