<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/6
 * Time: 16:31
 * Description:
 */


namespace app\admin\model;
use think\Model;
use think\Db;

class Miniapp extends Model
{
    //小程序-栏目表
    private $miniapp_column_table = 'miniapp_column';
    //小程序-tag表
    private $miniapp_tag_table = 'miniapp_tag';
    public function __construct()
    {
        parent::__construct();
    }

    //获取栏目列表数据
    public function getColumnList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        return Db::name($this->miniapp_column_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //添加单条栏目列表数据
    public function addColumnInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->miniapp_column_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //添加多条栏目数据
    public function addMoreColumnInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        //获取添加数据的长度
        $length =count($arr);
        //开启数据库事务
        Db::startTrans();
        $res = Db::name($this->miniapp_column_table)->insertAll($arr);
        //判断添加成功的条数和数组的长度是否一致，一致说明全部添加成功，否则没有全部写入成功
        if($res == $length){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }

    }
    //删除栏目数据
    public function delColumnInfo($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->miniapp_column_table)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取栏目条数
    public function getColumnSum($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->miniapp_column_table)->where($where)->count('id');
    }
    //查询栏目信息
    public function getColumnInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->miniapp_column_table)->field($field)->where($where)->find();
    }
    //修改栏目数据
    public function alterColumnInfo($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->miniapp_column_table)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //添加多条tag信息
    public function addMoreTagInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        //获取添加数据的长度
        $length = count($arr);
        //开启数据库事务
        Db::startTrans();
        $res = Db::name($this->miniapp_tag_table)->insertAll($arr);
        if($res == $length){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }
    //查询tag列表数据
    public function getTagList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        return Db::name($this->miniapp_tag_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取tag列表总条数
    public function getTagCount($where = []){
        return Db::name($this->miniapp_tag_table)->where($where)->count('id');
    }
    //查询tag详细信息
    public function getTagInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->miniapp_tag_table)->field($field)->where($where)->find();
    }
    //更新tag详细信息
    public function updateTagInfo($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->miniapp_tag_table)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}