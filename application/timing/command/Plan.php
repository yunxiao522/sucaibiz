<?php


namespace app\timing\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;

class Plan extends Command
{
    protected $plan_model;
    protected $execute_type = ['1' => '每小时', '2' => '每天', '3' => '每周', '4' => '每月', '5' => '每年', '6' => '固定时间'];

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->plan_model = new \app\timing\model\Plan();
    }

    protected function configure()
    {
        $this->setName('plan')->setDescription('Here is the plan ');
    }

    protected function execute(Input $input, Output $output)
    {
        while (true) {
            $plan_list = $this->getPlanList();
            sleep(60);
            foreach ($plan_list as $key => $value) {
                task($value['fun_name'],"Here last plan");
                //更新任务执行状态
                $b['status'] = 2;
                $b['end_time'] = time();
                $a['id'] = $value['id'];
                //修改计划状态及最后执行时间
                $this->plan_model->edit(['id'=>$value['id']],[
                    'status'=>2,
                    'end_time'=>time(),
                ]);
                //修改计划执行次数
                $this->plan_model->fieldinc(['id'=>$value['id']],'num');
            }
        }
    }

    /**
     * @return array
     * Description 获取记录列表
     */
    private function getPlanList()
    {
        //获取当前年份
        $year = date('Y');
        //获取当前月份
        $mouth = date('m');
        //获取当前日期
        $day = date('d');
        //获取当前小时
        $hour = date('H');
        //获取当前分钟
        $minute = date('i');
        //获取当前星期
        $week = date('w') == 0 ? 7 : date('w');
        $plan_list = [];
        //获取计划列表
        for ($i = 1; $i < 7; $i++) {
            if ($i == 1) {
                $where['minute'] = $minute;
            } else if ($i == 2) {
                $where['hour'] = $hour;
                $where['minute'] = $minute;
            } else if ($i == 3) {
                $where['week'] = $week;
                $where['hour'] = $hour;
                $where['minute'] = $minute;
            } else if ($i == 4) {
                $where['day'] = $day;
                $where['hour'] = $hour;
                $where['minute'] = $minute;
            } else if ($i == 5) {
                $where['month'] = $mouth;
                $where['day'] = $day;
                $where['hour'] = $hour;
                $where['minute'] = $minute;
            } else if ($i == 6) {
                $where['execute_type'] = 1;
                $where['year'] = $year;
                $where['month'] = $mouth;
                $where['day'] = $day;
                $where['hour'] = $hour;
                $where['minute'] = $minute;
            }
            $where['execute_type'] = $i;
            $arr = $this->plan_model->getAll($where, 'id,fun_name', 1000);
            foreach ($arr as $key => $value) {
                $plan_list[] = $value;
            }
            unset($arr);
            unset($where);
        }
        return $plan_list;
    }
}