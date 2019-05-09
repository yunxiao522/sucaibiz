<?php

namespace app\model;

class MiniAppColumn extends Base
{
    protected $name = 'miniapp_column';

    protected $updateTime = '';

    public static $status = [1=>'推荐', 2=>'不推荐'];

    public function getTStatusAttr($value){
        return self::$status[$value];
    }
}