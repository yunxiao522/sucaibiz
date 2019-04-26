<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/24
 * Time: 16:21
 */

namespace app\admin\model;


use think\Model;
use think\Db;

class Sms extends Model
{
    public $table_name = 'user_sms';
    public $table_alias = 's';
    public $user_table_name;
    public $user_table_alias = 'u';
    public function __construct()
    {
        parent::__construct();
        $member = new Member();
        $this->user_table_name = $member->user_table_name .' ' .$this->user_table_alias;
    }

    //获取会员短信列表

    /**
     * @param array $where
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getSmsList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $condition = $this->table_alias .'.uid = ' .$this->user_table_alias .'.id';
        $res = Db::name($this->table_name)
            ->alias($this->table_alias)
            ->join($this->user_table_name ,$condition ,'left')
            ->field($field)
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->select();
        return $res;
    }

    //获取会员短信总条数
    public function getSmsCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //删除会员邮件
    public function delSmsInfo($where = []){
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
    public function getSmsInfo($where = [] ,$field = ' * '){
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