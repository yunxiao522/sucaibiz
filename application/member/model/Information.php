<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/20
 * Time: 13:20
 */

namespace app\member\model;
use think\Db;
use think\Model;

class Information extends Model
{
    private $table_name = 'user_information';
    private $uid = null;
    private $day = null;
    public function __construct($uid)
    {
        parent::__construct();
        //初始化日期数据
        $this->day = date('Y-m-d');
        $this->uid = $uid;
        //查询是否已经存在这个人的数据
        $count = $this->getCount(['uid'=>$uid]);
        if($count == 0){
            //为0则初始化这个会员的数据
            $this->addInformation(['uid'=>$uid ,'day'=>$this->day ,'create_time'=>time()]);
        }else{
            //判断是否已经存在当天数据
            $now = $this->getCount(['uid'=>$uid ,'day'=>$this->day] ,'id');
            if($now != 1){
                //判断是否是连续天数数据
                $list = $this->getInformationSelect(['uid'=>$uid] ,' * ' ,1,'day desc');
                $end = $list[0];
                $e_day = $end['day'];
                unset($end);
                //将最后一次的数据写入今天
                $end['create_time'] = time();
                $end['day'] = $this->day;
                $end['uid'] = $this->uid;
                $this->addInformation($end);
                //补齐最后一次到今天的数据
                //计算相差多少天
                $length = (strtotime(date('Y-m-d') ,time()) - strtotime($e_day)) / 86400;
                for($i = 1 ;$i < $length ;$i++){
                    $end['create_time'] = strtotime("-$i days");
                    $end['day'] = date('Y-m-d' ,$end['create_time']);
                    $this->addInformation($end);
                }
            }
        }
    }
    //插入数据
    public function addInformation($arr = []){
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
    //更新数据
    public function updateInformation($where = [] ,$arr = []){
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
    //增加某一个字段的值
    public function incInformationField($where = [] ,$field = 'index_num' ,$num = '1'){
        if(empty($where)){
            return false;
        }
        return Db::name($this->table_name)->where($where)->setInc($field ,$num);
    }
    //减少某一个字段的值
    public function decInformationField($where = [] ,$field =  'index_num' ,$num = '1'){
        if(empty($where)){
            return false;
        }
        return Db::name($this->table_name)->where($where)->setDec($field ,$num);
    }
    //根据条件计算数量
    public function getCount($where = '' ,$field = 'index_num'){
        return Db::name($this->table_name)->where($where)->count($field);
    }
    //获取单条数据
    public function getInformationFind($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->find();
    }
    //获取多条数据
    public function getInformationSelect($where = [] ,$field = ' * ' ,$limit = 10 ,$order = ' id desc '){
        if(empty($where)){
            return false;
        }
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取某个字段的值
    public function getInformationField($where = [] ,$field = ' id '){
        if(empty($where)){
            return 0;
        }
        $res = Db::name($this->table_name)->field($field)->where($where)->find();
        $field = trim($field ,' ');
        if(!empty($res)){
            return $res[$field];
        }else{
            return 0;
        }
    }
    //根据条件计算某一个字段的总和
    public function getSum($where = '' ,$field = 'index_num'){
        return Db::name($this->table_name)->where($where)->sum($field);
    }
}