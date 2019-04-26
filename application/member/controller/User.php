<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/22
 * Time: 22:39
 */
namespace app\member\controller;
use think\Request;
use think\View;

class User extends Common{
    private $experince_field = 'experience';
    public function __construct()
    {
        parent::__construct();
    }

    //更改用户经验方法
    public function changeExperince($where =[] ,$type ='' ,$price = 0){
        if(empty($type) || $price == 0 ||empty($where)){
            return false;
        }
        $user = model('User');
        $result = $user->updateUserField($where ,$this->experince_field ,$type ,$price);
        return $result;
    }
    //添加用户标签方法
    public function addUserTag(){
        if(Request::instance()->isPost()){
            //验证前台提交的数据
        }else{
            return View('templates/user_tag_add');
        }
    }
}