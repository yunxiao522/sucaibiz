<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/5
 * Time: 15:55
 */

namespace app\member\model;
use think\Db;
use think\Model;

class Fens extends Model
{
    private $table_name = 'user_fens';
    public function __construct()
    {
        parent::__construct();
    }
    //根据条件获取粉丝数据
    public function getCount($where = [] ,$field = 'uid'){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->table_name)->where($where)->count($field);
    }
    //获取粉丝列表
    public function getFensList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = 'create_time desc'){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //删除粉丝列表数据
    public function delFensInfo($where = []){
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
    //添加信息
    public function addFensInfo($arr = []){
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