<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/24
 * Time: 20:33
 */

namespace app\member\model;


use app\common\model\Base;
use think\Model;
use think\Db;

class Sms extends Base
{
    private $table_name = 'user_sms';
    public $table = 'user_sms';
    public function __construct()
    {
        parent::__construct();
    }

    //写入会员短信表
    public function addSmsInfo($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->insert($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
}