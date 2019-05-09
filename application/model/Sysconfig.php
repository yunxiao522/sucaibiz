<?php


namespace app\model;


class Sysconfig extends Base
{
    protected $name = 'sysconfig';

    public static $sysconfig_type = [
        1 => ['k' => 1, 'type' => 'test', 'value' => '文本框'],
        2 => ['k' => 2, 'type' => 'radio', 'value' => '单选框'],
        3 => ['k' => 3, 'type' => 'checkbox', 'value' => '多选框'],
        4 => ['k' => 4, 'type' => 'select', 'value' => '下拉框'],
        5 => ['k' => 5, 'type' => 'textarea', 'value' => '文本框'],
    ];
}