<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/12
 * Time: 17:35
 * Description：日志数据库模型
 */
namespace app\admin\model;
use think\Model;
use think\Db;
class Log extends Model{
    private $table_name_arr = [
        'upload_log_table_name' => 'log_upload',
        'login_log_table_name' => 'log_login'
    ];
    private $login_log_table_name = 'log_login';
    private $operate_table_name = 'log_operate';
    public $table_alias = 'l';
    public $user_table_name ='user';
    public $user_table_alias = 'u';
    public function __construct()
    {
        parent::__construct();
    }

    public $table_name;

    //获取表名
    public function getLogTableName(){
        if(!isset($this->table_name) || !isset($this->table_name_arr[$this->table_name])){
            echo '数据错误';
            die;
        }
        return $this->table_name_arr[$this->table_name];
    }

    //添加数据到数据表
    public function createTableInfo($arr = []){
        $table_name = $this->getLogTableName();
        if(empty($arr) || !is_array($arr)){
            return false;
        }
        $res = Db::name($table_name)->insert($arr);
        if($res === false){
            return $res;
        }else{
            //获取新添加数据的id
            $last_id = Db::name($table_name)->getLastInsID();
            return $last_id;
        }
    }

    //获取日志表详细信息列表
    public function getTableList($where = [] , $field = ' * ' , $limit = 100000 , $order = 'id desc'){
        $table_name = $this->getLogTableName();
        $res = Db::name($table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取日志总条数
    public function getLogCount($where = []){
        $table_name = $this->getLogTableName();
        $res = Db::name($table_name)->where($where)->count('id');
        return $res;
    }

    //获取登录日志列表
    public function getLoginLog($where = [] , $field = ' l.*,u.id,u.username,u.nickname ' , $limit = 100 , $order = 'l.id desc')
    {
        $condition = 'u.id = l.uid';
        $res = Db::name($this->login_log_table_name)
            ->alias('l')
            ->join('user u',$condition,'left')
            ->field($field)
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->select();
        return $res;
    }

    //获取登录日志总条数
    public function getLoginLogCount($where = []){
        $res = Db::name($this->login_log_table_name)->where($where)->count('id');
        return $res;
    }

    //获取操作日志列表
    public function getOperateList($where = [] , $field = ' o.*,u.id,u.user_name,u.nick_name ' , $limit = 100 , $order = 'o.id desc'){
        $condition = 'u.id = o.uid';
        $res = Db::name($this->operate_table_name)
            ->alias('o')
            ->join('admin_user u',$condition,'left')
            ->field($field)
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->select();
        return $res;
    }

    //获取操作日志总条数
    public function getOperateCount($where = []){
        $res = Db::name($this->operate_table_name)->where($where)->count('id');
        return $res;
    }


}