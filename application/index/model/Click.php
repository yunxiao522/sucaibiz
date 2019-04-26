<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/2
 * Time: 9:42
 * Description: 点击量表数据模型
 */


namespace app\index\model;
use app\common\model\Base;
use think\Db;
use think\model;

class Click extends model
{
    private $table_name = 'click';
    public function __construct()
    {
        parent::__construct();
    }
    //添加数据
    public function add($arr = []){
        $res = Db::name($this->table_name)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //增加点击量
    public function incrClick($where = [] ,$field = 'click'){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->setInc($field ,1);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //根据条件查询点击总数
    public function getCount($where){
        if(empty($where)){
            return false;
        }
        return Db::name($this->table_name)->where($where)->count('id');
    }
}