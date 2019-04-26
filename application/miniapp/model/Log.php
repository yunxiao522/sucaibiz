<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/17
 * Time: 20:06
 * Description:
 */


namespace app\miniapp\model;
use think\Model;
use think\Db;

class Log extends Model
{
    private $login_log_table = 'log_login';
    public function __construct()
    {
        parent::__construct();
    }

    //添加登录日志
    public function insertLoginLog($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->login_log_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}