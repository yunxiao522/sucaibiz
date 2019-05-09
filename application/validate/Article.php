<?php

namespace app\validate;

class Article extends BaseValidate
{
    protected $rule = [
        'token'=>'require',
        'title'=>'require|max:80',
        'litpic'=>'require|max:100',
        'click'=>'require|number|<0',
        'source'=>'require|max:15',
        'author'=>'require|max:15',
        'column_id'=>'require|number|inScope:Column,id',
        'gold'=>'require|<0',
        'pubdate'=>'require|max:20',
        'keywords'=>'require|max:60',
        'description'=>'require|max:250',
        'redirecturl'=>'max:50',
        'channel'=>'require|number|inScope:ColumnType,id',
        'templet'=>'require|max:60'
    ];
    protected $message = [
        'token.require'=>'非法访问',
        'title.require'=>'请输入文档标题',
        'title.max'=>'输入的文档标题不能超过80个字符',
        'litpic.require'=>'请上传缩略图',
        'litpic.max'=>'文档缩略图地址不能超过100个字符',
        'click.require'=>'请输入一个点击数量',
        'click.number'=>'点击数量只能是数字',
        'click.lt'=>'输入的点击数量不能小于0',
        'source.require'=>'文档来源不能为空',
        'source.max'=>'文档来源不能超过15个字符',
        'author.require'=>'文档作者不能为空',
        'author.max'=>'文档作者不能超过15个字符',
        'column_id.require'=>'请选择文档所属栏目',
        'column_id.number'=>'文档所属栏目id只能为数字',
        'column_id.inScope'=>'选择的栏目不正确',
        'gold.require'=>'请输入阅读文档所需金币',
        'gold.lt'=>'阅读所需金币数量不能小于0,可以是0',
        'pubdate.require'=>'请选择发布时间',
        'pubdate.max'=>'发布时间不能超过20个字符',
        'keywords.require'=>'文档关键字不能为空',
        'keywords.max'=>'文档关键字一共不能超过60个字符',
        'description.require'=>'请输入文档简介',
        'description.max'=>'文档简介不能超过250个字符',
        'redirecturl.lg'=>'文档存储名称不能超过50个字符',
        'channel.require'=>'文档所属栏目类型不能为空',
        'channel.number'=>'文档所属栏目类型必须是数字',
        'channel.inScope'=>'文档所属栏目类型数据错误',
        'templet.require'=>'请输入文档模板位置',
        'templet.max'=>'文档模板位置信息不能超过60个字符'
    ];
}