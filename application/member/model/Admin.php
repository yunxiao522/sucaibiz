<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/6
 * Time: 13:55
 */

namespace app\member\model;
use think\Model;
use think\Db;

class Admin extends Model
{
    private $table_name = 'admin_user';
    public function __construct()
    {
        parent::__construct();
    }
    //获取管理员信息
    public function getAdminInfo($where = []){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field('nick_name')->where($where)->find();
    }
}