<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/19
 * Time: 12:22
 * Description:
 */


namespace app\miniapp\model;
use think\Model;
use think\Db;

class Feedback extends Model
{
    //反馈数据表表名
    private $feedback_table = 'feedback';
    public function __construct()
    {
        parent::__construct();
    }

    //添加樊哙
    public function addFeedBack($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->feedback_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}