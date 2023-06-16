<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 14:44:12
 * @LastEditors: light
 * @LastEditTime: 2023-03-31 16:34:48
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\model;

use think\Model;

class CoreApp extends Model{
    protected $type=[
        'cover'=>'serialize'
    ];
}