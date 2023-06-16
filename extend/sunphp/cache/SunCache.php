<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-07 11:16:34
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 15:48:16
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\cache;

defined('SUN_IN') or exit('Sunphp Access Denied');

use think\facade\Cache;

class SunCache{

    public static function prefix(){
        return 'sunphp_cache_';
    }

    public static function set($name,$value,$expire_time=0){
        $name=self::prefix().$name;
        if($expire_time>0){
            Cache::set($name,$value, $expire_time);
        }else{
            Cache::set($name,$value);
        }
    }

    public static function get($name){
        $name=self::prefix().$name;
        return Cache::get($name);
    }

    public static function delete($name){
        $name=self::prefix().$name;
        return Cache::delete($name);
    }

    public static function clean(){
        return Cache::clean();
    }



}