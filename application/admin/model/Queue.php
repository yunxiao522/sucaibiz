<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/3/25
 * Time: 12:47
 */
namespace app\admin\model;
use think\Model;
use think\Db;
class Queue extends Model{
    private $table_name = 'queue';
    public function __construct()
    {
        parent::__construct();
    }

    //添加数据到队列表中
    public function addQueue($data = []){
        if(empty($data)){
            return false;
        }
        $queue_id = Db::name($this->table_name)->insertGetId($data);
        return $queue_id;
    }

    //更新数据到队列表中
    public function updateQueue($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取队列列表
    public function getQueueList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $res = Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取队列表总条数
    public function getQueueCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }
}