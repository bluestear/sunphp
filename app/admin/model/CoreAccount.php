<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-17 10:48:07
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 10:01:20
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\model;

use think\Model;
class CoreAccount extends Model{
    protected $type=[
        'level'=>'integer'
    ];
}