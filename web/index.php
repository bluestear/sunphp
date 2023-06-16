<?php

/*
 * @Author: SonLight Tech
 * @Date: 2023-05-15 11:03:17
 * @LastEditors: light
 * @LastEditTime: 2023-05-31 17:42:27
 * @Description: SonLight Tech版权所有
 */


 declare(strict_types=1);

 // [ 应用入口文件 ]
namespace think;

define('SUN_IN', true);
define('IN_IA', true);
require __DIR__ . '/../vendor/autoload.php';


/* addons模块的入口地址 */
global $_W,$_GPC;
$_W['addons_index']='web';



// 执行HTTP应用并响应
$app=new App();
//必须手动初始化，加载配置
$app->initialize();


$request = $app->request;
include_once root_path() . 'extend/sunphp/addons/bootstrap.php';



// 不通过admin模块执行
$module_now=$_W['current_module']['name'];
$class_a=ucfirst('site');
$class_module=ucfirst(strtolower($module_now)).'Module'.$class_a;


// 兼容数据操作
include_once root_path().'extend/sunphp/function/db_ims.php';

// 兼容常用方法，如message(),load()等等
include_once root_path().'extend/sunphp/addons/functions.php';

//兼容WeAccount::create()->sendTplNotice方法
include_once root_path().'extend/sunphp/addons/WeAccount.php';


//引入WeModule，兼容$this->操作方法
include_once root_path().'extend/sunphp/addons/WeModule'.$class_a.'.php';

include_once root_path().'addons/'.$module_now.'/site.php';


$class_now=new $class_module();
$method='doWeb'.$_GPC['do'];

if(session_id()){
    // 防止session_start阻塞
    session_commit();
}

$result=$class_now->$method();

echo $result;
die();
















