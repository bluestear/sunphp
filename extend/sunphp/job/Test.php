<?php


declare(strict_types=1);

namespace sunphp\job;

use think\facade\Db;
use think\queue\Job;

defined('SUN_IN') or exit('Sunphp Access Denied');

/* think-queue的任务队列示例 */
class Test{
    public function fire(Job $job,$data){
        /* 任务入口 */
        $is_done=$this->doTask($data);
        if($is_done){
            /* 执行成功，删除当前任务*/
            $job->delete();
        }else{
            /* 执行失败，重新发布任务，或者尝试3次后删除任务*/
            if($job->attempts()>3){
                $job->delete();
            }else{
                /* 执行失败，延迟2秒后重新发布任务 */
                $job->release(2);
            }
        }
    }

    public function doTask($data){
        /* 任务处理逻辑 */

        // sleep(3);
        // 默认sync是同步执行
        // 使用redis后，就是异步执行

        // 执行业务逻辑……
        $result=1;


        if($result>0){
            return true;
        }else{
            return false;
        }

    }

    public function failed($data){
        /* 任务发布失败的执行逻辑 */

    }


    public function test(){

        /* 队列的发布和启动方法*/
        // $res=Queue::push('\sunphp\job\Test',['wechat'=>date('H:i:s')],'sunphp_job');
        // return json(['queue'=>$res]);

        // 启动队列——只要有新的队列就执行
        // php think queue:work
        // 启动队列
        // php think queue:listen


        // 启动指定队列
        // php think queue:work --queue sunphp_job
        // 启动队列
        // php think queue:listen --queue sunphp_job


        // linux启动队列，常驻内存
        // php think queue:work --daemon --queue sunphp_job
        // php think queue:listen --daemon --queue sunphp_job



    }


}
