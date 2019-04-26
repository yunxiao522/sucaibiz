<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/30
 * Time: 14:52
 */

namespace app\index\model;
use app\common\model\Base;
use think\Session;
use think\Db;


class Common extends Base
{
    private $log_operate = 'log_operate';
    private $redis;
    private $time;
    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
        $this->time = 30;
    }

    //添加操作日志表
    private function insertOperate($data ,$type = 1 ,$class = ''){
        $uid = Session::get('admin')['id'];
        if(!isset($uid)){
            $uid = 0;
        }
        //组合数据添加
        $a = [
            'uid'=>$uid,
            'type'=>$type,
            'class'=>$class,
            'content'=>$data,
            'create_time'=>time()
        ];
        $res = Db::name($this->log_operate)->insert($a);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //二次封装更新数据方法
    public function updateData($where = [] ,$data = [] ,$class = 'admin' ,$table_name = ''){
        if(empty($where) || empty($data) || empty($table_name)){
            return false;
        }
        //先查询原有数据
        $original_data = Db::name($table_name)->where($where)->field(' * ')->find();
        $log_centent = '';
        foreach ($data as $key => $value){
            if(isset($original_data[$key])&&$key != 'create_time' &&$key != 'alter_time'){
                if($value != $original_data[$key]){
                    $log_centent .= $key .':'."$original_data[$key]" ."修改为" ."$value" .";";
                }
            }
        }
        if(empty($log_centent)){
            return true;
        }
        //更新表数据
        //先开启数据库事务
        Db::startTrans();
        $res = Db::name($table_name)->where($where)->update($data);
        if($res !== false){
            if($this->insertOperate($log_centent ,1 ,$class)){
                Db::commit();
                return true;
            }else{
                Db::rollback();
            }
        }
        return false;
    }

    //二次封装删除数据方法
    public function deleteData($where = [] ,$pk = '' , $class = '',$table_name = ''){
        if(empty($where) || empty($pk) || empty($table_name) || empty($class)){
            return false;
        }
        //先查询原有数据
        $original_data = Db::name($table_name)->where($where)->field($pk)->find();
        $log_centent = "删除 '" .$original_data[$pk] ."'";
        //更新表数据
        //先开启数据库事务
        Db::startTrans();
        $res = Db::name($table_name)->where($where)->delete();
        if($res !== false){
            if($this->insertOperate($log_centent ,2 ,$class)){
                Db::commit();
                return true;
            }else{
                Db::rollback();
            }
        }
        return false;
    }

    //二次封装查询列表数据方法
    public function getDataListCache($table_name = '' ,$where = '' ,$field = ' * ' ,$limit = 10000 ,$order = ' id desc '){
        //构造redis的存储key
        $key = $table_name .$this->getW($where) .$field .$limit .$order;
        //查询数据
        $data = $this->getRedis($key);
        if(empty($data)){
            $data = Db::name($table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
            $this->setRedis($key ,$data);
        }
        return $data;
    }

    //二次封装查询信息方法
    public function getDateInfoCache($table_name = '' ,$where = '' ,$field = ''){
        //构造redis的存储key
        $key = $table_name .$this->getW($where) .$field;
        //查询数据
        $data = $this->getRedis($key);
        if(empty($data)){
            $data = Db::name($table_name)->field($field)->where($where)->find();
            $this->setRedis($key ,$data);
        }
        return $data;
    }

    //处理where数据

    /**
     * @param $where
     * @return string
     */
    public function getW($where){
        if(is_array($where)){
            return json_encode($where ,JSON_UNESCAPED_UNICODE);
        }else{
            return $where;
        }
    }

    //写入数据到redis内
    public function setRedis($key ,$value){
        $this->redis->set($key ,json_encode($value ,JSON_UNESCAPED_UNICODE) ,$this->time);
    }
    //获取redis内数据
    public function getRedis($key){
        $info = $this->redis->get($key);
        if(!empty($info)){
            return json_decode($info ,true);
        }else{
            return [];
        }
    }

}