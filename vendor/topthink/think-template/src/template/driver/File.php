<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-09 16:48:31
 * @LastEditors: light
 * @LastEditTime: 2023-05-24 09:51:25
 * @Description: SonLight Tech版权所有
 */
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\template\driver;

use Exception;

class File
{
    protected $cacheFile;

    /**
     * 写入编译缓存
     * @access public
     * @param  string $cacheFile 缓存的文件名
     * @param  string $content 缓存的内容
     * @return void
     */
    public function write(string $cacheFile, string $content): void
    {
        // 检测模板目录
        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 生成模板缓存文件
        if (false === file_put_contents($cacheFile, $content)) {
            throw new Exception('cache write error:' . $cacheFile, 11602);
        }
    }

    // sonlight自定义解析方法
    public function createMobileUrl($do,$arg=[]){
        global $_W,$_GPC;
        $module=$_W['current_module']['name'];

        if(empty($arg['a'])){
            $a='site';
        }else{
            $a=$arg['a'];
        }
        $str='index.php?i='.$_GPC['i'].'&c=entry&a='.$a.'&m='.$module.'&do='.$do;

        // 其他自定义参数
        foreach($arg as $k=>$v){
            if($k!='a'){
                $str.='&'.$k.'='.$v;
            }
        }
        return $str;
    }

    public function createWebUrl($do,$arg=[])
    {
        global $_W,$_GPC;
        $module=$_W['current_module']['name'];
        $str='index.php?i='.$_GPC['i'].'&c=site&a=entry&m='.$module.'&do='.$do;
         // 其他自定义参数
         foreach($arg as $k=>$v){
            if($k!='a'){
                $str.='&'.$k.'='.$v;
            }
        }
        return $str;
    }


    /**
     * 读取编译编译
     * @access public
     * @param  string  $cacheFile 缓存的文件名
     * @param  array   $vars 变量数组
     * @return void
     */
    public function read(string $cacheFile, array $vars = []): void
    {
        $this->cacheFile = $cacheFile;

        if (!empty($vars) && is_array($vars)) {
            // 模板阵列变量分解成为独立变量
            extract($vars, EXTR_OVERWRITE);
        }

        //载入模版缓存文件
        include $this->cacheFile;
    }

    /**
     * 检查编译缓存是否有效
     * @access public
     * @param  string  $cacheFile 缓存的文件名
     * @param  int     $cacheTime 缓存时间
     * @return bool
     */
    public function check(string $cacheFile, int $cacheTime): bool
    {
        // 缓存文件不存在, 直接返回false
        if (!file_exists($cacheFile)) {
            return false;
        }

        if (0 != $cacheTime && time() > filemtime($cacheFile) + $cacheTime) {
            // 缓存是否在有效期
            return false;
        }

        return true;
    }
}
