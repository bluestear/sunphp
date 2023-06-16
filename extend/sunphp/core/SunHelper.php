<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-05 13:35:13
 * @LastEditors: light
 * @LastEditTime: 2023-05-17 15:32:25
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace sunphp\core;

use app\admin\model\CoreMenu;

defined('SUN_IN') or exit('Sunphp Access Denied');

/* 辅助多应用开发的帮助类 */
class SunHelper
{

    // 应用模块获取菜单
    public static function getMenus()
    {
        // 使用者菜单权限
        $user=request()->middleware('user');
        $app=request()->middleware('app');

        $app_id=$app['id'];

        if($user['type']!=2){
            $use_account=request()->middleware('use_account');
            $uaid=$use_account['id'];

            //筛选出不可以使用的
            $no_use=CoreMenu::alias('a')->join('core_usermenu b','a.id=b.menu_id')
            ->where(['a.app_id'=>$app_id,'b.can_use'=>0,'b.uaid'=>$uaid])->column(['b.menu_id']);
            if(empty($no_use)){
                //未设置，默认全部菜单
                $menus=CoreMenu::where('app_id',$app_id)->order(['id'=>'asc'])->select();
            }else{
                $con[]=['app_id','=',$app_id];
                $con[] = ['id', 'not in', $no_use];
                $menus=CoreMenu::where($con)->order(['id'=>'asc'])->select();
            }
        }else{
            $menus=CoreMenu::where('app_id',$app_id)->order(['id'=>'asc'])->select();
        }

        return $menus->toArray();
    }

}
