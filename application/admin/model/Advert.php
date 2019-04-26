<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/9
 * Time: 17:04
 * Description:
 */


namespace app\admin\model;
use think\Db;

class Advert extends Common
{
    private $table_name = 'advert';
    public $table = 'advert';
    //添加 广告
    public function add($arr = []){
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

    //获取广告
    public function getAdvert($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->find();
    }

    //获取广告列表
    public function getAdvertList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }

    //修改广告信息
    public function alterAdvert($where=[] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //删除广告信息
    public function delAdvert($where = []){
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