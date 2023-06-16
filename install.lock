<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-04-09 16:46:43
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 16:57:02
 * @Description: SonLight Tech版权所有
 */

//系统安装

$disable_functions = ini_get('disable_functions');
$disabled = explode(',', $disable_functions);
//判断是否包含在被禁用的数组中
if(in_array('exec', $disabled)){
	echo "请先取消php禁用函数exec";
	die();
}

$path=__DIR__;

//删除存在的.git文件夹
$git_dir=$path.'/'.'.git';
if(is_dir($git_dir)){
    print_r('根目录下已经存在/.git/文件夹');
    echo '<br/>';
    print_r('如需重新安装，请删除/.git/文件夹');
    echo '<br/>';
    die();
}

$gitee="https://gitee.com/bluestear/sunphp.git";
$cmd='cd '.$path;
$cmd.=' && git init ';
$cmd.=' && git remote add origin '.$gitee;
$cmd.=' && git pull  origin master ';

print_r('Sunphp安装文件下载中……');
echo '<br/>';

exec($cmd,$log,$status);
$data['log']=$log;
$data['execstr']=$cmd;
$data['status']=$status;



if($status==0){
$website=$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'];
header("Location:".$website);
}else{
    echo "安装文件下载失败！";
    echo '<br/>';
    print_r($log);
    echo '<br/>';
}




