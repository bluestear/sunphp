<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-13 18:21:29
 * @LastEditors: light
 * @LastEditTime: 2023-06-01 13:49:30
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
$_W['addons_index']='app';


// 执行HTTP应用并响应
$app=new App();
//必须手动初始化，加载配置
$app->initialize();


$request = $app->request;
include_once root_path() . 'extend/sunphp/addons/bootstrap.php';



$module_now=$_W['current_module']['name'];
$class_a=ucfirst(strtolower($_GPC['a']));
$class_module=ucfirst(strtolower($module_now)).'Module'.$class_a;






// 兼容数据操作
include_once root_path().'extend/sunphp/function/db_ims.php';

// 兼容常用方法，如message(),load()等等
include_once root_path().'extend/sunphp/addons/functions.php';






if($_GPC['c']=='entry'){

    //执行应用内部逻辑

    //兼容WeAccount::create()->sendTplNotice方法
    include_once root_path().'extend/sunphp/addons/WeAccount.php';

    //引入WeModule，兼容$this->操作方法
    include_once root_path().'extend/sunphp/addons/WeModule'.$class_a.'.php';


    include_once root_path().'addons/'.$module_now.'/'.strtolower($_GPC['a']).'.php';


    $class_now=new $class_module();


    if($class_a=='Site'){
        $method='doMobile'.$_GPC['do'];
    }else{
        // webapp、wxapp等入口
        $method='doPage'.$_GPC['do'];
    }


    if(session_id()){
        // 防止session_start阻塞
        session_commit();
    }

    $result=$class_now->$method();


    echo $result;
    die();

}else{

    //执行框架内逻辑
    include_once root_path().'extend/sunphp/addons/'.strtolower($_GPC['from']).'/'.strtolower($_GPC['c']).'/WeFrame'.$class_a.'.php';
    $class_frame='WeFrame'.$class_a;
    $class_method=strtolower($_GPC['do']);

    $class_frame_instance=new $class_frame();
    $result=$class_frame_instance->$class_method();

    echo $result;
    die();

}













