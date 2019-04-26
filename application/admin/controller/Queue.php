<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/11
 * Time: 15:07
 * Description: 队列管理
 */


namespace app\admin\controller;

use SucaiZ;

class Queue
{
    private $redis;
    private $crontab_key = 'crontab_status';
    public function __construct()
    {
        $this->redis = getRedis();
    }

    //接收触发处理信息的请求
    public function executeget(){

        $queue_name = input('name');
        task($queue_name);
    }

    //接收定时访问请求
    public function crontab(){
        $status = $this->redis->get($this->crontab_key);
        if(empty($status)){
            $this->redis->set($this->crontab_key ,1 ,120);
            $this->exeCrontab();
        }
        echo 1;
    }


    public function exeCrontab(){
        $plan = model('Plan');
        do{
            //获取当前时间戳
            $year = date('Y');
            $mouth = date('m');
            $day = date('d');
            $hour = date('H');
            $minute = date('i');
            $week = date('w');
            if($week == 0){
                $week = 7;
            }
            $plan_list = [];
            //获取固定时间任务
            $where = [
                'condition'=>1,
            ];
            for($i = 1;$i<7;$i++){
                if($i == 1){
                    $where['minute'] = $minute;
                }else if($i == 2){
                    $where['hour'] = $hour;
                    $where['minute'] = $minute;
                }else if($i == 3){
                    $where['week'] = $week;
                    $where['hour'] = $hour;
                    $where['minute'] = $minute;
                }else if($i == 4){
                    $where['day'] = $day;
                    $where['hour'] = $hour;
                    $where['minute'] = $minute;
                }else if($i == 5){
                    $where['month'] = $mouth;
                    $where['day'] = $day;
                    $where['hour'] = $hour;
                    $where['minute'] = $minute;
                }else if($i == 6){
                    $where['execute_type'] = 1;
                    $where['year'] = $year;
                    $where['month'] = $mouth;
                    $where['day'] = $day;
                    $where['hour'] = $hour;
                    $where['minute'] = $minute;
                }
                $where['execute_type'] = $i;
                $arr = $plan->getPlanList($where ,' id,fun_name ');
                foreach ($arr as $key=> $value){
                    $plan_list[] = $value;
                }
                unset($arr);
                unset($where);
            }
            foreach ($plan_list as $key => $value){
                $d = [
                    'function' => $value['fun_name'],
                ];
                task('crond' ,$d);
                //更新任务执行状态
                $b['status'] = 2;
                $b['end_time'] = time();
                $a['id'] = $value['id'];
                $plan->updatePlan($a ,$b);
                $plan->updatePlanNum($a);
            }
            sleep(60);
        }while(true);
    }
}