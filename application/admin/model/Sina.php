<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/5
 * Time: 9:49
 */

namespace app\admin\model;


use think\Db;

class Sina extends Common
{
    private $table_name = 'sina_weibo';
    public $table = 'sina_weibo';
    public function __construct(){
        parent::__construct();
    }
    //写入微博列表
    public function addWeiBo($arr = []){
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

}