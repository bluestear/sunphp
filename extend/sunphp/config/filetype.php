<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-14 11:23:52
 * @LastEditors: light
 * @LastEditTime: 2023-05-04 11:18:06
 * @Description: SonLight Tech版权所有
 */

// 除图片视频音频以外，支持上传的文件类型
// 配置的格式：文件后缀=>[mime类型]
$sunphp_file_type=array(
    'pdf'=>'application/pdf',
    'doc'=>'application/msword',
    'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'ppt'=>'application/vnd.ms-powerpoint',
    'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'xls'=>'application/vnd.ms-excel',
    'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'txt'=>'text/plain',
    'zip'=>[
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream',
        'multipart/x-zip'
    ],
    'rar'=>[
        'application/x-rar-compressed',
        'application/octet-stream'
    ],
    'mp3'=>[
        'audio/mp3',
        'audio/mpeg'
    ],
    'crt'=>'application/x-x509-ca-cert',
    'pem'=>'application/octet-stream',
    'wav'=>'audio/x-wav',
    'wma'=>'audio/x-ms-wma',
    'mp4'=>'video/mp4',
    'mov'=>'video/quicktime',
    'avi'=>'video/x-msvideo',
    'flv'=>'application/octet-stream',
    'f4v'=>'application/octet-stream',
    'webm'=>'video/webm ',
    'jpg'=>'image/jpeg',
    'jpeg'=>'image/jpeg',
    'gif'=>'image/gif',
    'png'=>'image/png',
    'ai' => 'application/postscript',
    'eps' => 'application/postscript',
    'ps' => 'application/postscript'
);