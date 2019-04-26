<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/6
 * Time: 14:45
 */

namespace app\common\model;
use SucaiZ\Cache\Mysql;
use think\Db;

class Column extends Base
{
    private $table_name = 'column';
    public $table = 'column';
    public function __construct()
    {
        parent::__construct();
    }
    //获取栏目信息
    public function getColumnInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        if(isset($where['id'])){
            return Mysql::find($this->table_name ,'id' ,$where['id']);
        }else{
            return Db::name($this->table_name)->field($field)->where($where)->find();
        }
    }
    //获取栏目列表
    public function getColumnList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }

    public function getOne($where = [], $field = ' * ', $order = 'id desc')
    {
        if(isset($where['id'])){
            return Mysql::find($this->table,'id',$where['id']);
        }else{
            return parent::getOne($where, $field, $order);
        }
    }
}