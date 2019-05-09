<?php


namespace app\validate;


class Version extends BaseValidate
{
    protected $rule = [
        'number'=>'require|max:10',
        'pubdate'=>'require',
        'content'=>'require',
        'title'=>'require|max:20'
    ];
    protected $message = [
        'number.require'=>'请输入版本号',
        'number.max'=>'版本号不能超过10个字符',
        'pubdate.require'=>'请输入发布时间',
        'content.require'=>'请输入发布版本内容',
        'title.require'=>'请输入版本号描述',
        'title.max'=>'版本号描述不能超过20个字符'
    ];
}