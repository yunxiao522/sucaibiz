<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/2
 * Time: 13:18
 * Description: 计划任务管理
 */

namespace app\admin\controller;

use app\model\Task;
use think\Db;
use think\Request;

class Plan extends Common
{
    //验证规则
    protected $rule = [
        'name' => 'require|max:30',
        'date' => 'require|max:20',
        'execute_type' => 'require|number',
        'fun_name' => 'require|max:50',
        'description' => 'require'
    ];
    //验证规则信息
    protected $msg = [
        'name.require' => '请输入计划名称',
        'name.max' => '计划名称不能超过30个字符',
        'date.require' => '请输入执行时间',
        'date.max' => '执行时间不能超过20个字符',
        'execute_type.require' => '请选择执行时间类型',
        'execute_type.number' => '参数错误',
        'fun_name.require' => '函数名称不能为空',
        'fun_name.max' => '函数名称不能超过50个字符',
        'description.require' => '描述不能为空'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * Description 获取计划列表数据
     */
    public function getPlanList()
    {
        $where = [];
        $Plan_List = Task::getList($where, 'id,name,fun_name,status,execute_time,execute_type,num,end_time,condition,create_time,alter_time', 'id desc');
        foreach ($Plan_List['data'] as $key => $value) {
            $Plan_List['data'][$key]['condition'] = Task::$condition[$value['condition']];
            $Plan_list['data'][$key]['execute_type'] = Task::$execute_type[$value['execute_type']];
        }
        return self::ajaxOkdata($Plan_List, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 新建任务计划
     */
    public function addPlan()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $result = $this->validate(input(),$this->rule,$this->msg);
            if(true !== $result){
                return self::ajaxError($result);
            }
            $condition = input('condition');
            if (isset($condition)) {
                $condition = 1;
            } else {
                $condition = 2;
            }

            $exectue_info = $this->verifyPlanExecuteTime(input('execute_type'),input('date'));
            if(is_string($exectue_info)){
                return self::ajaxError($exectue_info);
            }

            $data = [
                'name' => input('name'),
                'status' => 1,
                'execute_type' => input('execute_type'),
                'execute_time' => input('date'),
                'create_time' => time(),
                'num' => 0,
                'description' => input('description'),
                'condition' => $condition,
                'fun_name' => input('fun_name')
            ];
            $data += $exectue_info;

            $res = Task::add($data);
            if ($res) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            return View('Plan_add');
        }
    }

    /**
     * @return false|string
     * Description 删除计划任务
     */
    public function delPlan()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $res = Task::del($where);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改计划任务
     */
    public function alterPlan()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            //验证数据
            $result = $this->validate(input(), $this->rule, $this->msg);
            if (true !== $result) {
                return self::ajaxError($result);
            }
            $condition = input('condition');
            if (isset($condition)) {
                $condition = 1;
            } else {
                $condition = 2;
            }

            $execute_info = $this->verifyPlanExecuteTime(input('execute_type'), input('date'));
            if (is_string($execute_info)) {
                return self::ajaxError($execute_info);
            }
            $data = [
                'name' => input('name'),
                'execute_type' => input('execute_type'),
                'execute_time' => input('date'),
                'description' => input('description'),
                'condition' => $condition,
                'fun_name' => input('fun_name'),
                'alter_time' => time()
            ];
            $data += $execute_info;
            //开启数据库事务
            Db::startTrans();
            //初始化参数说明
            Task::edit($where, ['year' => '', 'month' => '', 'day' => '', 'week' => '', 'hour' => '', 'minute' => '']);
            $res = Task::edit($where, $data);
            if ($res) {
                Db::commit();
                return self::ajaxOk('修改成功');
            } else {
                Db::rollback();
                return self::ajaxError('修改失败');
            }
        } else {
            $Plan_info = Task::getOne($where, 'name,execute_type,execute_time,condition,description,fun_name');
            $this->assign('plan_info', $Plan_info);
            $this->assign('id', $id);
            return View('Plan_alter');
        }
    }

    /**
     * @param $execute_type
     * @param $execute_time
     * @return array|string
     * Description 验证计划执行时间
     */
    protected function verifyPlanExecuteTime($execute_type, $execute_time)
    {
        $data = [];
        switch ($execute_type) {
            case 1:
                if ($execute_time > 59 || $execute_time < 0) {
                    return '输入的执行时间只能在1-60之间';
                }
                $data['minute'] = $execute_time;
                break;
            case 2:
                $is_dateTime = checkDateTime($execute_time, 'H:i');
                if (!$is_dateTime) {
                    return '输入的执行时间不符合时间规则';
                }
                $data['hour']=(int)date('H',strtotime($execute_time));
                $data['minute'] = (int)date('i',strtotime($execute_time));
                break;
            case 3:
                $execute_time_arr = explode('$', $execute_time);
                if (!isset($execute_time_arr[1])) {
                    return '输入的执行时间不符合规则';
                }
                $is_dateTime = checkDateTime($execute_time_arr[1], 'H:i');
                if (!$is_dateTime) {
                    return '输入的执行时间不符合规则';
                }
                if ($execute_time_arr[0] < 1 || $execute_time_arr[0] || !is_int($execute_time_arr[0])) {
                    return '输入的星期,只能是1-7之间的整数';
                }
                $hour = explode(':', $execute_time_arr[1]);
                $data['week'] = $execute_time_arr[0];
                $data['hour'] = $hour[0];
                $data['minute'] = $hour[1];
                break;
            case 4:
                $execute_time_arr = explode('$', $execute_time);
                if (!isset($execute_time_arr[1])) {
                    return '输入的执行时间不符合规则';
                }
                $is_dateTime = checkDateTime($execute_time_arr[1], 'H:i');
                if (!$is_dateTime) {
                    return '输入的执行时间不符合规则';
                }
                if ($execute_time_arr[0] < 0 || $execute_time_arr[0] > 31 && !is_int($execute_time_arr[0])) {
                    return '输入的日期只能为1-31的整数';
                }
                $hour = explode(':', $execute_time_arr[1]);
                $data['day'] = $execute_time_arr[0];
                $data['hour'] = $hour[0];
                $data['minute'] = $hour[1];
                break;
            case 5:
                $execute_time_arr = explode('$', $execute_time);
                if (!isset($execute_time_arr[1])) {
                    return '输入的执行时间不符合规则';
                }
                $is_year = checkDateTime($execute_time_arr[0], 'm-d');
                $is_hour = checkDateTime($execute_time_arr[1], 'H:i');
                if (!$is_year || !$is_hour) {
                    return '输入的执行时间不符合时间规则';
                }
                $year_info = explode('-', $execute_time_arr[0]);
                $hour_info = explode(':', $execute_time_arr[1]);
                $data['month'] = $year_info[0];
                $data['day'] = $year_info[1];
                $data['hour'] = $hour_info[0];
                $data['minute'] = $hour_info[1];
                break;
            case 6:
                $is_dateTime = checkDateTime($execute_time);
                if (!$is_dateTime) {
                    return '输入的执行时间不符合时间规则';
                }
                $execute_time = strtotime($execute_time);
                $data['year'] = date('Y', $execute_time);
                $data['month'] = date('m', $execute_time);
                $data['day'] = date('d', $execute_time);
                $data['hour'] = date('H', $execute_time);
                $data['minute'] = date('i', $execute_time);
                break;
        }
        return $data;
    }


}