<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/23
 * Time: 22:38
 */

namespace app\admin\model;


use think\Model;
use think\Db;

class Email extends Model
{
    public $table_name = 'user_email';
    public $table_alias = 'e';
    public $user_table_name;
    public $user_table_alias = 'u';
    public function __construct()
    {
        parent::__construct();
        //获取会员表表名
        $member = new Member();
        $this->user_table_name = $member->user_table_name .' ' .$this->user_table_alias;
    }

    //获取会员邮件列表
    public function getEmailList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $condition = $this->table_alias .'.uid = ' .$this->user_table_alias .'.id';
        $res = Db::name($this->table_name)
            ->alias('e')
            ->join($this->user_table_name ,$condition ,'left')
            ->field($field)
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->select();
        return $res;
    }

    //获取会员邮件总条数
    public function getEmailCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //删除会员邮件
    public function delEmailInfo($where = []){
        if(empty($where)){
            echo '非法访问';
            die;
        }
        $res = Db::name($this->table_name)->where($where)->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取会员邮件详细信息
    public function getEmailInfo($where = [] ,$field = ' * '){
        $condition = $this->table_alias .'.uid = ' .$this->user_table_alias .'.id';
        $res = Db::name($this->table_name)
            ->alias($this->table_alias)
            ->join($this->user_table_name ,$condition ,'left')
            ->field($field)
            ->where($where)
            ->find();
        return $res;
    }
}