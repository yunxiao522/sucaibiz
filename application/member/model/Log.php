<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/18
 * Time: 22:37
 */
namespace app\member\model;
use think\Model;
use think\Db;
class Log extends Model
{
    private $login_table_name = 'log_login';
    public function __construct()
    {
        parent::__construct();
    }

    //写入登录日志
    public function addLoginLog($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->login_table_name)->insert($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取登录日志
    public function getLoginLogList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = 'id desc'){
        if(empty($where)){
            return [];
        }
        return Db::name($this->login_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取登录总数
    public function getCount($where = [] ,$field = 'id'){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->login_table_name)->where($where)->count($field);
    }
}