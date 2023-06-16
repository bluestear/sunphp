<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-15 14:14:16
 * @LastEditors: light
 * @LastEditTime: 2023-05-25 11:31:01
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

defined('SUN_IN') or exit('Sunphp Access Denied');

class SunLoader{
    public function func($arg=''){
        switch($arg){
            case 'logging':
            break;
            case 'communication':
            break;
            case 'file':
            break;
            default:
            break;
        }
    }

    public function library($arg){
        $file=$arg;
        if(strpos($arg,'.php')==false){
            $file.='.php';
        }
        include_once root_path().'extend/sunphp/library/'.$file;
    }

    public function model($arg=''){
        switch($arg){
            case 'mc':
            break;
            case 'communication':
            break;
            default:
            break;
        }
    }

    public function app($arg=''){}
    public function web($arg=''){}



}