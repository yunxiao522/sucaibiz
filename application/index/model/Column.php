<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/16
 * Time: 9:36
 * Description：文档分类数据库模型
 */
namespace app\index\model;
use think\Model;
use think\Db;
class Column extends Common {
    private $table_name = "column";
    public $table = 'column';
    public function __construct()
    {
        parent::__construct();
    }
    //获取栏目排列最新编号
    public function getSortNum($parent_id = 0){
        $res = Db::name($this->table_name)->where(['parent_id' => $parent_id])->count('id');
        return $res;
    }

    //新增栏目
    public function createColumn($data = []){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $res = Db::name($this->table_name)->insert($data);
        if($res){
            $column_id = Db::name($this->table_name)->getLastInsID();
            return $column_id;
        }else{
            return false;
        }
    }

    //获取栏目列表

    /**
     * @param array $where
     * @param string $filed
     * @param int $limit
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getColumnList($where = [] ,$filed = ' * ' ,$limit = 1000 ,$order='id desc'){
        $res = Db::name($this->table_name)->field($filed)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取栏目详细信息
    public function getColumnInfo($where = [] , $field = ' * '){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->field($field)->where($where)->find();
        if($res){
            return $res;
        }else{
            return false;
        }
    }

    //修改栏目数据
    public function alterColumnInfo($where = [] , $arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($arr);
        return $res;
    }

    //删除栏目
    public function delColumn($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->delete();
        return $res;
    }

    //判断栏目id是否存在

    /**
     * @param string $id
     * @return bool
     */
    public function checkColumnId($id = ''){
        //判断是否输入文档id,没有输入返回不存在
        if(empty($id)){
            return false;
        }

        //到数据库中查询条数
        $res = Db::name($this->table_name)->where(['id'=>$id])->count('id');
        if($res == 0){
            return false;
        }else{
            return true;
        }
    }

    //获取栏目列表方法
    public function getColumnListToCache($where = [] ,$field = ' * ' ,$limit = 1000 ,$order = ' id desc '){
        return $this->getDataListCache($this->table_name ,$where ,$field ,$limit ,$order);
    }

}