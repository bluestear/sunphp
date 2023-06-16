<?php
/*
 * @Author: SonLight Tech
 * @Date: 2023-02-24 14:52:38
 * @LastEditors: light
 * @LastEditTime: 2023-05-31 08:17:45
 * @Description: SonLight Tech版权所有
 */
declare(strict_types=1);
defined('SUN_IN') or exit('Sunphp Access Denied');

use think\facade\Db;

function tableprefix(){
    $config=config();
    $prefix=$config['database']['connections']['mysql']['prefix'];
    return $prefix;
}

function tablename($table){
    $prefix=tableprefix();
    return ' '.$prefix.$table.' ';
}

// 调试语句
function pdo_debug(){
    return Db::getLastSql();
}

function pdo_begin(){
    // 启动事务
    Db::startTrans();
}

function pdo_startTrans(){
    // 启动事务
    Db::startTrans();
}

function pdo_commit(){
    // 提交事务
    Db::commit();
}

function pdo_rollback(){
    // 回滚事务
    Db::rollback();
}


//一行记录
function pdo_get($table,$con=[],$fields=[]){
    //thinkphp6的name方法自动加前缀
    return Db::name($table)->where($con)->field($fields)->find();
}

//一行的某个字段值
function pdo_getvalue($table,$con=[],$value){

    return Db::name($table)->where($con)->value($value);
}

//所有行
function pdo_getall($table,$con=[],$fields=[]){

    return Db::name($table)->where($con)->field($fields)->select();
}

//返回添加成功的条数，通常情况返回 1
function pdo_insert($table,$data){

    return Db::name($table)->insert($data);
}

//插入并返回id
function pdo_insertid($table,$data){

    return Db::name($table)->insertGetId($data);
}

//批量插入数据
function pdo_insertall($table,$data){

    return Db::name($table)->insertAll($data);
}

//返回影响数据的条数
function pdo_update($table,$data,$con=[]){

    return Db::name($table)->where($con)->update($data);
}

function pdo_delete($table,$con=[]){

    return Db::name($table)->where($con)->delete();
}

//返回第一行
//如果可能有多条数据，必须在sql中使用limit 1来限制数量
function pdo_fetch($sql,$params=[]){
    $res= Db::query($sql,$params);
    //如果为空[]，current返回false
    return current($res);
}

//返回一个值，比如count(1)，第一行第一列的值
function pdo_fetchcolumn($sql,$params=[]){
   $res=Db::query($sql,$params);
    //这时候返回的是数组
    //如果是空[]
    if(empty($res)){
        return false;
    }
    return current($res[0]);
}

//返回所有行
function pdo_fetchall($sql,$params=[]){
    return Db::query($sql,$params);
}

//增删改
function pdo_query($sql,$params=[]){
    return Db::execute($sql,$params);
}

//安装更新数据库
function pdo_run($sql,$params=[]){
    return Db::execute($sql,$params);
}

//切换连接数据库后执行sql
function pdo_connect_run($sql,$params=[],$database='mysql'){
    return Db::connect($database)->execute($sql,$params);
}

function pdo_tableexists($table){
    $prefix=tableprefix();
    if (substr($table,0,strlen($prefix))!=$prefix) {
        $table= $prefix.$table;
    }
    $res = Db::query('SHOW TABLES LIKE '."'".$table."'");
    if($res){
        return true;
    }else{
        return false;
    }
}

function pdo_fieldexists($table,$column){
    $prefix=tableprefix();
    if (substr($table,0,strlen($prefix))!=$prefix) {
        $table= $prefix.$table;
    }
    $res = Db::query('select count(*) from information_schema.columns where table_name = '."'".$table."' ". 'and column_name ='."'".$column."'");
    if($res[0]['count(*)'] != 0){
        return true;
    }else{
        return false;
    }
}

