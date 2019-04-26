<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/8 0008
 * Time: 20:19
 */

namespace app\index\model;


use think\Db;

class MyDown extends Common
{
    public $table = 'my_down';
    public function __construct()
    {
        parent::__construct();
    }

    public function add($data){
        $res = Db::name($this->table)->insert($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}