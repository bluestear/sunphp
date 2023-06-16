<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-03 15:57:30
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 14:56:16
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\account;

defined('SUN_IN') or exit('Sunphp Access Denied');

use EasyWeChat\Factory;

class Wxxcx {
    protected $config;

    public function __construct($config=[])
    {
        $this->config=$config;
    }

    public function getApp(){
        $config=$this->config;
        $app = Factory::miniProgram($config);
        return $app;
    }

    /* 获取wxxcx对象类里面的方法 */
    public function __call($name, $arguments)
    {
        $config=$this->config;
        $app = Factory::miniProgram($config);
        return $$app->$name(...$arguments);
    }


    public function session($code){
        $config=$this->config;
        $app = Factory::miniProgram($config);
        return $app->auth->session($code);
    }

    public function decryptData($session, $iv, $encryptedData){
        $config=$this->config;
        $app = Factory::miniProgram($config);
        $decryptedData = $app->encryptor->decryptData($session, $iv, $encryptedData);
        return $decryptedData;
    }



}