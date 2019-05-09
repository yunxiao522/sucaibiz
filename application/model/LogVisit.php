<?php


namespace app\model;


class LogVisit extends Base
{
    protected $name = 'log_visit';

    protected $updateTime = '';

    public static $visit_type = [1 => '文档', 2 => '列表', 3 => '首页', 4 => '其它'];
}