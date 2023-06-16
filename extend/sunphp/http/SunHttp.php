<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-04-11 13:42:33
 * @LastEditors: light
 * @LastEditTime: 2023-04-11 13:54:37
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\http;

defined('SUN_IN') or exit('Sunphp Access Denied');


class SunHttp
{
    public static function post($url, $post_data)
    {

        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);

        $data = curl_exec($ch); //运行curl
        curl_close($ch);

        return $data;
    }

    public static function post2($url, $post_data)
    {
        //必须要设置ssl不验证，否则容易出问题
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata
            ),
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    public static function get($url){

        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 1); //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, 35);

        $data = curl_exec($ch); //运行curl
        curl_close($ch);

        return $data;
    }

    public static function get2($url) {
		$options = array(
			  'http' => array(
				  'method' => 'GET',
				  'header' => 'Content-type:application/x-www-form-urlencoded',
              ),
              "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false
            )
		  );
		  $context = stream_context_create($options);
		  $result = file_get_contents($url, false, $context);
		  return $result;
	}
}
