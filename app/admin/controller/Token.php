<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-24 14:45:23
 * @LastEditors: light
 * @LastEditTime: 2023-05-04 09:30:02
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);
namespace app\admin\controller;

use app\admin\model\CoreUser;
use sunphp\jwt\SunJwt;

class Token extends Base{
    public function refresh(){


        $session_id=$this->request->post('session_id','');
        $refresh_token=$this->request->post('refresh_token','');


        if(empty($session_id)||empty($refresh_token)){
			return jsonResult(400, "用户未登录", []);
        }

        try{
            $res = SunJwt::verifyJwt($refresh_token, $session_id);

			if ($res['status'] == 1000) {
                //检查用户是否存在
                $user=CoreUser::where('session_id',$session_id)->where('is_delete',0)->find();
                if(empty($user)){
			        return jsonResult(402, "用户不存在", []);
                }
				$sid = $session_id;
				$access_token = SunJwt::createJwt($sid);
				$data = array(
					'access_token' => $access_token
				);
				//refresh_token可能过期，需要自动续期
				if ($res['data']['exp'] - 86400 * 5 < time()) {
					$refresh_token = SunJwt::createJwt($sid, 3600 * 24 * 15);
					$data['refresh_token'] = $refresh_token;
				}

				//用户刷新token，续期cookie
				cookie('sunphp_user_session_id',$session_id,36000);

				return jsonResult(200, "操作成功", $data);
			} else if ($res['status'] == 1003) {
                //refresh_token过期，删除token退出
				return jsonResult(402, "登录已过期", []);
			} else {
				return jsonResult(401, "签名错误", []);
			}
        } catch (\Exception $e) {
			return jsonResult(402, "登录已失效", []);
		}


    }
}
