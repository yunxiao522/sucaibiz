<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/8
 * Time: 0:01
 * Description：文章属性模型
 */
namespace app\admin\model;
use think\Model;
use think\Db;
class Attribute extends Common {
    private $table_name = 'attribute';
    public $table = 'attribute';
    public function __construct()
    {
        parent::__construct();
    }

    //查询获取属性列表
    public function getAttributeList($where = [] , $field = ' * '){
        $res = Db::name($this->table_name)->field($field)->where($where)->select();
        return $res;
    }

    //新增属性
    public function addAttribute($data = []){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $res = Db::name($this->table_name)->insert($data);
        return $res;
    }

    //获取属性详细信息
    public function getAttributeInfo($where = [] , $field = ' * '){
        if(empty($where) || !is_array($where)){
            return [];
        }
        $res = Db::name($this->table_name)->field($field)->where($where)->find();
        return $res;
    }

    //修改属性信息
    public function alterAtt($where = [] , $arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($arr);
        return $res;
    }
}