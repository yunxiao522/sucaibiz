<?php


namespace app\model;


class AdminUser extends Base
{
    protected $name = 'admin_user';

    public static $status = [1=>'启用', 2=>'禁用'];
}