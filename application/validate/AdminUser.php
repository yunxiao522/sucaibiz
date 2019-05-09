<?php

namespace app\validate;

class AdminUser extends BaseValidate
{
    protected $rule = [
        'user_name'=>'require|max:20|uniquer:user_name',
        'nick_name'=>'require|max:20',
        'email'=>'require|is_email|uniquer:email',
        'level'=>'require|number|inScope:AdminLevel,id',
        'phone'=>'require|is_phone|uniquer:phone',
        'realname'=>'require|max:15',
        'password'=>'require',
        'verfypassword'=>'require|confirm:password'
    ];

    protected $message = [
        'user_name.require'=>'请输入用户账户',
        'user_name.max'=>'用户账户不能超过20个字符',
        'user_name.uniquer'=>'用户账号已存在',
        'nick_name.require'=>'请输入账户昵称',
        'nick_name.max'=>'用户昵称不能超过20个字符',
        'email.require'=>'请输入用户邮箱',
        'email.is_email'=>'输入的邮箱格式不正确',
        'email.uniquer'=>'邮箱账号已存在',
        'level.require'=>'请选择账户所属角色',
        'level.number'=>'账户角色值只能是数字',
        'level.inScope'=>'账号等级错误',
        'phone.require'=>'请输入手机号码',
        'phone.is_phone'=>'输入的手机号码格式不正确',
        'phone.uniquer'=>'手机号码已存在',
        'realname.require'=>'请输入真实姓名',
        'realname.max'=>'输入的真实姓名不能超过15个字符',
        'password.require'=>'请输入密码',
        'verfypassword.require'=>'请输入验证密码',
        'verfypassword.confirm'=>'两次输入的密码不一致'
    ];

    protected $scene = [
        'add'=>['user_name','nick_name','email','level','phone','realname','password','verfypassword'],
        'edit'=>['user_name','nick_name','email','level','phone','realname'],
        'alter_password'=>['password','verfypassword']
    ];
}