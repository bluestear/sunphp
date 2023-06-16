<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-22 14:21:35
 * @LastEditors: light
 * @LastEditTime: 2023-05-04 09:30:43
 * @Description: SonLight Tech版权所有
 */
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-17 10:48:42
 * @LastEditors: light
 * @LastEditTime: 2023-03-31 11:39:54
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
namespace app\admin\controller;

use app\admin\model\CoreAccount;
use app\admin\model\CoreCreate;
use app\admin\model\CoreSystem;
use app\admin\model\CoreUseaccount;
use app\admin\model\CoreUseapp;
use app\admin\model\CoreUser;
use app\admin\validate\ValidateUser;
use Ramsey\Uuid\Uuid;
use sunphp\jwt\SunJwt;
use think\exception\ValidateException;

class User extends Base{

    protected $middleware=[
        \app\admin\middleware\AuthAdmin::class=>['except'=>['info','route','login','logout','register','change']]
    ];

    public function create(){

        $post=$this->request->post();
        if(empty($post['end_time'])){
            $post['end_time']=null;
        }
        //排除同名用户
        $u=CoreUser::where('name',$post['name'])->find();
        if(!empty($u)){
            return jsonResult(400,'用户名已存在！',[]);
        }
        if(empty($post['pwd'])){
            return jsonResult(400,'密码不能为空！',[]);
        }

        //md5加密pwd
        $sign=CoreSystem::where('id',1)->value('sys_sign');
        $post['pwd']=md5($post['pwd'].$sign);


        CoreUser::create($post,['type','name','avatar','pwd','phone','end_time','remark']);
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

        $data=CoreUser::where($con)->order('id',$order)->limit($start,$size)->withoutField(['is_delete','session_id','pwd','create_time','update_time'])->select();
        $total=CoreUser::where($con)->count();
        $result=[
            'items'=>$data,
            'total'=>$total
        ];
        return jsonResult(200,'操作成功',$result);
    }
    public function update(){
        $post=$this->request->post();

        //排除同名用户
        $u=CoreUser::where('name',$post['name'])->find();
        if(!empty($u)&&$u->id!=$post['id']){
            return jsonResult(400,'用户名已存在！',[]);
        }
        if(!empty($post['pwd'])){
            //md5加密pwd
            $sign=CoreSystem::where('id',1)->value('sys_sign');
            $post['pwd']=md5($post['pwd'].$sign);
        }
        CoreUser::update($post,['id'=>$post['id']],['type','name','pwd','phone','avatar','remark','end_time']);
        return jsonResult(200,'操作成功',[]);
    }

    public function delete(){
        $post=$this->request->post();
        // CoreUser::destroy($post['ids']);
        // CoreUser::update(['is_delete'=>1],[['id','in',$post['ids']]]);
        if($post['is_delete']>=1){
            CoreUser::where('id', 'in', $post['ids'])->delete();
            //删除权限
            CoreCreate::where('uid', 'in', $post['ids'])->delete();
            CoreUseapp::where('uid', 'in', $post['ids'])->delete();
        }else{
            $d=$post['is_delete']+1;
            CoreUser::where('id','in',$post['ids'])->update(['is_delete'=>$d]);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function resume(){
        $post=$this->request->post();
        CoreUser::where('id','in',$post['ids'])->update(['is_delete'=>0]);
        return jsonResult(200,'操作成功',[]);
    }

    public function login(){

        $post=$this->request->only(['name','pwd']);
        if(empty($post['name'])||empty($post['pwd'])){
            return jsonResult(400,'账号密码错误',[]);
        }
        //md5加密pwd
        $sign=CoreSystem::where('id',1)->value('sys_sign');
        $post['pwd']=md5($post['pwd'].$sign);

        $user=CoreUser::where($post)->withoutField(['id','ip','remark','pwd','create_time','update_time'])->find();
        if(empty($user)){
            return jsonResult(400,'账号密码错误',[]);
        }

        if($user['is_delete']==2){
            return jsonResult(400,'账号审核中',[]);
        }

        $data=[
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'login_time'=>date('Y-m-d H:i:s')
        ];

        $session_id='';
        if(empty($user['session_id'])){
            //生成session_id
            $session_id=Uuid::uuid1()->getHex()->toString();
            $data['session_id']=$session_id;
        }else{
            $session_id=$user['session_id'];
        }
        $user->save($data);

        //生成token
        $access_token = SunJwt::createJwt($session_id);
        $refresh_token = SunJwt::createJwt($session_id, 3600 * 24 * 30);

        $result=[
            'session_id'=>$session_id,
            'access_token'=>$access_token,
            'refresh_token'=>$refresh_token,
            'userinfo'=>$user
        ];

        //用户首次登录，设置cookie
        cookie('sunphp_user_session_id',$session_id,36000);

        return jsonResult(200,'操作成功',$result);
    }

    public function register(){

        $post=$this->request->post();

        //排除同名用户
        $u=CoreUser::where('name',$post['name'])->find();
        if(!empty($u)){
            return jsonResult(400,'用户名已存在！',[]);
        }
        if(empty($post['pwd'])){
            return jsonResult(400,'密码不能为空！',[]);
        }

        //md5加密pwd
        $system=CoreSystem::where('id',1)->field(['register','check','sys_sign','bind_phone'])->find();
        if(empty($system)){
            return jsonResult(400,'系统错误',[]);
        }
        if($system['register']!=1){
            return jsonResult(400,'禁止注册',[]);
        }

        if($system['bind_phone']==1){
            if(empty($post['phone'])){
                return jsonResult(400,'手机号不能为空',[]);
            }
        }

        $post['pwd']=md5($post['pwd'].$system['sys_sign']);

        $data=[
            'type'=>1,
            'name'=>$post['name'],
            'pwd'=>$post['pwd'],
            'is_delete'=>2
        ];

        if(!empty($post['phone'])){
            try{
                validate(ValidateUser::class)->check([
                    'phone'=>$post['phone']
                ]);
            }catch(ValidateException $e){
                // 验证失败 输出错误信息
                $err=$e->getError();
                return jsonResult(400,$err,[]);
            }
            $data['phone']=$post['phone'];
        }
        if($system['check']==0){
            $data['is_delete']=0;
        }

        CoreUser::create($data);
        return jsonResult(200,'注册成功',[]);
    }


    public function info(){
        $session_id=$this->request->post('session_id');
        //检查用户是否存在
        $user=CoreUser::where('session_id',$session_id)->where('is_delete',0)
        ->withoutField(['ip','remark','is_delete','pwd','create_time','update_time'])
        ->find();

        if(empty($user)){
            return jsonResult(402, "用户不存在", []);
        }
        $data=[
            'ip'=>$_SERVER['REMOTE_ADDR'],
            'login_time'=>date('Y-m-d H:i:s')
        ];
        $user->save($data);

        $create=CoreCreate::where('uid',$user['id'])
        ->withoutField(['id','uid','create_time','update_time'])
        ->find();

        $user['create_auth']=$create;

        //用户再次访问，续期cookie
        cookie('sunphp_user_session_id',$session_id,36000);
        return jsonResult(200,'操作成功',$user);

    }

    public function route(){
        $user=$this->request->middleware('user');
        switch($user['type']){
            case 1:
                $routes=['Account'];
                break;
            case 2:
                $routes=['Account','App','User','System'];
                $sys=CoreSystem::where('id',1)->field(['sys_type','sys_upgrade','sys_domain','sys_secret','sys_sign'])->find();
                if($sys['sys_upgrade']==1){
                    $routes[]='Version';
                }else{
                    //版权所有，侵权必究！
                    $secret=md5($sys->sys_domain.$sys->sys_type.$sys->sys_sign);
                    if($sys->sys_type!=2||$sys->sys_secret!=$secret){
                        $routes[]='Version';
                    }
                }
                break;
        }
        $data=[
            'menus'=>$routes
        ];
        return jsonResult(200,'操作成功',$data);

    }

    public function logout(){
        //清空session_id，所有端用户必须重新登陆
        $session_id=$this->request->post('session_id');
        $data=[
            'session_id'=>''
        ];
        CoreUser::where('session_id',$session_id)->update($data);

        return jsonResult(200,'操作成功',[]);
    }

    public function change(){
        $session_id=$this->request->post('session_id');
        $avatar=$this->request->post('avatar');
        $pwd=$this->request->post('pwd');

        $data=[];
        if(!empty($avatar)){
            $data['avatar']=$avatar;
        }
        if(!empty($pwd)){
            //md5加密pwd
            $sign=CoreSystem::where('id',1)->value('sys_sign');
            $data['pwd']=md5($pwd.$sign);
        }
        if(!empty($data)){
            CoreUser::where('session_id',$session_id)->update($data);
            return jsonResult(200,'操作成功',[]);
        }
        return jsonResult(400,'操作失败',[]);
    }

    public function auth(){
        $post=$this->request->post();
        $data=$post['create'];
        $data['uid']=$post['uid'];

        $s=CoreCreate::where('uid',$data['uid'])->find();
        if(empty($s)){
            CoreCreate::create($data);
        }else{
            $s->save($data);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function getAuth(){
        $post=$this->request->post();
        $create=CoreCreate::where('uid',$post['uid'])->withoutField(['id','uid','create_time','update_time'])->find();
        $data=[
            'create'=>$create
        ];
        return jsonResult(200,'操作成功',$data);
    }


    //用户可以使用的平台
    public function authList()
    {
        $post = $this->request->post();
        $size = $post['limit'];
        $start = ($post['page'] - 1) * $size;


        $con[] = [
            'is_delete', '=', 0
        ];
        if ($post['type'] > 0) {
            $con[] = ['type', '=', $post['type']];
        }
        if (!empty($post['title'])) {
            $con[] = ['name', 'like', '%' . $post['title'] . '%'];
        }

        $is_fetch=false;
        if (!empty($post['is_canuse'])) {
            $is_fetch=true;
            // 索引数组写法，必须带有''，才可以解析
            $cansue = CoreUseaccount::where('uid', $post['uid'])->column(['acid']);
            $con[] = ['id', 'in', $cansue];
        }

        $order = 'desc';
        if ($post['sort'] == '+id') {
            $order = 'asc';
        }


        $data = CoreAccount::where($con)->order('id', $order)->limit($start, $size)
        ->field('id,type,name,avatar')
        ->select();

        $total = CoreAccount::where($con)->count();


        $result = [
            'items' => $data,
            'total' => $total
        ];

        if (!empty($post['first']) && $post['first'] == 1) {
            if(!$is_fetch){
                $cansue = CoreUseaccount::where('uid', $post['uid'])->column(['acid']);
            }
            $result['useaccount']=$cansue;
        }

        return jsonResult(200, '操作成功', $result);
    }

    public function useaccount(){
        $post = $this->request->post();
        $cansue = CoreUseaccount::where('uid', $post['uid'])->column(['acid']);
        $del=array_diff($cansue,$post['useaccount']);
        if(!empty($del)){
            CoreUseaccount::where('uid',$post['uid'])->where('acid','in',$del)->delete();
        }
        $add=array_diff($post['useaccount'],$cansue);
        if(!empty($add)){
            foreach($add as $v){
                CoreUseaccount::create(['uid'=>$post['uid'],'acid'=>$v]);
            }
        }
        return jsonResult(200, '操作成功', []);

    }



}