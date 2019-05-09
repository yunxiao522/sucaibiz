<?php

namespace app\model;

class Comment extends Base
{
    protected $name = 'comment';

    public static $comment_status = [1=>'正常', 2=>'禁用'];

    public function getStatusAttr($value)
    {
        return self::$comment_status[$value];
    }
}