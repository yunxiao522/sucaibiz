<?php


namespace app\admin\controller;


use think\Request;

class Mobile extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    //栏目封面管理
    public function coverManage(){
        if(Request::instance()->isGet()){
            return view('coverManage');
        }
    }

    //栏目管理
    public function columnManage(){
        
    }

    //tag标签管理
    public function tagManage(){

    }
}