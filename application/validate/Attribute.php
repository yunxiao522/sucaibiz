<?php


namespace app\validate;


class Attribute extends BaseValidate
{
    protected $rule = [
        'att_name'=>'require|max:20',
        'att'=>'require|max:1'
    ];
    protected $message = [
        'att_name.require'=>'请输入属性名称',
        'att_name.max'=>'输入的属性名称不能超过20个字符',
        'att.require'=>'请输入属性值',
        'att.max'=>'输入的属性值只能是一个字符'
    ];
}