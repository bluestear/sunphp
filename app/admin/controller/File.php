<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-20 09:30:50
 * @LastEditors: light
 * @LastEditTime: 2023-05-22 11:44:50
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);

namespace app\admin\controller;
use sunphp\file\SunFile;


class File extends Base{

   	//上传文件接口，返回路径地址
	public function upload()
	{
		$post=$this->request->post();
		$file=$this->request->file();

			if (isset($post['file_type'])) {
				//是否远程上传，本地是否删除
				// 不设置参数，默认远程上传，设置为2，不远程上传
				$remote_upload=empty($post['remote_upload'])?true:false;
				$local_delete=empty($post['local_delete'])?true:false;

				switch ($post['file_type']) {
					case 'img':
						if (!empty($file['file_img'])) {

							$res = SunFile::upload('file_img', "image",$remote_upload,$local_delete);

							if ($res['status']!=1) {
								return jsonResult(400, $res['message'], array());
							}
							$data = array(
								"path" => $res['path']
							);
							return jsonResult(200, "上传成功", $data);
						}
						break;
					case 'video':
						if (!empty($file['file_video'])) {
							$res = SunFile::upload('file_video', "video",$remote_upload,$local_delete);

							if ($res['status']!=1) {
								return jsonResult(400, $res['message'], array());
							}
							$data = array(
								"path" => $res['path']
							);
							return jsonResult(200, "上传成功", $data);
						}
						break;
					case 'voice':
						if (!empty($file['file_voice'])) {


							//h5上传的音频的name没有扩展名
							$ext = pathinfo($_FILES['file_voice']['name'], PATHINFO_EXTENSION);
							if (empty($ext)) {
								$_FILES['file_voice']['name'] .= ".mp3";
							}


							$res = SunFile::upload('file_voice', "voice",$remote_upload,$local_delete);
							if ($res['status']!=1) {
								return jsonResult(400, $res['message'], array());
							}
							$data = array(
								"path" => $res['path']
							);
							return jsonResult(200, "上传成功", $data);
						}
						break;
					case 'file':
						if (!empty($file['file_file'])) {

							//blob本地资源会自动更改name，必须指定
							if (!empty($post['file_name'])) {
								$_FILES['file_file']['name'] = $post['file_name'];
							}

							$res = SunFile::upload('file_file', "file",$remote_upload,$local_delete);
							if ($res['status']!=1) {
								return jsonResult(400, $res['message'], array());
							}
							$data = array(
								"path" => $res['path']
							);
							return jsonResult(200, "上传成功", $data);
						}
						break;
					default:
						break;
				}
			}
			return jsonResult(400, "参数错误", array());
	}


}