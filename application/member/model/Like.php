<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/5
 * Time: 18:21
 */

namespace app\member\model;
use SucaiZ\Cache\Mysql;
use think\Db;

use think\Model;

class Like extends Model
{
    private $table_name = 'my_like';
    private $class_table_name = 'my_like_class';
    public function __construct()
    {
        parent::__construct();
    }
    //获取我的收藏列表
    public function getLikeList($where = [] ,$field = ' * ',$limit = 100 ,$order = 'id desc'){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取我收藏的条数
    public function getLikeCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->table_name)->where($where)->count('create_time');
    }
    //创建收藏夹方法

    /**
     * @param array $arr 创建收藏夹的信息
     * @return bool 返回是否成功结果
     */
    public function addLikeClass($arr = []){
        if(empty($arr)){
            return false;
        }
        $result = Db::name($this->class_table_name)->insert($arr);
        if($result === false){
            return false;
        }else{
            return true;
        }
    }
    //查询收藏夹方法

    /**
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @return 返回查询的 收藏夹信息
     */
    public function getLikeClassInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        if(isset($where['id'])){
            return Mysql::find($this->class_table_name ,'id' ,$where['id']);
        }else{
            return Db::name($this->class_table_name)->field($field)->where($where)->find();
        }
    }
    //获取收藏夹条数

    /**
     * @param array $where
     * @param string $field
     * @return int|string
     */
    public function getLikeClassCount($where = [] ,$field = 'id'){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->class_table_name)->where($where)->count($field);
    }
    //获取收藏夹列表

    /**
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param int $limit 查询条数
     * @param string $order 排序规则
     * @return 返回收藏夹列表
     */
    public function getLikeClassList($where = [] ,$field = ' * ' ,$limit = 10 ,$order = ' id desc '){
        return Db::name($this->class_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //删除收藏夹

    /**
     * @param array $where 删除条件
     * @return bool 删除结果
     */
    public function delLikeClass($where = []){
        if(empty($where)){
            return false;
        }
        $result = Db::name($this->class_table_name)->where($where)->delete();
        if($result === false){
            return false;
        }else{
            return true;
        }
    }
    //编辑收藏夹信息
    public function editLikeClass($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $result = Db::name($this->class_table_name)->where($where)->update($arr);
        if($result === false){
            return false;
        }else{
            return true;
        }
    }
    //删除我的收藏操作
    public function delLikeInfo($where = []){
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










































}