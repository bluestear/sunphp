<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-14 14:43:12
 * @LastEditors: light
 * @LastEditTime: 2023-03-15 15:11:13
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\model;
use think\Model;

class CoreStorage extends Model{
    protected $type=[
        'type'=>'integer',
        'ali_oss'=>'serialize',
        'tencent_cos'=>'serialize',
        'qiniu'=>'serialize'
    ];
}