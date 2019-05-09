<?php


namespace app\model;


class BackUp extends Base
{
    protected $name = 'backup';

    protected $updateTime = '';

    public static $status = [1=>'创建备份', 2=>'完成备份'];
    public function getStatusAttr($value){
        return self::$status[$value];
    }
    public function getRollTimeAttr($value){
        return date('Y-m-d H:i:s',$value);
    }
}