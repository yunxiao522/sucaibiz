<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/14
 * Time: 17:30
 * Description:
 */


namespace app\admin\model;
use think\Db;
use think\Model;

class Msg extends Model
{
    //消息表表名
    private $msg_table_name = 'msg';
    public function __construct()
    {
        parent::__construct();
    }
    //获取消息表总条数
    public function getCount($where = []){
        return Db::name($this->msg_table_name)->where($where)->count('id');
    }
}