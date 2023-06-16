<?php

declare(strict_types=1);

namespace sunphp\file;

defined('SUN_IN') or exit('Sunphp Access Denied');


class SunEnv{
    public static function set($key, $val) {
        //获取数据
        $env_path=root_path().'.env';
        $env_content = file($env_path);
        $env_data = preg_grep('/^#' . $key . '\s*=|^' . $key . '\s*=/', $env_content);
        $old_value = $env_data ? preg_replace('/\r|\n/', '', array_shift($env_data)) : '';

        //写入数据
        $new_data = $key . ' = ' . $val;
        if($old_value) {
            $regex = '/^' . preg_quote($old_value, '/') . '/m';
            $env_new=preg_replace($regex, $new_data, implode( '',$env_content));
            $res=file_put_contents($env_path, $env_new);
            return (bool) $res;
        }

        return (bool) file_put_contents($env_path, PHP_EOL . $new_data, FILE_APPEND);
    }
}