<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/30
 * Time: 9:14
 * Descroption：点击数据库模型
 */

namespace app\admin\model;
use app\common\model\Base;
use think\Db;

class Click extends Base
{
    public $table= 'click';
//    protected $name = '_click';
    public function __construct()
    {
        parent::__construct();
    }
    //获取点击数据列表
    public function getClickList($where = [] ,$field = ' * ' ,$limit = 10 ,$order = ' id desc '){
        return Db::name($this->table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //更新点击信息
    public function updateClickInfo($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->table)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}