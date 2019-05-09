<?php


namespace app\model;


class User extends Base
{
    protected $name = 'user';

    public static $user_status = [1 => '未激活', 2 => '已激活'];

    public function email(){
        return $this->hasMany('UserEmail','id','uid');
    }
}