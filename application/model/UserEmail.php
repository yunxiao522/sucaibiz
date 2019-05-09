<?php


namespace app\model;

class UserEmail extends Base
{
    protected $name = 'user_email';

    protected $updateTime = '';

    //邮件状态
    public static $email_status = [1 => '发送成功', 2 => '发送失败', 3 => '发送中', 0 => '发送成功'];
}