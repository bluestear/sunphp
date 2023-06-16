<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-15 16:11:26
 * @LastEditors: light
 * @LastEditTime: 2023-05-24 09:34:15
 * @Description: SonLight Tech版权所有
 */

 declare(strict_types=1);


use TencentCloud\Batch\V20170312\Models\TaskTemplateView;
use think\facade\View;


defined('SUN_IN') or exit('Sunphp Access Denied');


global $_W,$_GPC;

$module=$_W['current_module']['name'];

// 校验参数正确性
switch($_W['addons_index']){
    case 'app':
        $tpl_file= root_path().'addons/'.$module.'/template/mobile/'.$_W['sunphp_tpl_file'].'.html';
    break;
    case 'web':
        $tpl_file= root_path().'addons/'.$module.'/template/'.$_W['sunphp_tpl_file'].'.html';
    break;
    default:
    break;
}




// smarty模板也有语法差异
// require root_path().'vendor/smarty/smarty/libs/Smarty.class.php';
// $smarty = new Smarty;


// 模板引擎的语法差别（自定义）
// 1，手动处理{php echo }的语法
// 2，手动处理{template "common/header"}的语法
// 3，手动处理{template "common/header"}的语法

View::assign(get_defined_vars());
// View::assign(get_defined_constants());

$template_view=View::fetch($tpl_file);
echo $template_view;

