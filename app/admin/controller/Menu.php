<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\CoreApp;
use app\admin\model\CoreBindapp;
use app\admin\model\CoreMenu;
use app\admin\model\CoreUsermenu;

class Menu extends Base{


    public function userList()
    {
        $post = $this->request->post();
        $size = $post['limit'];
        $start = ($post['page'] - 1) * $size;


        $con[] = [
            'is_delete', '=', 0
        ];

        if (!empty($post['title'])) {
            $con[] = ['name', 'like', '%' . $post['title'] . '%'];
        }

        $order = 'desc';
        if ($post['sort'] == '+id') {
            $order = 'asc';
        }

        $app_id_list = CoreBindapp::alias('a')->join('core_supports b','a.sid=b.id')
        ->where('acid', $post['acid'])->column(['app_id']);

        $con[] = ['id', 'in', $app_id_list];


        $data = CoreApp::where($con)->order('id', $order)->limit($start, $size)
        ->field('id,name,icon,logo')
        ->select();

        if(!empty($data)){
            foreach($data as &$val){
                $val['menus']=CoreMenu::where('app_id',$val['id'])->field('id,title')->select();
            }
        }

        $total = CoreApp::where($con)->count();

        $result = [
            'items' => $data,
            'total' => $total
        ];

        if (!empty($post['first']) && $post['first'] == 1) {
            $cansue = CoreUsermenu::where('uaid', $post['uaid'])->field('menu_id,can_use')->select();
            $result['usermenu']=$cansue;
        }

        return jsonResult(200, '操作成功', $result);
    }

    public function setList(){
        $post = $this->request->post();
        foreach($post['usermenu'] as $val){
            $con=[
                'uaid'=>$post['uaid'],
                'menu_id'=>$val['menu_id']
            ];
            $m=CoreUsermenu::where($con)->find();
            if(!empty($m)){
                if($m->can_use==$val['can_use']){
                    continue;
                }
                $m->can_use=$val['can_use'];
                $m->save();
            }else{
                $con['can_use']=$val['can_use'];
                CoreUsermenu::create($con);
            }
        }
        return jsonResult(200, '操作成功', []);

    }


}