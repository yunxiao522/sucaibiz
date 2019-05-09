<?php


namespace app\model;

class LogLogin extends Base
{
    protected $name = 'log_login';

    protected $createTime = 'login_time';

    protected $updateTime = '';

    public function getLoginTimeAttr($value){
        return date('Y-m-d H:i:s', $value);
    }
}