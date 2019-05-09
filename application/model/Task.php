<?php


namespace app\model;

class Task extends Base
{
    protected $name = 'task';

    public static $status = [1 => '未执行', 2 => '已执行'];

    public static $execute_type = [1 => '每小时', 2 => '每天', 3 => '每周', 4 => '每月', 5 => '每年', 6 => '固定时间'];

    public static $condition = [1=>'启用', 2=>'禁用'];

    public function getEndTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public function getStatusAttr($value)
    {
        return self::$status[$value];
    }


}