<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/6
 * Time: 20:28
 * Description: Rbac相关表数据库模型
 */


namespace app\admin\model;
use think\Db;

class Rbac extends Common
{
    //模块表名
    private $model_tabe = 'rbac_model';

    //构造函数
    public function __construct(){
        parent::__construct();
    }

    //获取模块列表
    public function getModelList($where = [] ,$field = ' * ' ,$limit = 1000 ,$orde = ' id asc '){
        return Db::name($this->model_tabe)->field($field)->where($where)->limit($limit)->order($orde)->select();
        dump(Db::table($this->model_tabe)->getLastSql());
    }

    //添加模块方法
    public function addModel($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->model_tabe)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取模块列表总数
    public function getModelCount($where = []){
        return Db::name($this->model_tabe)->where($where)->count(' id ');
    }

    //获取模块详细信息方法
    public function getModelInfo($where = [] ,$field=' * '){
        if(empty($where) || empty($field)){
            return [];
        }
        return Db::name($this->model_tabe)->field($field)->where($where)->find();
    }

    //删除模块方法
    public function delModel($where = []){
        if(empty($where)){
            return false;
        }
        return $this->deleteData($where ,'id' ,'rbac_model' ,$this->model_tabe);
    }

    //修改模块信息
    public function alterModel($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        return $this->updateData($where ,$arr ,'rbac_model' ,$this->model_tabe);
    }
}