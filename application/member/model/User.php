<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/3/3
 * Time: 11:48
 */
namespace app\member\model;
use app\common\model\Base;
use think\Model;
use think\Db;
use SucaiZ\Cache\Mysql;
class User extends Base {
    private $table_name = 'user';
    private $user_operate_table_name = 'user_operate';
    private $user_tag_table_name = 'user_tag';
    private $user_tag_list_table_name = 'user_tag_list';
    public $table = 'user';
    public function __construct()
    {
        parent::__construct();
    }

    //添加用户
    public function insertUser($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->insertGetId($data);
        return $res;
    }
    //更新用户信息方法
    public function updateUser($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        if(isset($where['id'])){
            $res = Mysql::update($this->table_name ,'id' ,$where['id'] ,$data);
        }else{
            $res = Db::name($this->table_name)->where($where)->update($data);
        }
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //查询用户信息方法
    public function getUser($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        if (empty($where)) {
            return [];
        }
        if (isset($where['id'])) {
            return Mysql::find($this->table_name, 'id', $where['id']);
        } else {
            $res = Db::name($this->table_name)->where($where)->field(' * ')->find();
            return $res;
        }
    }
    //根据条件获取用户总数
    public function getUserCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //更新数据表字段值
    public function updateUserField($where = [] ,$field = '' ,$type = '+' ,$price = 1 ){
        if(empty($field) || empty($where)){
            return false;
        }
        if($type == '+'){
            $res = Db::name($this->table_name)->where($where)->setInc($field ,$price);
        }else if($type == '-'){
            $res = Db::name($this->table_name)->where($where)->setDec($field ,$price);
        }
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
    //插入用户操作记录方法
    public function insertUserOperate($uid ,$type ,$content ,$class ,$description){
        $arr = [
            'uid'=>$uid,
            'type'=>$type,
            'content'=>$content,
            'class'=>$class,
            'create_time'=>time(),
            'description'=>$description
        ];
        $res = Db::name($this->user_operate_table_name)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //高级更新用户信息方法
    public function updateUserInfo($where = [] ,$arr = [] ,$class = '' ,$update_key = [] ,$description = ''){
        if(empty($where) || empty($arr) || empty($class) || empty($update_key) || empty($description)){
            return false;
        }
        //开启数据库事务
        Db::startTrans();
        //根据where获取用户id
        $user_info = $this->getUser($where ,' * ');
        //先执行更新操作
        $res = $this->updateUser($where ,$arr);
        if($res === false){
            //回滚数据
            Db::rollback();
            return false;
        }else{
            $uid = $user_info['id'];
            //组合用户更新记录
            $content = [];
            foreach($update_key as $key => $value){
                if($arr[$key] != $user_info[$key]){
                    $content[] = [
                        'key'=>$key,
                        'text'=>$value,
                        'original'=>$user_info[$key],
                        'complete'=>$arr[$key]
                    ];
                }
            }
            $content_text = json_encode($content ,JSON_UNESCAPED_UNICODE);
            //插入用户操作记录
            $result = $this->insertUserOperate($uid ,1 ,$content_text ,$class ,$description);
            if($result){
                //保存数据
                Db::commit();
                return true;
            }else{
                //回滚数据
                Db::rollback();
                return false;
            }
        }
    }
    //添加用户标签方法
    public function addUserTagInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->user_tag_table_name)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取标签列表方法
    public function getUserTagInfoList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        return Db::name($this->user_tag_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
}