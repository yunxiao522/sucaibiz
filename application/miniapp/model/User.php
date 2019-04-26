<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/13
 * Time: 1:31
 * Description: 用户表模型
 */


namespace app\miniapp\model;


use think\Db;
use think\Model;

class User extends Model
{
    private $user_table = 'user';

    //获取用户信息
    public function getUserInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->user_table)->field($field)->where($where)->find();
    }

    //根据条件获取账号总数
    public function getUserCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->user_table)->where($where)->count('id');
    }

    //添加用户方法
    public function addUserInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        return Db::name($this->user_table)->insertGetId($arr);
    }
    //更新用户表数据
    public function updateUserInfo($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->user_table)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}