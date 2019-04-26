<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/14
 * Time: 11:01
 * Description：用户授权数据库模型
 */

namespace app\member\model;
use think\Db;
use think\Model;

class Author extends Model
{
    private $table_name = 'user_author';
    public function __construct()
    {
        parent::__construct();
    }
    //新增用户授权信息
    public function addAuthor($arr = []){
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
    //修改用户授权信息
    public function alterAuthor($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //删除用户授权信息
    public function deleteAuthor($where){
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
    //获取用户授权信息
    public function getAuthor($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->find();
    }
    //根据条件获取用户授权信息条数
    public function getAuthorCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->table_name)->where($where)->count('uid');
    }
    //高级新增用户授权信息方法
    public function addAuthorInfo(){

    }
    //高级修改用户授权信息方法
    public function alterAuthorInfo(){

    }
}