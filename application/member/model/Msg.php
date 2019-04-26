<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/16
 * Time: 13:04
 */

namespace app\member\model;


use think\Db;
use think\Model;

class Msg extends Model
{
    private $table_name = 'msg';
    public function __construct()
    {
        parent::__construct();
    }
    //根据条件获取消息条数
    public function getCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->table_name)->where($where)->count('id');
    }
    //获取消息列表
    public function getMsgList($where = [] ,$field = ' * ' ,$limit = 10 ,$order = 'id desc'){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //更新消息信息
    public function updateMsgInfo($where = [] ,$arr = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取消息详细信息
    public function getMsgInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->find();
    }
    //添加消息方法
    public function addMsgInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->table_name)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

}