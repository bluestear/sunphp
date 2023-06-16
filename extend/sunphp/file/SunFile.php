<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-07 11:16:34
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 14:19:52
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\file;

defined('SUN_IN') or exit('Sunphp Access Denied');

use app\admin\model\CoreStorage;
use  think\facade\Filesystem;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use OSS\OssClient;
use OSS\Core\OssException;



class SunFile
{

    public static function attachurl(){

        $get=request()->get();

        // 开启远程就是远程附件地址
        if(empty($get['i'])){
            $storage=CoreStorage::where('acid',0)->find();
        }else{
            $storage=CoreStorage::where('acid',$get['i'])->find();
            if(empty($storage)||$storage['type']==1){
                $storage=CoreStorage::where('acid',0)->find();
            }
        }

        // 本地附件url
        $attachurl_local=request()->domain()."/"."attachment/";

        if(empty($storage)){
            $type=1;
        }else{
            $type=$storage->type;
        }

        $attachurl='';

        switch($type){
            case 1:
                $attachurl=$attachurl_local;
                break;
            case 2:
                $oss=$storage->ali_oss;
                $attachurl=$oss['url'].'/';
                break;
            case 3:
                $oss=$storage->tencent_cos;
                $attachurl=$oss['url'].'/';
                break;
            case 4:
                $oss=$storage->qiniu;
                $attachurl=$oss['url'].'/';
                break;
        }

        return $attachurl;

    }

    public static function upload($file_name, $type,$remote_upload=true,$local_delete=true)
    {

        $get=request()->get();

        $upfile = $_FILES[$file_name];
        if (strstr($upfile['name'], ".") === false) {
            $temp = explode("/", $upfile['type']);
            $ext = end($temp);
        } else {
            $ext = pathinfo($upfile['name'], PATHINFO_EXTENSION);
            $ext = strtolower($ext);
        }

        //检查type类型
        $allow_type=['image', 'audio','voice', 'video','file'];
        if(!in_array($type,$allow_type)){
            $result = [
                "status" => 0,
                'message'=>'上传失败！参数type错误',
                "path" => ''
            ];
            return $result;
        }


        //检查系统设置的上传后缀限制
        $s=CoreStorage::where('acid',0)->field(['suffix','img_size','video_size','file_size'])->find();
        if(empty($s)||empty($s->suffix)){
            $result = [
                "status" => 0,
                'message'=>'上传失败！系统附件未设置',
                "path" => ''
            ];
            return $result;
        }

        $suffix=preg_split("/[\s\r\n]+/",$s->suffix);

        if(!in_array($ext,$suffix)){
            $result = [
                "status" => 0,
                'message'=>'上传失败！附件后缀不支持',
                "path" => ''
            ];
            return $result;
        }

        //检查大小限制
        switch($type){
            case 'image':
                if($upfile['size']>(1024*$s->img_size)){
                    $result = [
                        "status" => 0,
                        'message'=>'上传失败！图片大小超过'.$s->img_size.'KB',
                        "path" => ''
                    ];
                    return $result;
                }
            break;
            case 'audio':
            case 'voice':
            case 'video':
                if($upfile['size']>(1024*$s->video_size)){
                    $result = [
                        "status" => 0,
                        'message'=>'上传失败！音视频大小超过'.$s->video_size.'KB',
                        "path" => ''
                    ];
                    return $result;
                }
            break;
            case 'file':
                if($upfile['size']>(1024*$s->file_size)){
                    $result = [
                        "status" => 0,
                        'message'=>'上传失败！文件大小超过'.$s->file_size.'KB',
                        "path" => ''
                    ];
                    return $result;
                }
            break;
        }

        //手动增加上传文件的类型
		require(root_path() . 'extend/sunphp/config/filetype.php');

        //验证文件后缀和MIME
        $check_mime=true;
		if (array_key_exists($ext, $sunphp_file_type)) {
			//检查mime类型
			if (is_array($sunphp_file_type[$ext])) {
				if (!in_array($upfile['type'], $sunphp_file_type[$ext])) {
                    $check_mime=false;
				}
			} else {
				if ($upfile['type'] != $sunphp_file_type[$ext]) {
                    $check_mime=false;
				}
			}
        }else{
            $check_mime=false;
        }

        if(!$check_mime){
            $result = [
                "status" => 0,
                'message'=>'上传失败！MIME类型错误',
                "path" => ''
            ];
            return $result;
        }


        //生成文件路径
        if (!empty($get['i'])&&intval($get['i'])>0) {
            $uniacid = intval($get['i']);
            $path = "{$type}s/{$uniacid}/" . date('Y/m');
        } else {
            $path = "{$type}s/system";
        }

        //指定文件名称
        do {
            $data = uniqid("", true);
            $data .= microtime();
            $data .= $_SERVER['HTTP_USER_AGENT'];
            $data .= $_SERVER['REMOTE_PORT'];
            $data .= $_SERVER['REMOTE_ADDR'];
            $hash = strtolower(hash('ripemd128', "sunphp" . md5($data)));
            $filename = md5($hash) . '.' . $ext;
        } while (file_exists(root_path() . "attachment/" . $path . "/" . $filename));

        $file = request()->file($file_name);
        $res = Filesystem::putFileAs($path, $file, $filename);


        // window下res是反斜杠
        // $res = Filesystem::putFile( $path, $file,'md5');
        // str_replace('\\', '/', $res)

        //判断是否上传到云存储，是否上传到云存储后删除本地文件
        if($remote_upload){
            self::remoteUpload($path . "/" . $filename,$local_delete);
        }

        $result = [
            "status" => 1,
            "message"=>"上传成功",
            "path" => $res
        ];
        return $result;
    }

    public static function remoteUpload($file,$local_delete=true)
    {
        //远程上传，并尝试删除本地的文件。
        $get=request()->get();
        if(empty($get['i'])){
            $storage=CoreStorage::where('acid',0)->find();
        }else{
            $storage=CoreStorage::where('acid',$get['i'])->find();
            if(empty($storage)||$storage['type']==1){
                $storage=CoreStorage::where('acid',0)->find();
            }
        }


        switch($storage['type']){
            case 1:
                return "云存储已关闭";
            break;
            case 2:
                $ali=$storage['ali_oss'];
                // 阿里云账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM用户进行API访问或日常运维，请登录RAM控制台创建RAM用户。
                $accessKeyId = $ali['accesskey'];
                $accessKeySecret =$ali['secretkey'];
                // Endpoint以杭州为例，其它Region请按实际情况填写。
                $endpoint = $ali['endpoint']?$ali['endpoint']:'https://oss-cn-beijing.aliyuncs.com';
                // 填写Bucket名称，例如examplebucket。
                $bucket = $ali['bucket'];

                // 要上传文件的本地路径
                $filePath = root_path() . "attachment/" .$file;
                // 上传到存储后保存的文件名
                $object = $file;

                $upload_res=true;
                try {
                    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    $ossClient->uploadFile($bucket, $object, $filePath);
                } catch (OssException $e) {
                    print_r($e->getMessage());
                    $upload_res=false;
                }
                //删除本地文件
                if($local_delete){
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                return $upload_res;
            break;
            case 3:
                $tencent=$storage['tencent_cos'];

                $secretId = $tencent['accesskey']; //替换为用户的 secretId，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
                $secretKey = $tencent['secretkey']; //替换为用户的 secretKey，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi

                $region = $tencent['region']?$tencent['region']:"ap-nanjing"; //替换为用户的 region，已创建桶归属的region可以在控制台查看，https://console.cloud.tencent.com/cos5/bucket

                // 填写Bucket名称，例如examplebucket。
                $bucket = $tencent['bucket'];

                // 要上传文件的本地路径
                $filePath = root_path() . "attachment/" .$file;
                // 上传到存储后保存的文件名
                $object = $file;

                //协议头部，默认为http
                $schema=request()->scheme();

                $cosClient = new \Qcloud\Cos\Client(
                    array(
                        'region' => $region,
                        'schema' => $schema,
                        'credentials'=> array(
                            'secretId'  => $secretId ,
                            'secretKey' => $secretKey)
                        ));

                $upload_res=true;

                try {
                    $result = $cosClient->upload(
                        $bucket = $bucket, //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
                        $key = $object, //此处的 key 为对象键
                        $body = fopen($filePath, 'rb')
                    );
                    // 请求成功
                    // print_r($result);
                } catch (\Exception $e) {
                    // 请求失败
                    print_r($e);
                    $upload_res=false;
                };

                //删除本地文件
                if($local_delete){
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                return $upload_res;
            break;
            case 4:

                $qiniu=$storage['qiniu'];
                $accessKey =$qiniu['accesskey'];
                $secretKey = $qiniu['secretkey'];
                $bucket = $qiniu['bucket'];

                // 构建鉴权对象
                $auth = new Auth($accessKey, $secretKey);
                // 生成上传 Token
                $token = $auth->uploadToken($bucket);
                // 要上传文件的本地路径
                $filePath = root_path() . "attachment/" .$file;
                // 上传到存储后保存的文件名
                $key = $file;
                // 初始化 UploadManager 对象并进行文件的上传。
                $uploadMgr = new UploadManager();
                // 调用 UploadManager 的 putFile 方法进行文件的上传。
                list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath, null, 'application/octet-stream', true, null, 'v2');

                //删除本地文件
                if($local_delete){
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                if ($err !== null) {
                    var_dump($err);
                    return false;
                } else {
                    // 上传成功
                    // var_dump($ret);
                    return true;
                }
            break;
            default:
            break;
        }

    }

    public static function  remoteDownload($url,$file_path=''){
        //远程下载
        $output = $file_path;//本地完整的文件地址（目录+名称+后缀）
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);  //兼容https路径文件(忽略证书)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);  //兼容https路径文件(忽略证书)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $file = curl_exec($ch);
        curl_close($ch);

        if(!empty($file_path)){
            if(file_put_contents($output, $file)) {
                return $output;
            }
        }else{
            return $file;
        }
    }


    //删除目录和目录下所有文件
    /*
    * $dir目录不带斜杠/
    */
    public static function removeDirectory($dir) {
        if(!is_dir($dir)){
            return true;
        }
        try{
           $handle = opendir($dir);
           while (false !== ($entry = readdir($handle))) {
              if ($entry != "." && $entry != "..") {
                 if (is_dir($dir.'/'.$entry)) {
                    self::removeDirectory($dir.'/'.$entry);
                 } else {
                    unlink($dir.'/'.$entry);
                 }
              }
           }
           closedir($handle);
           rmdir($dir);
        }catch(\Exception $e){
            // dump($e);
            return false;
        }
        return true;
    }

    // 拷贝目录和目录下所有文件
    /*
    * $dir目录没有斜杠/
    */
    public static function copyDirectory($old_dir,$new_dir) {
        try{
            $dir = opendir($old_dir);
            if(!is_dir($new_dir)){
                mkdir($new_dir,0777,true);
            }
            while(false !== ( $file = readdir($dir)) ) {
                if (( $file != '.' ) && ( $file != '..' )) {
                    if ( is_dir($old_dir . '/' . $file) ) {
                        self::copyDirectory($old_dir . '/' . $file,$new_dir . '/' . $file);
                    }
                    else {
                        copy($old_dir . '/' . $file,$new_dir . '/' . $file);
                    }
                }
            }
            closedir($dir);
        }catch(\Exception $e){
            // dump($e);
            return false;
        }
        return true;
    }



    /* 文件写入，加锁 */
    public static function write($file,$mode,$content){
        $fp = fopen($file, $mode);
        if (flock($fp, LOCK_EX)) {
            // 进行排它型锁定
            fwrite($fp, $content);
            flock($fp, LOCK_UN); // 释放锁定
        } else {
            //文件锁定中，程序阻塞
        }
        fclose($fp);
    }



}
