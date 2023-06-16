<?php

declare(strict_types=1);

use app\admin\model\CoreAccount;
use app\admin\model\CoreApp;
use app\admin\model\CoreBindapp;
use app\admin\model\CoreStorage;
use app\admin\model\CoreUseaccount;
use app\admin\model\CoreUser;
use sunphp\cache\SunCache;

defined('SUN_IN') or exit('Sunphp Access Denied');

global $_W,$_GPC;

$time=time();
$get=$request->get();
$post=$request->post();
$header=$request->header();





// 校验参数正确性
switch($_W['addons_index']){
    case 'app':
        if(empty($get['i'])){
            echo "i参数错误！";
            die();
        }

        // 可能存在的参数
        $_W['member']=[
            'uid'=>''
        ];

    break;
    case 'web':
        if(empty($get['i'])){
            $cookie_i=cookie('sunphp_addons_uniacid');

            if(empty($cookie_i)){
                header('Location:'.$request->domain());
                die();
            }else{
                $get['i']=$cookie_i;

                // 将i参数嵌入到request->get()对象里面
                $request->withGet($get);
            }
        }else{

            // 更新i值
            setcookie('sunphp_addons_uniacid',$get['i'],time()+36000);

            // tp6方法设置无效
            // cookie('sunphp_addons_uniacid',$get['i'],36000);

        }



        //初始化page
        if(empty($get['page'])){
            $get['page']=1;
        }

        //表单token值，和checksubmit配合使用
        $_W['token']='sunphp_addons_index';

        // 检查后台用户是否登陆
        $cookie=$request->cookie();
        if(empty($cookie['sunphp_user_session_id'])){
            header('Location:'.$request->domain());
            die();
        }
        //检查用户是否存在
        $user=CoreUser::where('session_id',$cookie['sunphp_user_session_id'])->where('is_delete',0)->find();
        if(empty($user)){
            header('Location:'.$request->domain());
            die();
        }

        //后台登录用户的角色
        $_W['role']='operator';
        $_W['isadmin']=false;
        $_W['isfounder']=false;

        if($user['type']!=2){
            //检查使用者权限
            $use_account=CoreUseaccount::where([
                'uid'=>$user['id'],
                'acid'=>$get['i']
            ])->find();
            if(empty($use_account)){
                return response('');
                echo "无平台操作权限";
                die();
            }
            $request->use_account=$use_account->toArray();

            if($use_account['role']==2){
                // 平台所有者
                $_W['role']='owner';
                $_W['isadmin']=true;
            }

        }else{
            //后台登录用户的角色
            $_W['role']='founder';
            $_W['isadmin']=true;
            $_W['isfounder']=true;
        }

        //保存在middleware中
        $request->user=$user->toArray();

        //后台登录用户
        $_W['uid']=$user['id'];



    break;
    default:
    break;
}

if(empty($get['a'])){
    // a可能webapp、wxapp等
    $get['a']='site';
}

if(empty($get['c'])){
    // from=wxapp的时候，c可能是auth，a可能是session，调用的是框架方法，而不是进入应用
    $get['c']='entry';
}


if(!empty($get['module_name'])){
    $module_name=$get['module_name'];
}else if(!empty($get['m'])){
    $module_name=$get['m'];
}else{
    echo "module_name参数错误！";
    die();
}

if(empty($get['do'])){
    echo "do参数错误！";
    die();
}


//检查平台
$account=CoreAccount::where('id',$get['i'])->where('is_delete',0)->find();
if(empty($account)){
    echo "平台不存在！";
    die();
}



//检查应用
$module=CoreApp::where(['identity'=>$module_name,'dir'=>'addons'])->find();
if(empty($module)){
    echo "应用不存在！";
    die();
}


$request->account=$account->toArray();
$request->app=$module->toArray();



//检查平台是否绑定应用
$can_use=CoreBindapp::alias('a')->join('core_supports b','a.sid=b.id')
->where(['a.acid'=>$account['id'],'b.app_id'=>$module['id']])->find();
if(empty($can_use)){
    echo "平台未绑定应用";
    die();
}


// 常量定义
!(defined('IA_ROOT')) && define('IA_ROOT', substr(root_path(),0,-1));
!(defined('ATTACHMENT_ROOT')) && define('ATTACHMENT_ROOT',IA_ROOT.DIRECTORY_SEPARATOR.'attachment');
!(defined('MODULE_ROOT')) && define('MODULE_ROOT',IA_ROOT.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.$module_name);

!(defined('MODULE_URL')) && define('MODULE_URL',$request->domain()."/".'addons'."/".$module_name."/");
!(defined('TIMESTAMP')) && define('TIMESTAMP',$time);
!(defined('CLIENT_IP')) && define('CLIENT_IP',$request->ip());
!(defined('DEVELOPMENT')) && define('DEVELOPMENT',false);



// 构造wxapp的参数
if(!empty($get['from'])){
    switch($get['from']){
        case 'wxapp':
            if(!empty($get['state'])&&(strpos($get['state'],'we7sid-')!==false)){
                $wxapp_session=SunCache::get(str_replace('we7sid-','',$get['state']));
                if(!empty($wxapp_session)&&!empty($wxapp_session['openid'])){
                    $_W['openid']=$wxapp_session['openid'];
                }
            }
        break;
        default:
        break;
    }
}



// 构造$_W参数
$_W['timestamp']=$time;
$_W['clientip']=$request->ip();
$_W['siteroot']=$request->domain()."/";
$_W['siteurl']=$request->domain().$request->url();

// 本地附件url
$_W['attachurl_local']=$_W['siteroot']."attachment/";

// 开启远程就是远程附件地址
$storage=CoreStorage::where('acid',$get['i'])->find();

$sys_storage_set=false;
if(empty($storage)||$storage['type']==1){
    $storage=CoreStorage::where('acid',0)->find();
    $sys_storage_set=true;
}

if(empty($storage)){
    $type=1;
}else{
    $type=$storage->type;
}
switch($type){
    case 1:
        $_W['attachurl']=$_W['attachurl_local'];
        break;
    case 2:
        $oss=$storage->ali_oss;
        $_W['attachurl']=$oss['url'].'/';
        break;
    case 3:
        $oss=$storage->tencent_cos;
        $_W['attachurl']=$oss['url'].'/';
        break;
    case 4:
        $oss=$storage->qiniu;
        $_W['attachurl']=$oss['url'].'/';
        break;
}

$_W['attachurl_remote']=$_W['attachurl'];
$_W['config']['cookie']['pre']='';



//存储的类型
if($type>1){
    $_W['setting']['remote']['type']=$type;
}

$_W['isajax']=$request->isAjax();
$_W['ispost']=$request->isPost();
$_W['sitescheme']=$request->scheme();
$_W['ishttps']=$_W['sitescheme']=='https'?true:false;


$_W['uniacid']=$get['i'];
$_W['current_module']['name']=$module_name;
$_W['current_module']['version']=$module['version'];


// $_W['account']['level']="1";$account包含了level
$_W['account']=$account->toArray();



if($sys_storage_set){
    $system_storage=$storage;
}else{
    $system_storage=CoreStorage::where('acid',0)->field(['img_size','video_size','file_size'])->find();
}
$_W['setting']['upload']['audio']['limit']=$system_storage['video_size'];
$_W['setting']['upload']['image']['limit']=$system_storage['img_size'];
$_W['setting']['upload']['file']['limit']=$system_storage['file_size'];


// 打开的容器
if(empty($header['user-agent'])){
    $ua='';
}else{
    $ua = $header['user-agent'];
}

if(strpos($ua, 'MicroMessenger') == false && strpos($ua, 'Windows Phone') == false){
    //普通浏览器，不区分详细
    $_W['container']="unknown";
}else{
    // 微信浏览器
    $_W['container']="wechat";
}


// $_W['isfounder']
// $_W['role']="mdkeji_im";



// 构造gpc参数
//不带cookie $_GPC = array_merge($_COOKIE,$get, $post);
$_GPC = array_merge($get, $post);

//单独保存post的值
$_GPC['__input'] = $post;




// 转换为Sungpc对象，访问未知属性时，给默认值
// 注意：会导致二维数组无法赋值，如有二维数组赋值，需要注意格式
include_once root_path() . 'extend/sunphp/addons/SunGPC.php';
$_GPC=new SunGPC($_GPC);





