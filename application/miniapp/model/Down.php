<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/15
 * Time: 17:26
 * Description: 我的下载表
 */


namespace app\miniapp\model;
use think\Model;
use think\Db;

class Down extends Model
{
    //我的下载表名
    private $down_table = 'my_down';

    public function __construct()
    {
        parent::__construct();
    }

    //根据条件获取总条数
    public function getDownCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->down_table)->where($where)->count('create_time');
    }

    //添加我的下载信息
    public function insertDown($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->down_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取我的下载列表
    public function getDownList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id asc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->down_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
}