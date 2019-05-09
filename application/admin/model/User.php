<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/8
 * Time: 13:55
 * Description：管理员用户表数据库模型
 */
namespace app\admin\model;
use think\Model;
use think\Db;

class User extends Common {
    public $table_name = 'admin_user';
    private $table_name1 = 'admin_level';
    public $table = 'user';
    public function __construct()
    {
        parent::__construct();
    }

    //查询单个用户信息方法
    public function getUserInfoOne($where = [] , $field = ' * '){
        $res = Db::name($this->table_name)->field($field)->where($where)->find();
        return $res;
    }

    //修改用户信息
    public function editUserInfo($where = [] , $data = []){
        if(empty($where)){
            return false;
        }
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取用户列表方法
    public function getUserList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $res = Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取用户等级列表
    public function getUserLevelList(){
        $res = Db::name($this->table_name1)->order(' id asc  ')->select();
        return $res;
    }

    //获取用户等级总数
    public function getUserLevelCount(){
        return Db::name($this->table_name1)->count('id');
    }

    //获取管理员总数
    public function getUserCount($where){
        return Db::name($this->table_name)->where($where)->count('id');
    }

    //添加用户信息
    public function addUserInfo($arr = []){
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

    //删除用户信息方法
    public function delUserInfo($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //删除角色信息方法
    public function delLevel($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name1)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //添加角色信息
    public function addLevel($arr=[]){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->table_name1)->insert($arr);
        if($res === false)
        {
            return false;
        } else{
            return true;
        }
    }

    //获取角色信息
    public function getLevelInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name1)->field($field)->where($where)->find();
    }

    //修改角色信息
    public function alterLevelInfo($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->table_name1)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

}