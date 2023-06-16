<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-23 16:09:03
 * @LastEditors: light
 * @LastEditTime: 2023-05-30 14:32:35
 * @Description: SonLight Tech版权所有
 */
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-23 16:09:03
 * @LastEditors: light
 * @LastEditTime: 2023-05-22 14:25:43
 * @Description: SonLight Tech版权所有
 */

namespace sunphp\jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;



class SunJwt{


    //签发JWT，通过用户临时身份动态生成密码
	public static function createJwt($sid,$second=36000)
	{
		$key = md5("sunphp".$sid);
        $time = time(); //当前时间
        $expire = $time + $second; //过期时间
       	$token = [
        	'iss' => 'https://bluestear.gitee.io/sunphp/', //签发者 可选
           	'aud' => 'https://bluestear.gitee.io/sunphp/', //接收该JWT的一方，可选
           	'iat' => $time, //签发时间
           	'nbf' => $time, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
           	'exp' => $expire
        ];
        return JWT::encode($token, $key,'HS256');
    }

    //验证JWT
    public static function verifyJwt($jwt,$sid)
	{
        $key = md5("sunphp".$sid);
		try {
	       		JWT::$leeway = 60;//当前时间减去60，把时间留点余地
	       		$decoded = JWT::decode($jwt, new Key($key, 'HS256')); //HS256方式，这里要和签发的时候对应

                $arr = (array)$decoded;

                $data=array(
                    "status"=>1000,
                    "data"=>$arr
                );
	    	}catch(SignatureInvalidException $e) {  //签名不正确
                $error=$e->getMessage();
                $data=array(
                    "status"=>1001,
                    "data"=>$error
                );
	    	}catch(BeforeValidException $e) {  // 签名在某个时间点之后才能用
                $error=$e->getMessage();
                $data=array(
                    "status"=>1002,
                    "data"=>$error
                );
	    	}catch(ExpiredException $e) {  // token过期
                $error=$e->getMessage();
                $data=array(
                    "status"=>1003,
                    "data"=>$error
                );
            }catch(\Exception $e) {  //其他错误
                $error=$e->getMessage();
                $data=array(
                    "status"=>1004,
                    "data"=>$error
                );
            }

        return $data;

    }


}
