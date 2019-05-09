<?php


namespace app\model;


class Queue extends Base
{
    protected $name = 'queue';

    protected $updateTime = '';

    public static $queue_type = [1=>'基础队列', 2=>'执行队列'];

    public static $status = [1=>'未执行', 2=>'执行成功', 3=>'执行失败'];

    public function getQueueTypeAttr($value){
        return self::$queue_type[$value];
    }

    public function getStatusAttr($value){
        return self::$status[$value];
    }

    public function getOutTimeAttr($value){
        return date('Y-m-d H:i:s', $value);
    }
}