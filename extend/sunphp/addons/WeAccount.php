<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-15 14:14:16
 * @LastEditors: light
 * @LastEditTime: 2023-05-26 16:15:48
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

defined('SUN_IN') or exit('Sunphp Access Denied');

use sunphp\account\SunAccount;

class WeAccount{
    public static function create($uniacid=''){
        return SunAccount::create($uniacid);
    }
}