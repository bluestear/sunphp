<?php
declare(strict_types=1);
namespace app\admin\model;

use think\Model;

class CoreSms extends Model{
    protected $type=[
        'type'=>'integer',
        'ali_sms'=>'serialize',
        'tencent_sms'=>'serialize'
    ];
}