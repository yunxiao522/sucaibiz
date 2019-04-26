<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/21
 * Time: 12:53
 * Description:下载表模型
 */


namespace app\index\model;
use think\Db;

class Down extends Common
{
    private $down_table_name = 'down';
    private $my_down_table_name = 'my_down';
    private $user_down_table_name = 'user_down';
    public $table = 'down';
    public function __construct()
    {
        parent::__construct();
    }
    //获取下载表总数
    public function getDownCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->down_table_name)->where($where)->count('id');
    }
    //添加下载表信息
    public function insertDownInfo($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->down_table_name)->insert($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //增加下载文件次数
    public function incrDownNum($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->down_table_name)->where($where)->setInc('num');
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //添加我的下载
    public function insertMyDown($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->my_down_table_name)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取我的下载总数
    public function getMyDownCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->my_down_table_name)->where($where)->count('uid');
    }
}