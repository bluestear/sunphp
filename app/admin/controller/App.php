<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-03-20 13:45:51
 * @LastEditors: light
 * @LastEditTime: 2023-05-23 18:08:32
 * @Description: SonLight Tech版权所有
 */

declare(strict_types=1);

namespace app\admin\controller;

use AlibabaCloud\SDK\ICE\V20201109\Models\GetTranscodeJobResponseBody\transcodeParentJob\outputGroup\processConfig\imageWatermarks\overwriteParams\file;
use app\admin\model\CoreApp;
use app\admin\model\CoreMenu;
use app\admin\model\CoreSupports;
use app\admin\model\CoreSystem;
use app\admin\model\CoreUseapp;
use app\admin\model\CoreUser;
use Exception;
use sunphp\file\SunFile;
use sunphp\http\SunHttp;

class App extends Base
{

    protected $middleware=[\app\admin\middleware\AuthAdmin::class];


    public function list()
    {
        $post = $this->request->post();
        $size = $post['limit'];
        $start = ($post['page'] - 1) * $size;


        $num=0;
        if (!empty($post['first']) && $post['first'] == 1) {
            //所有未安装的应用
            try {
                $num_app=$this->scanApp('app');
                $num_addons=$this->scanApp('addons');
                $num=$num_app+$num_addons;

            } catch (Exception $e) {
                // dump($e);
            }
        }

        $con[] = [
            'is_delete', '=', $post['is_delete']
        ];
        if ($post['type'] > 0) {
            // 返回列数组
            $supports = CoreSupports::where('type', $post['type'])->column(['app_id']);
            $con[] = ['id', 'in', $supports];
        }
        if (!empty($post['title'])) {
            $con[] = ['name', 'like', '%' . $post['title'] . '%'];
        }

        if (!empty($post['is_upgrade'])) {
            // 索引数组写法，必须带有''，才可以解析
            $con[] = ['new_version','not null',''];
        }

        $order = 'desc';
        if ($post['sort'] == '+id') {
            $order = 'asc';
        }

        $data = CoreApp::where($con)->order('id', $order)->limit($start, $size)->withoutField(['is_delete', 'create_time', 'update_time'])->select();

        //支持类型
        foreach($data as &$val){
            $val['supports']=CoreSupports::where('app_id',$val['id'])->column('type');
        }
        $total = CoreApp::where($con)->count();
        $result = [
            'items' => $data,
            'total' => $total,
            'num'=>$num,
            'mall_url'=>'https://mall.sunphp.cn'
        ];
        return jsonResult(200, '操作成功', $result);
    }


    public function authList()
    {
        $post = $this->request->post();
        $size = $post['limit'];
        $start = ($post['page'] - 1) * $size;


        $con[] = [
            'is_delete', '=', 0
        ];
        if ($post['type'] > 0) {
            $con[] = ['b.type', '=', $post['type']];
        }
        if (!empty($post['title'])) {
            $con[] = ['name', 'like', '%' . $post['title'] . '%'];
        }

        $is_fetch=false;
        if (!empty($post['is_canuse'])) {
            $is_fetch=true;
            // 索引数组写法，必须带有''，才可以解析
            $cansue = CoreUseapp::where('uid', $post['uid'])->column(['sid']);
            $con[] = ['b.id', 'in', $cansue];
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
                $cansue = CoreUseapp::where('uid', $post['uid'])->column(['sid']);
            }
            $result['useapp']=$cansue;
        }

        return jsonResult(200, '操作成功', $result);
    }

    public function useapp(){
        $post = $this->request->post();
        $cansue = CoreUseapp::where('uid', $post['uid'])->column(['sid']);
        $del=array_diff($cansue,$post['useapp']);
        if(!empty($del)){
            CoreUseapp::where('uid',$post['uid'])->where('sid','in',$del)->delete();
        }
        $add=array_diff($post['useapp'],$cansue);
        if(!empty($add)){
            foreach($add as $v){
                CoreUseapp::create(['uid'=>$post['uid'],'sid'=>$v]);
            }
        }
        return jsonResult(200, '操作成功', []);

    }

    public function update()
    {
        $post = $this->request->only(['id', 'logo', 'name', 'description']);
        CoreApp::update($post);
        return jsonResult(200, '操作成功', []);
    }


    /* 首次安装下载 */
    public function download()
    {


        $disable_functions = ini_get('disable_functions');
        $disabled = explode(',', $disable_functions);
        //判断是否包含在被禁用的数组中
        if(in_array('exec', $disabled)){
            return jsonResult(400, '请先取消php禁用函数exec', []);
        }

        $post = $this->request->post();
        if(!preg_match("/git|sunphp/i",$post['url'])){
            return jsonResult(400, '下载地址不正确', []);
        }

        $gitee=$post['url'];


        // 判断是否 从商城下载
        if(preg_match("/^sunphp\-/i",$post['url'])){

            $is_win=false;
            if (stristr(php_uname(), "windows")){
                $is_win=true;
            }

            //形式如sunphp-12345678
            $sys=CoreSystem::where('id',1)->find();
            $info=SunHttp::post('https://mall.sunphp.cn/git.php',[
                'code'=>trim($post['url']),
                'is_win'=>$is_win,
                'domain'=>$this->request->host(),
                'record_name'=>$sys->record_name,
                'record_no'=>$sys->record_no
            ]);
            $info=json_decode($info,true);
            if($info['success']==1){
                $gitee=$info['git_url'];

                //判断服务器类型
                // $ssh_dir='/etc/ssh/ssh_config.d/';无权限访问
                $ssh_dir='/www/wwwroot/sunphp.git/';
                if ($is_win){
                    $ssh_dir='C:\\Users\\Administrator\\.ssh\\';
                }else{
                    if(!is_dir($ssh_dir)){
                        mkdir($ssh_dir,0777,true);
                    }
                }


                //配置可能存在的ssh信息
                if(!empty($info['ssh_conf_data'])){
                    if($is_win){
                        SunFile::write($ssh_dir.'config','a+',$info['ssh_conf_data']);
                    }else{
                        SunFile::write($ssh_dir.$info['ssh_conf'],'w+',$info['ssh_conf_data']);
                    }
                }
                if(!empty($info['ssh_key_data'])){
                    SunFile::write($ssh_dir.$info['ssh_key'],'w+',$info['ssh_key_data']);

                    //设置权限600
                    chmod($ssh_dir.$info['ssh_key'],0600);
                }

            }else{
                return jsonResult(400, $info['message'], $info);
            }
        }


        $path=root_path().'runtime/sunphp_module/';

        if(!is_dir($path)){
            mkdir($path,0777,true);
        }

        $cmd='cd '.$path;
        $cmd.=' && git clone '.$gitee;
        exec($cmd,$log,$status);
        $data['log']=$log;
        $data['execstr']=$cmd;
        $data['status']=$status;

        if($status==0){
            //分析应用app/addons类型，并且移动目录
            $this->moveModule();
            return jsonResult(200, '操作成功', $data);
        }else{
            //文件夹存在目录，必须清除
            SunFile::removeDirectory($path);
            return jsonResult(400, '下载应用失败！请重试！', $data);
        }
    }

    /* 获取远程的manifest.xml文件 */
    private function fetchXml($path){
        //有可能是git安装，有可能手动安装
        if(is_dir($path.'/.git')){
            try{
                $cmd='cd '.$path;
                $cmd.=' && git fetch origin ';
                $cmd.=' && git checkout origin/master manifest.xml ';
                exec($cmd,$log,$status);
                $exec_data['log']=$log;
                $exec_data['execstr']=$cmd;
                $exec_data['status']=$status;
            }catch(\Exception $e){
                // dump($e);
            }
        }
    }


    /* 用户点击更新，下载最新仓库，覆盖到本地，保留用户手动创建的文件 */
    private function pullOrigin($path){
        //有可能是git安装，有可能手动安装
        if(is_dir($path.'/.git')){
            try{
                $cmd='cd '.$path;
                $cmd.=' && git fetch --all ';
                $cmd.=' && git reset --hard origin/master ';
                $cmd.=' && git pull origin master ';
                exec($cmd,$log,$status);
                $exec_data['log']=$log;
                $exec_data['execstr']=$cmd;
                $exec_data['status']=$status;
            }catch(\Exception $e){
                // dump($e);
            }
        }
    }


    private  function moveModule()
    {
        //获取目录下所有模块
        $dir=root_path().'runtime/sunphp_module/';
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }

        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..'|| $file == 'admin') {
                continue;
            }
            $tmp_file = $dir . $file;

            if (is_dir($tmp_file)) {

                $app = CoreApp::where('identity', $file)->find();
                if(!empty($app)){
                    // 已经存在模块，删除当前目录和文件
                    SunFile::removeDirectory($tmp_file);
                    continue;
                }

                $manifest = $tmp_file . "/manifest.xml";

                if (file_exists($manifest)) {

                    $xml = file_get_contents($manifest);

                    $dom = new \DOMDocument();
                    $dom->loadXML($xml);
                    $m = $dom->getElementsByTagName('manifest')->item(0);

                    $application = $m->getElementsByTagName("application")->item(0);


                    if($application->getElementsByTagName("identity")->length>0){
                        //app模块
                        $new_dir=root_path().'app/'.$file;
                    }else{
                        //addons模块
                        $new_dir=root_path().'addons/'.$file;
                    }

                    SunFile::copyDirectory($tmp_file,$new_dir);
                    SunFile::removeDirectory($tmp_file);

                }
            }
        }

    }

    public function delete()
    {
        $post = $this->request->post();
        // CoreApp::destroy($post['ids']);
        // CoreApp::update(['is_delete'=>1],[['id','in',$post['ids']]]);
        if ($post['is_delete'] == 1) {
            //彻底卸载
            for ($i = 0; $i < count($post['ids']); $i++) {
                $module = CoreApp::where('id', $post['ids'][$i])->find();
                if (!empty($module)) {
                    $manifest = root_path() . $module['dir']."/". $module['identity'] . "/manifest_old.xml";
                    if (file_exists($manifest)) {
                        $xml = file_get_contents($manifest);
                        $dom = new \DOMDocument();
                        $dom->loadXML($xml);
                        $m = $dom->getElementsByTagName('manifest')->item(0);
                        $uninstall = $m->getElementsByTagName("uninstall")->item(0)->textContent;
                        $db_uninstall = root_path() . $module['dir']."/". $module['identity'] . "/" . $uninstall; //XML文件
                        if (!empty($uninstall) && file_exists($db_uninstall)) {
                            switch($module['dir']){
                                case 'app':
                                    // 数据库前缀sun
                                    include_once root_path() . 'extend/sunphp/function/db.php';
                                break;
                                case 'addons':
                                    // 数据库前缀名称不一致ims
                                    include_once root_path() . 'extend/sunphp/function/db_ims.php';
                                break;
                                default:
                                break;
                            }
                            require_once($db_uninstall);
                        }
                    }
                }
            }

            CoreApp::where('id', 'in', $post['ids'])->delete();
            CoreSupports::where('app_id', 'in', $post['ids'])->delete();
        } else if($post['is_delete']==0){
            $d = $post['is_delete'] + 1;
            CoreApp::where('id', 'in', $post['ids'])->update(['is_delete' => $d]);
        }else{

        }

        return jsonResult(200, '操作成功', []);
    }

    public function resume()
    {
        $post = $this->request->post();
        CoreApp::where('id', 'in', $post['ids'])->update(['is_delete' => 0]);
        return jsonResult(200, '操作成功', []);
    }

    private  function scanApp($arg='app')
    {
        //获取目录下所有模块
        $dir = root_path().$arg."/";

        $files = scandir($dir);
        $num=0;
        $file_list = [];



        foreach ($files as $file) {
            if ($file == '.' || $file == '..'|| $file == 'admin') {
                continue;
            }
            $tmp_file = $dir . $file;

            if (is_dir($tmp_file)) {

                // 远程获取git仓库最新的manifest.xml
                $this->fetchXml($tmp_file);


                $manifest = $tmp_file . "/manifest.xml";

                if (file_exists($manifest)) {

                    $xml = file_get_contents($manifest);

                    $dom = new \DOMDocument();
                    $dom->loadXML($xml);
                    $m = $dom->getElementsByTagName('manifest')->item(0);

                    $application = $m->getElementsByTagName("application")->item(0);


                    $data = [];

                    switch($arg){
                        case 'app':
                            $data['identity'] = $application->getElementsByTagName("identity")->item(0)->textContent;
                            $data['admin'] = $application->getElementsByTagName("admin")->item(0)->textContent;
                            $data['icon'] = $application->getElementsByTagName("icon")->item(0)->textContent;
                        break;
                        case 'addons':
                            // identifie注意区别
                            $data['identity'] = $application->getElementsByTagName("identifie")->item(0)->textContent;

                            if($application->getElementsByTagName("admin")->length>0){
                                $data['admin'] = $application->getElementsByTagName("admin")->item(0)->textContent;
                            }

                            $data['icon']='icon.jpg';
                            $data['type']=2;
                        break;
                        default:
                        break;
                    }

                    $data['name'] = $application->getElementsByTagName("name")->item(0)->textContent;
                    $data['version'] = $application->getElementsByTagName("version")->item(0)->textContent;


                    if (empty($data['identity']) || empty($data['name']) || empty($data['version'])) {
                        //manifest.xml配置文件错误
                        continue;
                    }


                    $data['description'] = $application->getElementsByTagName("description")->item(0)->textContent;
                    $data['author'] = $application->getElementsByTagName("author")->item(0)->textContent;


                    $data['dir']=$arg;





                    $file_list[] = $file;

                    $app = CoreApp::where('identity', $data['identity'])->where('dir',$arg)->find();

                    $app_id = 0;
                    if (empty($app)) {
                        //安装
                        $data['is_delete'] = 2;
                        $core_app = new CoreApp();
                        $core_app->save($data);
                        $app_id = $core_app->id;
                    } else {
                        //升级或重新扫描
                            if ($app->is_delete == 2) {
                                // 待安装，多次重复扫描
                                $app->save($data);
                                $app_id = $app->id;
                            } else {
                                 //已安装，比较版本
                                 $v_res=false;
                                 $new_v=explode('.',$data['version']) ;
                                 $now_v=explode('.',$app->version);
                                 for($i=0;$i<count($new_v);$i++){
                                    if(strcmp($new_v[$i],$now_v[$i])>0){
                                        $v_res=true;
                                    }
                                 }
                                 if($v_res){
                                    $upgrade = ['new_version' => $data['version']];
                                    $app->save($upgrade);
                                 }

                            }
                    }


                    if ($app_id > 0) {
                        $platform = $m->getElementsByTagName("platform")->item(0);
                        $s = $platform->getElementsByTagName("supports")->item(0);

                        $supports = [];
                        if (!empty($s)) {
                            $type = $s->getElementsByTagName('item');
                            for ($i = 0; $i < $type->length; $i++) {
                                $t = $type->item($i)->getAttribute('type');
                                if (!empty($t)) {
                                    if(is_numeric($t)){
                                        $supports[] = $t;
                                    }else{
                                        $s_array=[
                                            'app'=>1,
                                            'wxapp'=>2,
                                            'toutiaoapp'=>3,
                                            'webapp'=>4,
                                            'system_welcome'=>4,//首页
                                            'plugin'=>4,//插件
                                            'android'=>5,
                                            'ios'=>5,
                                            'aliapp'=>6,
                                            'baiduapp'=>7
                                        ];
                                        //防止其他的分支类型报错，如：branch
                                        if(array_key_exists($t,$s_array)){
                                            $supports[] = $s_array[$t];
                                        }

                                    }
                                }
                            }
                        }

                        //记录支持类型
                        if (!empty($supports)) {
                            //删除所有过期类型
                            $del_con[] = ['app_id', '=', $app_id];
                            $del_con[] = ['type', 'not in', $supports];
                            CoreSupports::where($del_con)->delete();

                            for ($i = 0; $i < count($supports); $i++) {
                                $sup_con = ['app_id' => $app_id, 'type' => $supports[$i]];
                                $s = CoreSupports::where($sup_con)->find();
                                if (empty($s)) {
                                    CoreSupports::create($sup_con);
                                }
                            }
                        }

                        $num++;
                    }
                }
            }
        }

        if (!empty($file_list)) {
            //清除失效的未安装记录
            $con[] = ['dir', '=', $arg];
            $con[] = ['is_delete', '=', 2];
            $con[] = ['identity', 'not in', $file_list];
            CoreApp::where($con)->delete();
        }else{
            //没有待安装列表，也要删除失效记录
            $con[] = ['dir', '=', $arg];
            $con[] = ['is_delete', '=', 2];
            CoreApp::where($con)->delete();
        }
        return $num;
    }

    public function install()
    {
        $post = $this->request->post();
        $app = CoreApp::where('id', $post['id'])->find();
        if (empty($app)) {
            return jsonResult(400, '应用不存在', []);
        }

        if ($post['type'] == 2) {
            //升级应用，尝试获取git远程仓库版本
            $this->pullOrigin(root_path().$app['dir']."/" . $app->identity);
        }

        $filename = root_path().$app['dir']."/" . $app->identity . "/manifest.xml"; //XML文件
        $manifest_old = root_path().$app['dir']."/"  . $app->identity . "/manifest_old.xml"; //XML文件

        if (!file_exists($filename)) {
            CoreApp::where('id', $post['id'])->delete();
            return jsonResult(400, 'manifest.xml文件不存在', []);
        }
        $xml = file_get_contents($filename);
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $m = $dom->getElementsByTagName('manifest')->item(0);

        $application = $m->getElementsByTagName("application")->item(0);


        $data = [];

        switch($app['dir']){
            case 'app':
                $data['identity'] = $application->getElementsByTagName("identity")->item(0)->textContent;
                $data['admin'] = $application->getElementsByTagName("admin")->item(0)->textContent;
                $data['icon'] = $application->getElementsByTagName("icon")->item(0)->textContent;
            break;
            case 'addons':
                // identifie注意区别
                $data['identity'] = $application->getElementsByTagName("identifie")->item(0)->textContent;

                if($application->getElementsByTagName("admin")->length>0){
                    $data['admin'] = $application->getElementsByTagName("admin")->item(0)->textContent;
                }

                $data['icon']='icon.jpg';
                $data['type']=2;
            break;
            default:
            break;
        }

        $data['name'] = $application->getElementsByTagName("name")->item(0)->textContent;
        $data['version'] = $application->getElementsByTagName("version")->item(0)->textContent;

        if (empty($data['identity']) || empty($data['name']) || empty($data['version'])) {
            return jsonResult(400, 'manifest.xml配置文件错误', []);
        }
        $data['description'] = $application->getElementsByTagName("description")->item(0)->textContent;
        $data['author'] = $application->getElementsByTagName("author")->item(0)->textContent;


        $platform = $m->getElementsByTagName("platform")->item(0);
        $s = $platform->getElementsByTagName("supports")->item(0);

        if (!empty($s)) {
            $type = $s->getElementsByTagName('item');
            for ($i = 0; $i < $type->length; $i++) {
                $t = $type->item($i)->getAttribute('type');
                if (!empty($t)) {

                    if(is_numeric($t)){
                        $supports[] = $t;
                    }else{
                        $s_array=[
                            'app'=>1,
                            'wxapp'=>2,
                            'toutiaoapp'=>3,
                            'webapp'=>4,
                            'system_welcome'=>4,//首页
                            'plugin'=>4,//插件
                            'android'=>5,
                            'ios'=>5,
                            'aliapp'=>6,
                            'baiduapp'=>7
                        ];
                        //防止其他的分支类型报错，如：branch
                        if(array_key_exists($t,$s_array)){
                            $supports[] = $s_array[$t];
                        }

                    }

                }
            }
        }

        $install = $m->getElementsByTagName("install")->item(0)->textContent;
        $upgrade = $m->getElementsByTagName("upgrade")->item(0)->textContent;

        if (!empty($supports)) {
            //删除所有过期类型
            $del_con[] = ['app_id', '=', $post['id']];
            $del_con[] = ['type', 'not in', $supports];
            CoreSupports::where($del_con)->delete();
            for ($i = 0; $i < count($supports); $i++) {
                $con = ['app_id' => $post['id'], 'type' => $supports[$i]];
                $s = CoreSupports::where($con)->find();
                if (empty($s)) {
                    CoreSupports::create($con);
                }
            }
        }


        //安装或更新menu
        $bindings = $m->getElementsByTagName("bindings")->item(0);
        $menu = $bindings->getElementsByTagName("menu")->item(0);
        $menu_list=[];
        if (!empty($menu)) {
            $item = $menu->getElementsByTagName('entry');
            for ($i = 0; $i < $item->length; $i++) {
                $title = $item->item($i)->getAttribute('title');

                switch($app['dir']){
                    case 'app':

                        $url = $item->item($i)->getAttribute('url');
                        if (!empty($title)&&!empty($url)) {
                            $menu_list[] = ['title'=>$title,'url'=>$url];
                        }

                    break;
                    case 'addons':

                        $do = $item->item($i)->getAttribute('do');
                        $state = $item->item($i)->getAttribute('state');
                        if (!empty($title)&&!empty($do)) {
                            $menu_list[] = ['title'=>$title,'do'=>$do,'state'=>$state];
                        }

                    break;
                    default:
                    break;
                }



            }
        }

        if (!empty($menu_list)) {
            $menu_id=[];
            for ($i = 0; $i < count($menu_list); $i++) {
                $item_now=$menu_list[$i];

                switch($app['dir']){
                    case 'app':

                        $con = [
                            'app_id' => $post['id'],
                            'title' =>$item_now['title'],
                            'url' =>$item_now['url']
                        ];

                    break;
                    case 'addons':

                        $con = [
                            'app_id' => $post['id'],
                            'title' =>$item_now['title'],
                            'do' =>$item_now['do'],
                            'state' =>$item_now['state']
                        ];

                    break;
                    default:
                    break;
                }


                $m_now = CoreMenu::where($con)->find();
                if (empty($m_now)) {
                    $new=CoreMenu::create($con);
                    $menu_id[]=$new->id;
                }else{
                    $menu_id[]=$m_now->id;
                }
            }
            if(!empty($menu_id)){
                //删除失效的id
                CoreMenu::where('id','not in',$menu_id)->where('app_id',$post['id'])->delete();
            }
        }


         //安装或更新cover
        $bindings = $m->getElementsByTagName("bindings")->item(0);
         $cover = $bindings->getElementsByTagName("cover")->item(0);
         $cover_list=[];
         if (!empty($cover)) {
             $item = $cover->getElementsByTagName('entry');
             for ($i = 0; $i < $item->length; $i++) {
                 $title = $item->item($i)->getAttribute('title');

                 switch($app['dir']){
                    case 'app':

                        $url = $item->item($i)->getAttribute('url');
                        if (!empty($title)&&!empty($url)) {
                            $cover_list[] = ['title'=>$title,'url'=>$url];
                        }

                    break;
                    case 'addons':

                        $do = $item->item($i)->getAttribute('do');
                        $state = $item->item($i)->getAttribute('state');
                        if (!empty($title)&&!empty($do)) {
                            $cover_list[] = ['title'=>$title,'do'=>$do,'state'=>$state];
                        }

                    break;
                    default:
                    break;
                 }

             }
         }
         $data['cover']=$cover_list;


         switch($app['dir']){
            case 'app':
                // 数据库前缀sun
                include_once root_path() . 'extend/sunphp/function/db.php';
            break;
            case 'addons':
                // 数据库前缀名称不一致ims
                include_once root_path() . 'extend/sunphp/function/db_ims.php';
            break;
            default:
            break;
        }


        if ($post['type'] == 1) {
            //安装
            $data['is_delete'] = 0;
            //再次更新数据
            $app->save($data);

            $db_install = root_path().$app['dir']."/" . $app->identity . "/" . $install; //XML文件
            if (!empty($install) && file_exists($db_install)) {
                require_once($db_install);
            }
        } else {
            //再次更新数据
            $data['new_version'] = null;
            $app->save($data);
            //升级
            $db_upgrade = root_path().$app['dir']."/" . $app->identity . "/" . $upgrade; //XML文件
            if (!empty($upgrade) && file_exists($db_upgrade)) {
                require_once($db_upgrade);
            }
        }

        //修改manifest文件名称
        rename($filename, $manifest_old);

        return jsonResult(200, '操作成功', []);
    }
}
