<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-13 15:07:38
 * @LastEditors: light
 * @LastEditTime: 2023-05-30 17:38:39
 * @Description: SonLight Tech版权所有
 */
declare (strict_types = 1);

namespace app\admin\controller;


class Index extends Base
{
    public function index()
    {

        $sub_directory='';
        if(!empty(env('APP.SUB_DIRECTORY'))){
            $sub_directory='/'.env('APP.SUB_DIRECTORY');
        }
        $data=[
            'domain'=>$this->request->domain(),
            'sub_directory'=>$sub_directory
        ];
        return view('index',$data);
    }


}
