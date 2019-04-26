<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/5
 * Time: 13:43
 */

namespace app\timing\model;
use think\Model;
use think\Db;

class Tag extends Model
{
    private $table_name = 'tag';
    public function __construct()
    {
        parent::__construct();
    }
    //更新tag表数据
    public function updateTagInfo($where = [] ,$arr = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}