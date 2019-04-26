<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/22
 * Time: 23:34
 */

namespace app\admin\model;


use think\Model;
use think\Db;

class Integral extends Model
{
    private $integral_level = 'user_integral_level';

    public function __construct()
    {
        parent::__construct();
    }

    //获取会员积分等级列表
    public function getIntegralList($where = [], $field = ' * ', $limit = 1000, $order = 'id desc')
    {
        $res = Db::name($this->integral_level)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //新增会员积分等级
    public function addIntegralInfo($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->integral_level)->insert($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取会员积分等级总条数
    public function getIntegralCount($where = []){
        $res = Db::name($this->integral_level)->where($where)->count('id');
        return $res;
    }

    //删除会员积分等级
    public function delIntegral($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->integral_level)->where($where)->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取会员积分等级信息
    public function getIntegralInfo($where = [], $field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->integral_level)->where($where)->field($field)->find();
        return $res;
    }

    //修改会员积分等级信息
    public function alterIntegralInfo($where =[] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        $res = Db::name($this->integral_level)->where($where)->update($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}