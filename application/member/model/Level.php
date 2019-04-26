<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/8
 * Time: 11:24
 * Description：用户等级模型
 */

namespace app\member\model;
use think\Db;
use think\Model;


class Level extends Model
{
    private $table_name = 'user_level';
    public function __construct()
    {
        parent::__construct();
    }
    //获取等级信息
    public function getLevelInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->find();

    }
}