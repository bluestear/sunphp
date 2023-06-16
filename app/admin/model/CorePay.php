<?php

declare(strict_types=1);
namespace app\admin\model;

use think\Model;
class CorePay extends Model{
    protected $type=[
        'wx_switch'=>'boolean',
        'ali_switch'=>'boolean'
    ];
}