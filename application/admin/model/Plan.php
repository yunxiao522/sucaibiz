<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/2
 * Time: 21:41
 */

namespace app\admin\model;


use think\Db;
use think\Model;

class Plan extends Common
{
    private $table_name = 'task';
    public $table = 'task';
    public function __construct()
    {
        parent::__construct();
    }

    //新增任务计划
    public function addPlan($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->insert($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取任务集合列表
    public function getPlanList($where = [] , $field = ' * ' , $limit = 10000 , $order = 'id desc'){
        $res = Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取任务总数目
    public function getPlanCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //删除计划任务
    public function delPlan($where = []){
        return $this->deleteData($where ,'name' ,'plan' ,$this->table_name);
    }

    //更新任务信息
    public function alterPlan($where = [] ,$data = []){
        return $this->updateData($where ,$data ,'plan' ,$this->table_name);
    }

    //原始更新任务信息方法
    public function updatePlan($where = [] ,$data = []){
        return Db::name($this->table_name)->where($where)->update($data);
    }

    //更新任务执行次数
    public function updatePlanNum($where = []){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->table_name)->where($where)->setInc('num');
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取任务详细信息
    public function getPlanInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->table_name)->where($where)->field($field)->find();
        return $res;
    }

}