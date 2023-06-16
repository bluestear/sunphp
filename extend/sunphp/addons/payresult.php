<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-13 18:21:29
 * @LastEditors: light
 * @LastEditTime: 2023-05-26 16:53:38
 * @Description: SonLight Tech版权所有
 */

 declare(strict_types=1);

use app\admin\model\CoreAccount;

// 不能作为入口文件，只能被引用
defined('SUN_IN') or exit('Sunphp Access Denied');


define('IN_IA', true);


/* addons模块的payResult入口 */
global $_W,$_GPC;

$request = $app->request;





$module_now=$notify_post['module'];
$account_now=CoreAccount::where('id',$notify_post['acid'])->find();
if(empty($account_now)){
    exit('fail');
}



// 常量定义
!(defined('IA_ROOT')) && define('IA_ROOT', substr(root_path(),0,-1));
!(defined('ATTACHMENT_ROOT')) && define('ATTACHMENT_ROOT',IA_ROOT.DIRECTORY_SEPARATOR.'attachment');
!(defined('MODULE_ROOT')) && define('MODULE_ROOT',IA_ROOT.DIRECTORY_SEPARATOR.'addons'.DIRECTORY_SEPARATOR.$module_now);
!(defined('MODULE_URL')) && define('MODULE_URL',$request->domain()."/".'addons'."/".$module_now."/");
!(defined('DEVELOPMENT')) && define('DEVELOPMENT',false);




$_W['uniacid']=$notify_post['acid'];
$_W['siteroot']=$request->domain()."/";
$_W['siteurl']=$request->domain().$request->url();
$_W['current_module']['name']=$notify_post['module'];

$_W['account']=$account_now->toArray();



$str_a='site';
switch(intval($account_now['type'])){
    case 1:
        $str_a='site';
        break;
    case 2:
        $str_a='wxapp';
        break;
    case 3:
        $str_a='toutiaoapp';
        break;
    case 4:
        $str_a='webapp';
        break;
    case 5:
        // 注意这里手机app
        $str_a='phoneapp';
        break;
    case 6:
        $str_a='aliapp';
        break;
    case 7:
        $str_a='baiduapp';
        break;
    default:
    break;
}

// 手动构造$_GPC
$_GPC['i']=$notify_post['acid'];
$_GPC['c']='entry';
$_GPC['a']=$str_a;
$_GPC['m']=$module_now;
$_GPC['module_name']=$module_now;
$_GPC['do']='payResult';




$class_a=ucfirst(strtolower($str_a));
$class_module=ucfirst(strtolower($module_now)).'Module'.$class_a;

// 兼容数据操作
include_once root_path().'extend/sunphp/function/db_ims.php';

// 兼容常用方法，如message(),load()等等
include_once root_path().'extend/sunphp/addons/functions.php';

//兼容WeAccount::create()->sendTplNotice方法
include_once root_path().'extend/sunphp/addons/WeAccount.php';

//引入WeModule，兼容$this->操作方法
include_once root_path().'extend/sunphp/addons/WeModule'.$class_a.'.php';


include_once root_path().'addons/'.$module_now.'/'.strtolower($str_a).'.php';


$class_now=new $class_module();
$method='payResult';


if(session_id()){
    // 防止session_start阻塞
    session_commit();
}
// 手动传递参数
$class_now->$method($notify_post);









