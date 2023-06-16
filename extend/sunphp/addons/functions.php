<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-16 15:31:11
 * @LastEditors: light
 * @LastEditTime: 2023-05-26 13:57:48
 * @Description: SonLight Tech版权所有
 */
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-16 15:31:11
 * @LastEditors: light
 * @LastEditTime: 2023-05-16 15:44:26
 * @Description: SonLight Tech版权所有
 */


declare(strict_types=1);

defined('SUN_IN') or exit('Sunphp Access Denied');

use TencentCloud\Batch\V20170312\Models\TaskTemplateView;
use think\facade\View;
use sunphp\account\SunAccount;
use sunphp\file\SunFile;

function message($title,$url='',$type='success'){
    $tpl_file= root_path().'view/sunphp/message/show.html';
    View::assign([
        'title'=>$title,
        'url'=>$url,
        'type'=>$type
    ]);

    $template_view=View::fetch($tpl_file);
    echo $template_view;
    die();
}

function pagination($total,$index,$size){
    global $_W,$_GPC;

    $web_url='/web/index.php?i='.$_GPC['i'].'&c=site&a=entry&module_name='.$_W['current_module']['name'].'&do='.$_GPC['do'];

    $html='<ul class="pagination">';

	$page = intval($_GPC["page"]);
	$pindex = max(1, $page);

    $num=ceil($total/$size);

    $preindex=1;
    if($pindex>1){
        $preindex=$pindex-1;
    }
    $next=$pindex+1;
    if($next>=$num){
        $next=$num;
    }

    if($num>1){
        $html.='<li><a href="'.$web_url.'&page=1">首页</a></li>';
        $html.='<li><a href="'.$web_url.'&page='.$preindex.'">&laquo;上一页</a></li>';
    }

    for($i=0;$i<$num;$i++){
        $page_i=$i+1;
        $html.='<li><a href="'.$web_url.'&page='.$page_i.'">'.$page_i.'</a></li>';
    }

    if($num>1){
         $html.='<li><a href="'.$web_url.'&page='.$next.'">&raquo;下一页</a></li>';
        $html.='<li><a href="'.$web_url.'&page='.$num.'">尾页</a></li>';
    }

    $html.='</ul>';

    return $html;
}

function checksubmit($var = 'submit', $allowget = false){
    global $_GPC;
    if($_GPC['token']=='sunphp_addons_index'){
        return true;
    }
   return false;
}


function tpl_form_field_image($field, $url, $arg='', $extras){

    global $_W,$_GPC;
    $upload_url=$_W['siteroot']."index.php/admin/file/upload?i=".$_GPC['i'];
    $attach_url=$_W['attachurl'];

    $html='<div>';

    $html.='<div>';
    $html.='<input class="form-control" id="'.$field.'" accept="image/*" type="text" name="'.$field.'" url="'.$url.'" value="'.$url.'">';

    $html.='<input class="sun-input-file" type="file" onchange="tapImage'.$field.'(this)">';



    $html.='</div>';

    $html.='<div>';
    $html.='<img id="img'.$field.'" src="'.$url.'" class="sun-img">';
    $html.='</div>';

    $html.='</div>';



    $html.='<script>';

    $html.='function tapImage'.$field.'(t){';

    $html.='var file=$(t)[0].files[0];';
    $html.='if(!file) {';
        $html.='	return;';
        $html.=' }';
        $html.='if(file.type==""){';
            $html.='file = new File([file], new Date().getTime()+".jpg",{type:"image/jpeg"});';
            $html.='}';

            $html.='var formdata=new FormData();';
            $html.='formdata.append("file_type","img");';

            $html.='formdata.append("session_id",localStorage.getItem("sunphp_admin_session_id"));';

            $html.='formdata.append("file_img",file);';
            $html.='$.ajax({';
                $html.='url:"'.$upload_url.'",';
                $html.='data:formdata,';
                $html.='headers:{"token":localStorage.getItem("sunphp_admin_access_token")},';
                $html.='type:"POST",';
                $html.='catch:false,';
                $html.='contentType:false,';
                $html.='processData:false,';
                $html.='success:function(result){';
                    $html.='if(result.status==200){';
                        $html.='var imgurl ="'.$attach_url.'"+result.data.path;';
                        $html.='$("input#'.$field.'").val(result.data.path);';
                        $html.='$("input#'.$field.'").attr("url",imgurl);';

                        $html.='$("img#img'.$field.'").attr("src",imgurl);';

                        $html.='}else if([401,402,403].indexOf(result.status)>-1){';
                        $html.='location.href="'.$_W['siteroot'].'";';
                        $html.='}';

                        $html.='}';
                        $html.='});';

    $html.='}';


    $html.='</script>';

return $html;

}


function tpl_form_field_audio($field, $url, $arg='', $extras){

    global $_W,$_GPC;
    $upload_url=$_W['siteroot']."index.php/admin/file/upload?i=".$_GPC['i'];
    $attach_url=$_W['attachurl'];

    $html='<div>';

    $html.='<div>';
    $html.='<input class="form-control" id="'.$field.'" accept="audio/*" type="text" name="'.$field.'" url="'.$url.'" value="'.$url.'">';

    $html.='<input class="sun-input-file" type="file" onchange="tapAudio'.$field.'(this)">';



    $html.='</div>';

    $html.='<div>';
    $html.='<audio id="audio'.$field.'" controls>';
    $html.='<source id="source'.$field.'" src="'.$url.'" class="sun-audio">';
    $html.='</audio>';
    $html.='</div>';

    $html.='</div>';



    $html.='<script>';

    $html.='function tapAudio'.$field.'(t){';

    $html.='var file=$(t)[0].files[0];';
    $html.='if(!file) {';
        $html.='	return;';
        $html.=' }';

            $html.='var formdata=new FormData();';
            $html.='formdata.append("file_type","voice");';

            $html.='formdata.append("session_id",localStorage.getItem("sunphp_admin_session_id"));';

            $html.='formdata.append("file_voice",file);';
            $html.='$.ajax({';
                $html.='url:"'.$upload_url.'",';
                $html.='data:formdata,';
                $html.='headers:{"token":localStorage.getItem("sunphp_admin_access_token")},';
                $html.='type:"POST",';
                $html.='catch:false,';
                $html.='contentType:false,';
                $html.='processData:false,';
                $html.='success:function(result){';
                    $html.='if(result.status==200){';
                        $html.='var url ="'.$attach_url.'"+result.data.path;';
                        $html.='$("input#'.$field.'").val(result.data.path);';
                        $html.='$("input#'.$field.'").attr("url",url);';

                        $html.='$("source#source'.$field.'").attr("src",url);';
                        $html.='$("audio#audio'.$field.'").load();';

                        $html.='}else if([401,402,403].indexOf(result.status)>-1){';
                        $html.='location.href="'.$_W['siteroot'].'";';
                        $html.='}';

                        $html.='}';
                        $html.='});';

    $html.='}';


    $html.='</script>';

return $html;

}

function tpl_form_field_video($field, $url, $arg='', $extras){

    global $_W,$_GPC;
    $upload_url=$_W['siteroot']."index.php/admin/file/upload?i=".$_GPC['i'];
    $attach_url=$_W['attachurl'];

    $html='<div>';

    $html.='<div>';
    $html.='<input class="form-control" id="'.$field.'" accept="video/*" type="text" name="'.$field.'" url="'.$url.'" value="'.$url.'">';

    $html.='<input class="sun-input-file" type="file" onchange="tapVideo'.$field.'(this)">';



    $html.='</div>';

    $html.='<div>';
    $html.='<video id="video'.$field.'" src="'.$url.'"  controls="controls" class="sun-video"></video>';
    $html.='</div>';

    $html.='</div>';



    $html.='<script>';

    $html.='function tapVideo'.$field.'(t){';

    $html.='var file=$(t)[0].files[0];';
    $html.='if(!file) {';
        $html.='	return;';
        $html.=' }';

            $html.='var formdata=new FormData();';
            $html.='formdata.append("file_type","video");';

            $html.='formdata.append("session_id",localStorage.getItem("sunphp_admin_session_id"));';

            $html.='formdata.append("file_video",file);';
            $html.='$.ajax({';
                $html.='url:"'.$upload_url.'",';
                $html.='data:formdata,';
                $html.='headers:{"token":localStorage.getItem("sunphp_admin_access_token")},';
                $html.='type:"POST",';
                $html.='catch:false,';
                $html.='contentType:false,';
                $html.='processData:false,';
                $html.='success:function(result){';
                    $html.='if(result.status==200){';
                        $html.='var url ="'.$attach_url.'"+result.data.path;';
                        $html.='$("input#'.$field.'").val(result.data.path);';
                        $html.='$("input#'.$field.'").attr("url",url);';

                        $html.='$("video#video'.$field.'").attr("src",url);';

                        $html.='}else if([401,402,403].indexOf(result.status)>-1){';
                        $html.='location.href="'.$_W['siteroot'].'";';
                        $html.='}';

                        $html.='}';
                        $html.='});';

    $html.='}';


    $html.='</script>';

return $html;

}


function load(){
    static $sunphp_load;
	if(empty($sunphp_load)) {
        include_once __DIR__ . '/SunLoader.php';
		$sunphp_load = new SunLoader();
	}
	return $sunphp_load;
}


function logging_run($arg){
    $log = app()->log;
    $log->write($arg);
}

function ihttp_post($url, $post_data)
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

function ihttp_get($url){

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

function cache_write($name,$value,$expire_time=0){
    $cache=app()->cache;
    if($expire_time>0){
        $cache->set($name,$value, $expire_time);
    }else{
        $cache->set($name,$value);
    }
}

function cache_load($name){
    $cache=app()->cache;
    return $cache->get($name);
}

function cache_delete($name){
    $cache=app()->cache;
    $cache->delete($name);
}

function cache_clean(){
    $cache=app()->cache;
    $cache->clear();
}

function mc_oauth_userinfo($unacid){
    $account=SunAccount::create($unacid);
    $userinfo=$account->login();
    return $userinfo;
}

//生成随机文件名称
function file_random_name($path,$ext){
    //指定文件名称
    do {
        $data = uniqid("", true);
        $data .= microtime();
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['REMOTE_PORT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $hash = strtolower(hash('ripemd128', "sunphp" . md5($data)));
        $filename = md5($hash) . '.' . $ext;
    } while (file_exists(root_path() . "attachment/" . $path  . $filename));

    return $filename;
}

// 本地上传，不远程上传
function file_upload($file,$type){

    $key='';
    foreach($_FILES as $k=>$v){
        if($file['tmp_name']==$v['tmp_name']&&$file['size']==$v['size']){
            $key=$k;
            break;
        }
    }

    if(!empty($key)){
         $res=SunFile::upload($key,$type,false,false);
         if($res['status']==1){
            $res['success']=1;
            return $res;
         }
    }
    return false;
}

function file_remote_upload($path,$local_delete=true){
    $res=SunFile::remoteUpload($path,$local_delete);
    return $res;
}

function is_error($arg){
    // 注意！报错返回true
    if(empty($arg)){
        return true;
    }
    return false;
}

// 编译文件
/*
$source：原始文件
$compile：编译后的模板文件
*/

function mkdirs($path) {
	if (!is_dir($path)) {
		mkdirs(dirname($path));
		mkdir($path);
	}
	return is_dir($path);
}

function file_move($temp_file, $real_file) {
	mkdirs(dirname($real_file));
	if (is_uploaded_file($temp_file)) {
		move_uploaded_file($temp_file, $real_file);
	} else {
		rename($temp_file, $real_file);
	}
	return is_file($real_file);
}

function template_compile($source, $compile){
    global $_W,$_GPC;
    View::assign(get_defined_vars());
    // View::assign(get_defined_constants());

    $template_view=View::fetch($source);
    //将文件写入编译后的目录

    $path = dirname($compile);
	if (!is_dir($path)) {
		mkdirs($path);
	}

	file_put_contents($compile, $template_view);
    return true;
}









// 无实际操作的无效方法
function checkauth(){
    return false;
}
//代金券和折扣券的兑换记录,
function mc_openid2uid($user_openid){
    return false;
}
function mc_credit_update($arg1='',$arg2='',$arg3='',$arg4='',$arg5='',$arg6=''){
    return false;
}

