<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 11:46:26
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 14:26:27
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\demo\controller;
use sunphp\file\SunFile;

/* 前端默认入口示例 */
class File {


    public function upload(){
        /*
        参数1：$_FILES里面的file_name值
        参数2：文件的类型，支持'image', 'audio','voice', 'video','file'
        参数3：（可选）是否远程上传，默认ture，检查云存储并远程上传
        参数4：（可选）是否远程上传后，删除本地文件，默认true，删除本地文件。
        */

        // 图片上传
		$res = SunFile::upload('file_img', "image");
        // 音频上传
		$res = SunFile::upload('file_audio', "audio");
        // 视频上传
		$res = SunFile::upload('file_video', "video");
        //文件上传
		$res = SunFile::upload('file_file', "file");

        // 上传后的文件地址
        echo $res['path'];


        //获取附件地址（本地地址/云存储地址）
        $attachurl=SunFile::attachurl();
        // 获取完整的文件地址
        $file_url=$attachurl.$res['path'];

        echo $file_url;

    }


}