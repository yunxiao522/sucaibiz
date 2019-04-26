<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/5
 * Time: 11:24
 */

namespace app\timing\controller;


use app\timing\model\Tag;

class Init extends Common
{
    public function __construct()
    {
        parent::__construct();
    }
    public function index(){
        echo 1;
    }
    public function initTag(){
        //组合更新条件
        $where = '1 = 1';
        $tag = new Tag();
        //判断今天是否是月初第一天
        $d = date('d');
        if($d == '01'){
            $tag->updateTagInfo($where ,['monthcc'=>0]);
        }
        //判断今天是否是星期一
        $w = date('w');
        if($w == 1){
            $tag->updateTagInfo($where ,['weekcc'=>0]);
        }
        //判断现在是否是凌晨
        $h = date('H');
        if($h == '00'){
            $tag->updateTagInfo($where ,['daycc'=>0]);
        }
    }
}