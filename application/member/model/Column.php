<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/6
 * Time: 10:38
 */

namespace app\member\model;
use think\Model;
use think\Db;

class Column extends Model
{
    private $table_name = 'column';
    public function __construct()
    {
        parent::__construct();
    }
    //获取栏目列表
    public function getColumnList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = 'id desc'){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取栏目信息
    public function getColumnInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->find();
    }
}