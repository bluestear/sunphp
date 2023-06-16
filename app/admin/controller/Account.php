<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-17 10:48:42
 * @LastEditors: light
 * @LastEditTime: 2023-05-25 18:14:31
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\admin\controller;
use app\admin\model\CoreAccount;
use app\admin\model\CoreUseaccount;
use app\admin\model\CoreBindapp;
use app\admin\model\CoreApp;
use app\admin\model\CoreCreate;
use app\admin\model\CoreEmail;
use app\admin\model\CoreMenu;
use app\admin\model\CorePay;
use app\admin\model\CoreSms;
use app\admin\model\CoreStorage;
use app\admin\model\CoreUseapp;
use app\admin\model\CoreUser;

class Account extends Base{

    public function create(){
        $post=$this->request->post();
        if(empty($post['end_time'])){
            $post['end_time']=null;
        }

        //创建权限验证
        $user=$this->request->middleware('user');

        $type_array=['wx_gzh','wx_xcx','zjtd_xcx','pc','app','zfb_xcx','bd_xcx'];
        $type=intval($post['type'])-1;

        if($user['type']!=2){
            $create=CoreCreate::where('uid',$user['id'])->find();
            if(empty($create)){
                return jsonResult(400,'无创建该平台权限',[]);
            }
            $can_create=false;

            if($type>0&&$type<=6){
                if($create[$type_array[$type]]>=1){
                    $can_create=true;
                }
            }else{
                return jsonResult(400,'参数错误',[]);
            }
            if(!$can_create){
                return jsonResult(400,'无创建该平台权限',[]);
            }
        }

        $fields=['type','name','avatar','appid','secret','end_time','remark'];
        if($post['type']==1){
            array_push($fields,'level');
        }
        $c=CoreAccount::create($post,$fields);
        //绑定使用者
        CoreUseaccount::create([
            'uid'=>$user['id'],
            'acid'=>$c->id,
            'role'=>2
        ]);
        if($user['type']!=2){
            CoreCreate::where('uid',$user['id'])->dec($type_array[$type],1)->update();
        }
        return jsonResult(200,'操作成功',[]);
    }
    public function list(){
        $post=$this->request->post();
        $size=$post['limit'];
        $start=($post['page']-1)*$size;

        $con[]=[
            'is_delete','=',$post['is_delete']
        ];
        if($post['type']>0){
            $con[]=['type','=',$post['type']];
        }
        if(!empty($post['title'])){
            $con[]=['name','like', '%'.$post['title'].'%'];
        }

        $order='desc';
        if($post['sort']=='+id'){
            $order='asc';
        }

        //用户角色可使用的平台
        $user=$this->request->middleware('user');

        $join_type='INNER';
        if($user['type']==2){
            $join_type='LEFT';
        }else{
            // $con[]=['b.uid','=',$user['id]];
        }

        $data=CoreAccount::alias('a')->join('core_useaccount b','a.id=b.acid and b.uid='.$user['id'],$join_type)
        ->where($con)->order(['b.order'=>'desc','id'=>$order])->limit($start,$size)
        ->field('a.*,b.uid,b.role,b.order')->select();

        $total=CoreAccount::alias('a')->join('core_useaccount b','a.id=b.acid and b.uid='.$user['id'],$join_type)
        ->where($con)->count();
        $result=[
            'items'=>$data,
            'total'=>$total
        ];
        return jsonResult(200,'操作成功',$result);
    }

    public function appList()
    {
        $post = $this->request->post();
        $size = $post['limit'];
        $start = ($post['page'] - 1) * $size;


        $con[] = [
            'is_delete', '=', 0
        ];

        $user=$this->request->middleware('user');

        if($user['type']!=2){
            $role=CoreUseaccount::where(['uid'=>$user['id'],'acid'=>$post['acid']])->value('role');
            if(empty($role)||$role!=2){
                return jsonResult(400, '无操作权限', []);
            }
        }

        $type=CoreAccount::where('id',$post['acid'])->value('type');
        if(empty($type)){
            return jsonResult(400, '平台不存在', []);
        }
        $con[] = ['b.type', '=', $type];

        if (!empty($post['title'])) {
            $con[] = ['name', 'like', '%' . $post['title'] . '%'];
        }

        $is_fetch=false;
        if (!empty($post['is_canuse'])) {
            $is_fetch=true;
            // 索引数组写法，必须带有''，才可以解析
            $cansue = CoreBindapp::where('acid', $post['acid'])->column(['sid']);
            $con[] = ['b.id', 'in', $cansue];
        }


        //平台管理员模块权限
        if($user['type']!=2){
            if(!$is_fetch){
                //不是已启用的，加入用户模块权限
                $is_fetch=true;
                $cansue = CoreBindapp::where('acid', $post['acid'])->column(['sid']);
                $useapp=CoreUseapp::where('uid',$user['id'])->column('sid');

                $my_app=array_merge($cansue,array_diff($useapp,$cansue));
                $con[] = ['b.id', 'in', $my_app];
            }
        }


        $order = 'desc';
        if ($post['sort'] == '+id') {
            $order = 'asc';
        }


        $data = CoreApp::alias('a')->join('core_supports b','a.id=b.app_id')
        ->where($con)->order('a.id', $order)->limit($start, $size)
        ->field('a.id,a.dir,a.identity,a.name,a.icon,a.logo,b.id as s_id,b.type as s_type')
        ->select();

        $total = CoreApp::alias('a')->join('core_supports b','a.id=b.app_id')
        ->where($con)->count();


        $result = [
            'items' => $data,
            'total' => $total
        ];

        if (!empty($post['first']) && $post['first'] == 1) {
            if(!$is_fetch){
                $cansue = CoreBindapp::where('acid', $post['acid'])->column(['sid']);
            }
            $result['useapp']=$cansue;
        }

        return jsonResult(200, '操作成功', $result);
    }

    public function useapp(){
        $post = $this->request->post();

        $user=$this->request->middleware('user');

        if($user['type']!=2){
            $role=CoreUseaccount::where(['uid'=>$user['id'],'acid'=>$post['acid']])->value('role');
            if(empty($role)||$role!=2){
                return jsonResult(400, '无操作权限', []);
            }
        }


        $cansue = CoreBindapp::where('acid', $post['acid'])->column(['sid']);
        $del=array_diff($cansue,$post['useapp']);
        if(!empty($del)){
            CoreBindapp::where('acid',$post['acid'])->where('sid','in',$del)->delete();
        }
        $add=array_diff($post['useapp'],$cansue);
        if(!empty($add)){
            foreach($add as $v){
                //验证权限
                if($user['type']!=2){
                    $can_use=CoreUseapp::where(['uid'=>$user['id'],'sid'=>$v])->find();
                    if(empty($can_use)){
                        continue;
                    }
                }
                CoreBindapp::create(['acid'=>$post['acid'],'sid'=>$v]);
            }
        }
        return jsonResult(200, '操作成功', []);

    }

    //当前启用的app应用模块
    public function myapp()
    {
        $post = $this->request->post();
        $size = $post['limit'];
        $start = ($post['page'] - 1) * $size;


        $con[] = [
            'is_delete', '=', 0
        ];

        $type=CoreAccount::where('id',$post['acid'])->value('type');
        if(empty($type)){
            return jsonResult(400, '平台不存在', []);
        }
        $con[] = ['b.type', '=', $type];

        if (!empty($post['title'])) {
            $con[] = ['name', 'like', '%' . $post['title'] . '%'];
        }

        // $cansue = CoreBindapp::where('acid', $post['acid'])->column(['sid']);
        // $con[] = ['b.id', 'in', $cansue];

        $con[] = ['c.acid', '=', $post['acid']];


        $order = 'desc';
        if ($post['sort'] == '+id') {
            $order = 'asc';
        }


        $data = CoreApp::alias('a')->join('core_supports b','a.id=b.app_id')->join('core_bindapp c','b.id=c.sid')
        ->where($con)->order(['c.order'=>'desc','a.id'=>$order])->limit($start, $size)
        ->field('a.id,a.dir,a.identity,a.name,a.icon,a.logo,a.admin,b.id as s_id,b.type as s_type,c.order,c.id as b_id')
        ->select();

        $total = CoreApp::alias('a')->join('core_supports b','a.id=b.app_id')->join('core_bindapp c','b.id=c.sid')
        ->where($con)->count();


        $result = [
            'items' => $data,
            'total' => $total
        ];
        if (!empty($post['first']) && $post['first'] == 1) {
            $account=CoreAccount::where('id',$post['acid'])->field('name,avatar,type')->find();
            $result['account']=$account;
        }

        return jsonResult(200, '操作成功', $result);
    }


    // 获取平台中的模块信息
    public function module(){
        $post = $this->request->post();

        $account=CoreAccount::where('id',$post['acid'])->where('is_delete',0)->field('name,avatar,type')->find();
        if(empty($account)){
            return jsonResult(400, '平台不存在', []);
        }

        //检查平台是否绑定应用
        $can_use=CoreBindapp::alias('a')->join('core_supports b','a.sid=b.id')
        ->where(['a.acid'=>$post['acid'],'b.app_id'=>$post['app_id']])->find();
        if(empty($can_use)){
            return jsonResult(400, '平台未绑定应用', []);
        }

        $module=CoreApp::where('id',$post['app_id'])->find();

        // 使用者菜单权限
        $user=$this->request->middleware('user');
        if($user['type']!=2){
            $uaid=CoreUseaccount::where(['uid'=>$user['id'],'acid'=>$post['acid']])->value('id');
            if(empty($uaid)){
                //无使用者权限，并且跳转404页面
                return jsonResult(404, '无使用者权限', []);
            }
            //筛选出不可以使用的
            $no_use=CoreMenu::alias('a')->join('core_usermenu b','a.id=b.menu_id')
            ->where(['a.app_id'=>$post['app_id'],'b.can_use'=>0,'b.uaid'=>$uaid])->column(['b.menu_id']);
            if(empty($no_use)){
                //未设置，默认全部菜单
                $menus=CoreMenu::where('app_id',$post['app_id'])->order(['id'=>'asc'])->select();
            }else{
                $con[]=['app_id','=',$post['app_id']];
                $con[] = ['id', 'not in', $no_use];
                $menus=CoreMenu::where($con)->order(['id'=>'asc'])->select();
            }
        }else{
            $menus=CoreMenu::where('app_id',$post['app_id'])->order(['id'=>'asc'])->select();
        }

        $result = [
            'account' => $account,
            'module' => $module,
            'menus'=>$menus
        ];
        return jsonResult(200, '操作成功', $result);
    }

    public function top(){
        $post=$this->request->post();
        $max=CoreBindapp::where('acid',$post['acid'])->max('order');
        $max++;
        $data=['order'=>$max];
        CoreBindapp::where('id',$post['b_id'])->update($data);
        return jsonResult(200, '操作成功', $data);
    }

    public function topAccount(){
        $post=$this->request->post();
        $user=$this->request->middleware('user');
        $con=[
            'uid'=>$user['id']
        ];
        $max=CoreUseaccount::where($con)->max('order');
        if(empty($max)){
            $max=2;
        }else{
            $max++;
        }
        $con['acid']=$post['acid'];

        $u=CoreUseaccount::where($con)->find();
        if(empty($u)){
            //系统管理员置顶
            if($user['type']==2){
                $con['role']=2;
                $con['order']=$max;
                CoreUseaccount::create($con);
            }else{
                return jsonResult(400, '无权限操作', []);
            }
        }else{
            $u->save(['order'=>$max]);
        }

        return jsonResult(200, '操作成功', ['order'=>$max]);
    }

    public function getPay(){
        $post=$this->request->post();
        $pay=CorePay::where('acid',$post['acid'])->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$pay);
    }

    public function updatePay(){
        $post=$this->request->post();
        $s=CorePay::where('acid',$post['acid'])->find();
        if(empty($s)){
            CorePay::create($post,['acid','wx_switch','ali_switch','wx_mchid','wx_apikey','wx_cert','wx_public_cert','ali_appid','ali_appkey','ali_app_cert','ali_public_cert','ali_root_cert']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function getStorage(){
        $post=$this->request->post();
        $storage=CoreStorage::where('acid',$post['acid'])->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$storage);
    }

    public function updateStorage(){
        $post=$this->request->post();
        $s=CoreStorage::where('acid',$post['acid'])->find();
        if(empty($s)){
            CoreStorage::create($post,['acid','type','ali_oss','tencent_cos','qiniu']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function getSms(){
        $post=$this->request->post();
        $sms=CoreSms::where('acid',$post['acid'])->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$sms);
    }

    public function updateSms(){
        $post=$this->request->post();
        $s=CoreSms::where('acid',$post['acid'])->find();
        if(empty($s)){
            CoreSms::create($post,['acid','type','ali_sms','tencent_sms']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function getEmail(){
        $post=$this->request->post();
        $email=CoreEmail::where('acid',$post['acid'])->withoutField(['id','acid','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$email);
    }

    public function updateEmail(){
        $post=$this->request->post();
        $s=CoreEmail::where('acid',$post['acid'])->find();
        if(empty($s)){
            CoreEmail::create($post,['acid','email_name','email_sender','email_code','email_smtp','email_sign']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function adduser(){
        $post=$this->request->post();
        $user=CoreUser::where('name',$post['name'])->field(['id','type'])->find();
        if(empty($user)){
            return jsonResult(400,'用户名不存在',[]);
        }
        if($user['type']==2){
            return jsonResult(400,'系统管理员无需添加',[]);
        }

        $con=['uid'=>$user['id'],'acid'=>$post['acid']];
        $exit=CoreUseaccount::where($con)->find();
        if(!empty($exit)){
        return jsonResult(400,'用户名已添加',[]);
        }
        $con['role']=$post['role'];
        CoreUseaccount::create($con);
        return jsonResult(200,'操作成功',[]);
    }


    public function update(){
        $post=$this->request->post();
        $fields=['name','avatar','appid','secret','end_time','remark'];
        if($post['type']==1){
            array_push($fields,'level');
        }
        CoreAccount::update($post,['id'=>$post['id']],$fields);
        return jsonResult(200,'操作成功',[]);
    }

    public function delete(){
        $post=$this->request->post();
        // CoreAccount::destroy($post['ids']);
        // CoreAccount::update(['is_delete'=>1],[['id','in',$post['ids']]]);
        $d=$post['is_delete']+1;

        $user=$this->request->middleware('user');

        if($user['type']==2){
            //管理员删除
            CoreAccount::where('id','in',$post['ids'])->update(['is_delete'=>$d]);
        }else{
        //操作员移除权限
        foreach($post['ids'] as $v){
            $role=CoreUseaccount::where(['uid'=>$user['id'],'acid'=>$v])->value('role');
            switch($role){
                case 1:
                    CoreUseaccount::where(['uid'=>$user['id'],'acid'=>$v])->delete();
                break;
                case 2:
                    CoreAccount::where('id',$v)->update(['is_delete'=>$d]);
                break;
                default:
                break;
            }
        }
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function deluser(){
        $post=$this->request->post();

        $user=$this->request->middleware('user');

        if($user['type']==2){
            CoreUseaccount::where('id','in',$post['ids'])->where('acid',$post['acid'])->delete();
        }else{
            foreach($post['ids'] as $v){
                $con=['id'=>$v,'acid'=>$post['acid']];
                $u=CoreUseaccount::where($con)->find();
                if($u['uid']==$user['id']){
                    continue;
                }else{
                    $u->delete();
                }
            }
        }

        return jsonResult(200,'操作成功',[]);
    }

    public function resume(){
        $post=$this->request->post();
        $user=$this->request->middleware('user');

        if($user['type']==2){
            CoreAccount::where('id','in',$post['ids'])->update(['is_delete'=>0]);
        }else{
            foreach($post['ids'] as $v){
                $role=CoreUseaccount::where(['uid'=>$user['id'],'acid'=>$v])->value('role');
                if($role==2){
                    CoreAccount::where('id',$v)->update(['is_delete'=>0]);
                }
            }
        }
        return jsonResult(200,'操作成功',[]);
    }

    //当前平台使用者
    public function userList()
    {
        $post = $this->request->post();
        $size = $post['limit'];
        $start = ($post['page'] - 1) * $size;

        $con[] = [
            'is_delete', '=', 0
        ];
        if ($post['role'] > 0) {
            $con[] = ['role', '=', $post['role']];
        }
        if (!empty($post['title'])) {
            $con[] = ['name', 'like', '%' . $post['title'] . '%'];
        }

        $con[] = ['b.acid', '=', $post['acid']];

        $order = 'desc';
        if ($post['sort'] == '+id') {
            $order = 'asc';
        }

        //系统管理员不显示
        $con[] = ['a.type', '<>', 2];


        $data = CoreUser::alias('a')->join('core_useaccount b','a.id=b.uid')
        ->where($con)->order(['b.role'=>'desc','a.id'=>$order])->limit($start, $size)
        ->field('a.id,a.name,a.avatar,b.id as ua_id,b.role')
        ->select();

        $total = CoreUser::alias('a')->join('core_useaccount b','a.id=b.uid')
        ->where($con)->count();


        $result = [
            'items' => $data,
            'total' => $total
        ];

        if (!empty($post['first']) && $post['first'] == 1) {
            $account=CoreAccount::where('id',$post['acid'])->field('name,avatar,type')->find();
            $result['account']=$account;
        }


        return jsonResult(200, '操作成功', $result);
    }


}