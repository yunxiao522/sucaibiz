<?php

namespace app\validate;

class AdminLevel extends BaseValidate
{
    protected $rule = [
        'name'=>'require|max:20',
        'level'=>'require|number',
        'description'=>'require|max:40'
    ];
    protected $message = [
        'name.require'=>'输入的等级名称不能为空',
        'name.max'=>'输入的等级名称不能超过20个字符',
        'level.require'=>'请选择角色值',
        'level.number'=>'输入的角色值只能为数字',
        'description.require'=>'请输入角色描述',
        'description.max'=>'输入的角色描述不能超过40个字符'
    ];
}