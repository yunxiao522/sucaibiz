$(function () {
    $.ajax({
        'url': '/admin/getnamerule',
        'type': 'post',
        success: function (e) {
            var rule = JSON.parse(e);
            $('#name_rule').val(rule.column_article_rule);
            $('#list_rule').val(rule.column_list_rule);
        }
    });
    layui.use(['form', 'layedit', 'laydate', 'upload'], function () {
        var form = layui.form
            , layer = layui.layer
            , layedit = layui.layedit
            , laydate = layui.laydate
            , upload = layui.upload;

        //日期
        laydate.render({
            elem: '#date'
        });
        laydate.render({
            elem: '#date1'
        });

        //创建一个编辑器
        var editIndex = layedit.build('LAY_demo_editor');

        //自定义验证规则
        form.verify({
            type_name: function (value) {
                if (value.length == 0) {
                    return '栏目名称不能为空哦';
                } else if (value.length > 30) {
                    return '栏目名称不能超过30个字符哦';
                }
            },
            channel_type: function (value) {
                if (value == '') {
                    return '要选择内容模型啊';
                }
            },
            sort_rank: function (value) {
                if (value == '') {
                    return '输入的排列顺序不能为空哦';
                }
            },
            is_send: function (value) {
                if (value == '') {
                    return '请选择是否支持投稿';
                }
            },
            keywords: function (value) {
                if (value.length > 100) {
                    return '输入的栏目关键词不能超过100个字符哦';
                }
            },
            type_dir: function (value) {
                if (value.length == 0) {
                    return '输入的栏目目录不能为空哦';
                }
                if (value.length > 100) {
                    return '输入的栏目目录不能超过100个字符哦';
                }
            },
            default_index: function (value) {
                if (value.length == 0) {
                    return '输入的默认首页名称不能为空哦';
                }
                if (value.length > 20) {
                    return '输入的默认首页名称不能超过20个字符哦';
                }
            },
            temp_index: function (value) {
                if (value.length == 0) {
                    return '输入的模板封面不能为空哦';
                }
                if (value.length > 60) {
                    return '输入的模板封面不能超过60个字符哦';
                }
            },
            temp_list: function (value) {
                if (value.length == 0) {
                    return '输入的列表封面不能为空哦';
                }
                if (value.length > 60) {
                    return '输入的列表封面不能超过60个字符哦';
                }
            },
            temp_article: function (value) {
                if (value.length == 0) {
                    return '输入的文章封面不能为空哦';
                }
                if (value.length > 60) {
                    return '输入的文章封面不能超过60个字符哦';
                }
            },
            name_rule: function (value) {
                if (value.length == 0) {
                    return '输入的文章命名规则不能为空哦';
                }
                if (value.length > 60) {
                    return '输入的文章命名规则不能超过60个字符哦';
                }
            },
            list_rule: function (value) {
                if (value.length == 0) {
                    return '输入的列表命名规则不能为空哦';
                }
                if (value.length > 60) {
                    return '输入的列表命名规则不能超过60个字符哦';
                }
            },
            mode_name: function (value) {
                if (value.length == 0) {
                    return '输入的模板名称不能为空哦';
                }
                if (value.length > 60) {
                    return '输入的模板名称不能超过60个字符哦';
                }
            },
            description: function (value) {
                if (value.length > 200) {
                    return '输入的栏目描述不能超过200个字符哦';
                }
            }
        });
        //上传栏目封面图
        var uploadInst = upload.render({
            elem: '#upload_cover_img' //绑定元素
            , url: '/admin/uploadcolumncoverimg' //上传接口
            , done: function (data) {
                layer.msg(data.msg, {time: 1000}, function () {
                    $('#cover_img').val(data.data.url);
                    $('.cover_img').attr('src', data.data.url);
                })
            }
            , error: function () {
            }
        });
        $('#upload_cover_img').on('click'  ,function(){
            return false;
        });
        //监听提交
        form.on('submit(add)', function (data) {
            var url = '';
            $.sc.ajax(url, 'post', data.field, function (e) {
                layer.msg(e.msg, {time: 1000}, function () {
                    if (e.success) {
                        parent.location.href = "/admin/column/show.html";
                    }
                });
            });
            return false;
        });
        form.on('submit(edit)', function(data){
            //发送ajax进行数据提交
            var id = $('#typeid').val();
            var url = "/admin/alter_column.html?id=" + id;
            $.sc.ajax(url, 'post', data.field, function () {
                layer.msg(e.msg, {time: 1000}, function () {
                    if (e.success) {
                        parent.location.href = "/admin/column/show.html";
                    }
                });
            });
            return false;
        });
    });
});
