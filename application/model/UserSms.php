<?php


namespace app\model;


class UserSms extends Base
{
    protected $name = 'user_sms';

    protected $updateTime = '';
    //短信状态
    public static $sms_status = [1 => '发送成功', 2 => '发送失败', 3 => '发送中'];
}