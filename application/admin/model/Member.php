<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/16
 * Time: 10:23
 * Description：会员数据库模型
 */
namespace app\admin\model;
use think\Model;
use think\Db;
class Member extends Common {
    public $user_table_name = 'user';
    public $level_table_name = 'user_level';
    public $log_table_name = 'log_login';
    public function __construct()
    {
        parent::__construct();
    }
    //获取用户等级列表
    public function getMemberLevel($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $res = Db::name($this->level_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //新增会员等级
    public function addMemberLevel($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->level_table_name)->insert($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取会员等级总数目
    public function geMemberLevelCount($where = []){
        $res = Db::name($this->level_table_name)->where($where)->count('id');
        return $res;
    }

    //删除会员等级
    public function delMemberLevel($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->level_table_name)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取会员等级详细信息
    public function getMemberLevelInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->level_table_name)->where($where)->field($field)->find();
        return $res;
    }

    //修改会员等级信息
    public function alterMemberLevel($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        $res = Db::name($this->level_table_name)->where($where)->update($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取会员列表
    public function getMemberList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $res = Db::name($this->user_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取会员总数
    public function getMemberCount($where = []){
        $res = Db::name($this->user_table_name)->where($where)->count('id');
        return $res;
    }

    //修改会员信息
    public function alterMember($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        $res = Db::name($this->user_table_name)->where($where)->update($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取会员登录日志列表
    public function getMemberLoginLogList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $res = Db::name($this->log_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取会员登录日志总条数
    public function getMemberLoginLogCpunt($where = []){
        $res = Db::name($this->log_table_name)->where($where)->count('id');
        return $res;
    }

    //获取会员详细信息
    public function getMemberInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->user_table_name)->where($where)->field($field)->find();
        return $res;
    }

    //删除会员等级
    public function delMember($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->user_table_name)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}