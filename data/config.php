<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-05-25 09:27:33
 * @LastEditors: light
 * @LastEditTime: 2023-06-02 10:19:47
 * @Description: SonLight Tech版权所有
 */


 defined('IN_IA') or exit('Access Denied');
 defined('SUN_IN') or define('SUN_IN', true);

    /**
     * 获取环境变量值
     * @access public
     * @param string $name    环境变量名（支持二级 .号分割）
     * @param string $default 默认值
     * @return mixed
     */
if(!function_exists('env')){
    function env(string $name = null, $default = null)
    {
        $env_file=__DIR__."/../.env";
        $file_array = parse_ini_file($env_file, true, INI_SCANNER_RAW) ?: [];

        $MY_ENV=[];

        if(isset($file_array['DATABASE'])){
            foreach($file_array['DATABASE'] as $key=>$val){
                $MY_ENV["DATABASE_".$key]=$val;
            }
        }

        if (is_null($name)) {
            return $MY_ENV;
        }
        $name = strtoupper(str_replace('.', '_', $name));
        if (isset($MY_ENV[$name])) {
            $result = $MY_ENV[$name];
            $convert = [
                'true'  => true,
                'false' => false,
                'off'   => false,
                'on'    => true,
            ];
            if (is_string($result) && isset($convert[$result])) {
                return $convert[$result];
            }
            return $result;
        }

        return $default;

    }

}


$config_db=include_once(__DIR__."/../config/database.php");



$config['db']['master']['host']=$config_db['connections']['mysql']['hostname'];
$config['db']['master']['username']=$config_db['connections']['mysql']['username'];
$config['db']['master']['password']=$config_db['connections']['mysql']['password'];
$config['db']['master']['database']=$config_db['connections']['mysql']['database'];
$config['db']['master']['port']=$config_db['connections']['mysql']['hostport'];
$config['db']['master']['charset'] = $config_db['connections']['mysql']['charset'];
$config['db']['master']['tablepre'] = 'ims_';
