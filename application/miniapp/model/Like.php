<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/15
 * Time: 14:40
 * Description: 收藏数据库模型
 */


namespace app\miniapp\model;
use think\Model;
use think\Db;

class Like extends Model
{
    //收藏表名
    private $like_table = 'my_like';
    //收藏夹表名
    private $class_table_name = 'my_like_class';
    public function __construct()
    {
        parent::__construct();
    }

    //添加收藏
    public function insertLike($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->like_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //删除收藏
    public function delLike($where = [])
    {
        if (empty($where)) {
            return false;
        }
        $res = Db::name($this->like_table)->where($where)->delete();
        if ($res === false) {
            return false;
        } else {
            return true;
        }
    }

    //获取收藏列表
    public function getLikeList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' create_time asc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->like_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取收藏夹条数
    public function getLikeClassCount($where = [] ,$field = 'id'){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->class_table_name)->where($where)->count($field);
    }
    //添加收藏夹信息
    public function addLikeClassInfo($arr = []){
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
}