<?php


namespace app\model;


class LogOperate extends Base
{
    protected $name = 'log_operate';

    protected $updateTime = '';

    //日志操作类型
    public static $log_type = [1=>'修改', 2=>'删除'];
    //日志级别
    public static $log_level = [1=>'公开', 2=>'私有'];
    //记录日志用户类型
    public static $log_user_type = [1=>'前台', 2=>'后台'];
}