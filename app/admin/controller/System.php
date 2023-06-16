<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-13 15:13:30
 * @LastEditors: light
 * @LastEditTime: 2023-06-05 08:56:23
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\CoreStorage;
use app\admin\model\CoreSystem;
use app\admin\model\CoreUser;
use think\facade\Cache;
use Ramsey\Uuid\Uuid;
use sunphp\file\SunEnv;
use sunphp\http\SunHttp;
use app\admin\validate\ValidateCommon;
use think\exception\ValidateException;


include_once root_path() . 'extend/sunphp/function/db.php';


class System extends Base{

    protected $middleware=[
        \app\admin\middleware\AuthAdmin::class=>['except'=>['global','install','clearCache']]
    ];

    //获取全局配置
    public function global(){
        //未安装拦截
        $install_file=root_path().'install.php';
        if(file_exists($install_file)){
            return jsonResult(200,'操作成功',['lock'=>1]);
        }

        $s=CoreStorage::where('acid',0)->field(['type','ali_oss','tencent_cos','qiniu'])->find();
        $data=[];

        $data['web_url']=$this->request->domain();

        if(empty($s)){
            $type=1;
        }else{
            $type=$s->type;
        }
        switch($type){
            case 1:
                $data['attach_url']=$data['web_url']."/attachment/";
                break;
            case 2:
                $oss=$s->ali_oss;
                $data['attach_url']=$oss['url'].'/';
                break;
            case 3:
                $oss=$s->tencent_cos;
                $data['attach_url']=$oss['url'].'/';
                break;
            case 4:
                $oss=$s->qiniu;
                $data['attach_url']=$oss['url'].'/';
                break;
        }

        $sys=CoreSystem::where('id',1)->field(['sys_type','sys_mall','sys_upgrade','sys_domain','sys_secret','sys_sign','bind_phone','register','record_name','record_no','sys_name','sys_logo'])->find();
        $data['record_name']=$sys->record_name;
        $data['record_no']=$sys->record_no;
        $data['sys_name']=$sys->sys_name;
        $data['sys_logo']=$sys->sys_logo;
        $data['bind_phone']=$sys->bind_phone;
        $data['register']=$sys->register;
        $data['sys_type']=$sys->sys_type;
        $data['sys_mall']=$sys->sys_mall;
        $data['sys_upgrade']=$sys->sys_upgrade;

        if($sys->sys_type==2){
            //版权所有，侵权必究！
            $secret=md5($sys->sys_domain.$sys->sys_type.$sys->sys_sign);
            if($sys->sys_secret!=$secret){
                $data['sys_type']=1;
            }
        }
        return jsonResult(200,'操作成功',$data);
    }
    public function get(){
        $s=CoreSystem::where('id',1)->withoutField(['id','sys_version','sys_secret','sys_sign','create_time','update_time'])->find();
        return jsonResult(200,'操作成功',$s);
    }
    public function update(){
        $post=$this->request->post();
        $s=CoreSystem::where('id',1)->find();
        if(empty($s)){
            CoreSystem::create($post,['sys_name','sys_logo','register','bind_phone','check','record_no','record_name']);
        }else{
            $s->save($post);
        }
        return jsonResult(200,'操作成功',[]);
    }

    public function clearCache(){
        Cache::clear();
        return jsonResult(200,'操作成功',[]);
    }

    public function version(){

        $path=root_path();
        if(is_dir($path.'.git')){
            try{
                $cmd='cd '.$path;
                $cmd.=' && git fetch origin ';
                $cmd.=' && git checkout origin/master app/admin/version.php ';
                exec($cmd,$log,$status);
                $exec_data['log']=$log;
                $exec_data['execstr']=$cmd;
                $exec_data['status']=$status;
            }catch(\Exception $e){
                // dump($e);
            }
        }

		$v=require(app_path() . 'version.php');
        $system=CoreSystem::where('id',1)->field(['sys_version','activate_key','sys_secret'])->find();
        $data=[
            'php'=>PHP_VERSION,
            'linux'=>php_uname('s').php_uname('r'),
            'server'=>$_SERVER['SERVER_SOFTWARE'],
            'new_version'=>$v['version'],
            'version'=>$system['sys_version'],
            'activate_key'=>$system['activate_key'],
            'sys_secret'=>$system['sys_secret']
        ];
        return jsonResult(200,'操作成功',$data);
    }

    public function upgrade(){

        $disable_functions = ini_get('disable_functions');
        $disabled = explode(',', $disable_functions);
        //判断是否包含在被禁用的数组中
        if(in_array('exec', $disabled)){
            return jsonResult(400,'请先取消php禁用函数exec',$disabled);
        }

        $path=root_path();
        if(!is_dir($path.'.git')){
            return jsonResult(400,'根目录下.git文件夹不存在',[]);
        }


        $cmd='cd '.root_path();
        $cmd.=' && git fetch --all ';
        $cmd.=' && git reset --hard origin/master ';
        $cmd.=' && git pull origin master ';
        exec($cmd,$log,$status);
        $data['log']=$log;
        $data['execstr']=$cmd;
        $data['status']=$status;

        //执行sql升级语句
        $upgrade_file=app_path() . 'db_upgrade.php';
        if(file_exists($upgrade_file)){
            require_once($upgrade_file);
        }

        //更新版本记录
        $v=require(app_path() . 'version.php');
        $version_data['sys_version']=$v['version'];
        CoreSystem::where('id',1)->update($version_data);

        return jsonResult(200,'操作成功',$data);
    }

    public function install(){
        $post=$this->request->post();

        $install_file=root_path().'install.php';
        if(!file_exists($install_file)){
            return jsonResult(200,'操作成功',['lock'=>1]);
        }

        // 将.env文件复制到根目录
        $env_file=root_path().'.env';
        $env_data=root_path().'data/.env';
        if(!file_exists($env_file)&&file_exists($env_data)){
            copy($env_data,$env_file);
        }

        if($post['type']=='check'){
            $v=require(app_path() . 'version.php');
            $data=[
                'php'=>PHP_VERSION,
                'linux'=>php_uname('s').php_uname('r'),
                'server'=>$_SERVER['SERVER_SOFTWARE'],
                'version'=>$v['version']
            ];
            if(file_exists(root_path() . 'sunphp.sql')){
                $data['sql']='sunphp.sql';
            }else{
                $data['sql']=false;
            }
            return jsonResult(200,'操作成功',$data);
        }else if($post['type']=='mysql'){
            SunEnv::set('HOSTNAME',$post['hostname']);
            SunEnv::set('DATABASE',$post['database']);
            SunEnv::set('USERNAME',$post['username']);
            SunEnv::set('PASSWORD',$post['password']);
            return jsonResult(200,'操作成功',[]);
        }else if($post['type']=='install'){
            $sql = file_get_contents(root_path() . 'sunphp.sql');
            $res=pdo_run($sql);
            if($res==0){
                //写入数据库成功，初始化系统
        		$v=require(app_path() . 'version.php');
                $data['sys_version']=$v['version'];
                $data['sys_type']=1;
                $data['id']=1;
                $data['sys_name']='sunphp';
                // $data['sys_logo']='logo.png';
                $data['sys_domain']=$this->request->domain();
                $data['sys_sign']=Uuid::uuid1()->getHex()->toString();
                CoreSystem::create($data);

                //管理员
                $user['pwd']=md5($post['pwd'].$data['sys_sign']);
                $user['name']=$post['name'];
                $user['type']=2;
                $user['is_delete']=0;
                CoreUser::create($user);

                //附件设置
                $storage=[
                    'id'=>1,
                    'acid'=>0,
                    'suffix'=>"png\njpg\njpeg\ngif\nmp4\nmp3\nmov\npem\ncrt\n",
                    'img_size'=>5000,
                    'video_size'=>5000,
                    'file_size'=>5000
                ];
                CoreStorage::create($storage);


                //标记已安装
                $install_file=root_path().'install.php';
                $install_lock=root_path().'install.lock';
                rename($install_file, $install_lock);
                return jsonResult(200,'操作成功',[]);
            }
            return jsonResult(400,'操作失败',[]);
        }
    }

    public function activate(){
        //激活高级商用版
        $post=$this->request->post();
        $sys=CoreSystem::where('id',1)->field(['sys_name','record_name','record_no','sys_type','sys_upgrade','sys_domain','sys_secret','sys_sign'])->find();
        //版权所有，侵权必究！
        $secret=md5($sys->sys_domain.$sys->sys_type.$sys->sys_sign);
        if($sys->sys_type==2&&$sys->sys_secret==$secret){
            CoreSystem::where('id',1)->update([
                'sys_upgrade'=>$post['sys_upgrade'],
                'sys_mall'=>$post['sys_mall']
            ]);
            return jsonResult(200,'操作成功',[]);
        }else{
            $url=$post['url'];
            if(empty($url)){
                return jsonResult(400,'激活秘钥错误',[]);
            }

            // try{
            //     validate(ValidateCommon::class)->check([
            //         'url'=>$post['url']
            //     ]);
            // }catch(ValidateException $e){
            //     // 验证失败 输出错误信息
            //     $err=$e->getError();
            //     return jsonResult(400,$err,[]);
            // }

            //版权所有，侵权必究！
            $activate_url='https://mall.sunphp.cn/activate.php?key='.$url;
            $res=SunHttp::post($activate_url,$sys->toArray());
            $result=json_decode($res,true);

            if($result['success']==1){
                $new_secret=md5($sys->sys_domain.'2'.$sys->sys_sign);
                CoreSystem::where('id',1)->update([
                    'sys_upgrade'=>$post['sys_upgrade'],
                    'sys_mall'=>$post['sys_mall'],
                    'sys_type'=>2,
                    'activate_key'=>$url,//保存激活秘钥，做正版校验
                    'sys_secret'=>$new_secret
                ]);
                return jsonResult(200,'操作成功',[]);
            }else{
                return jsonResult(400,'激活秘钥失效',[]);
            }
        }

    }

}