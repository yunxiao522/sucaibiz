$(function () {
    layui.use(['form', 'vip_tab','table'], function () {
        var form = layui.form,
            viptab = layui.vip_tab,
            table = layui.table;
        //自定义验证规则
        form.verify({
            class: function (value) {
                if (value == 0) {
                    return '必须要选择栏目分类哦';
                }
            },
            name: function (value) {
                if (value == '') {
                    return '输入的栏目不能为空哦';
                } else if (value.length > 15) {
                    return '输入的栏目名称不能超过15个字符哦';
                }
            },
            ico: function (value) {
                if (value == '') {
                    return '输入的图标不能为空哦';
                } else if (value.length > 100) {
                    return '输入的图标不能超过100个字符哦';
                }
            },
            url: function (value) {
                if (value.length > 50) {
                    return '输入的菜单跳转地址不能超过50个字符哦';
                }
            }
        });
        //监听class下拉选项
        form.on('select(class)', function (data) {
            var id = data.value;
            //发送ajax请求,获取对应分类的顶级菜单
            $.ajax({
                url: '/admin/sysconfig/getparentmenu.html',
                type: 'post',
                data: {class: id},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    layer.close(loading);
                    //清空父级菜单数据
                    $('select[name="parent_id"]').html('');
                    var data = JSON.parse(e);
                    $("<option value='0'>顶级菜单</option>").appendTo('select[name="parent_id"]');
                    if (data.errorcode == 0) {
                        //循环添加数据到父级菜单中
                        for (var i = 0; i < data.data.length; i++) {
                            var item = data.data[i];
                            $("<option value='" + item.id + "'>" + item.name + "</option>").appendTo('select[name="parent_id"]');
                        }
                    }
                    form.render('select');
                }
            });

        });
        //监听提交
        form.on('submit(demo1)', function (data) {
            $.ajax({
                url: '/admin/sysconfig_add_menu.html',
                type: 'post',
                data: data.field,
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    layer.close(loading);
                    var data = JSON.parse(e);
                    layer.msg(data.msg, {time: 2000}, function () {
                        if (data.errorcode == 0) {
                            parent.window.location = '/admin/sysconfig/menuManage.html';
                        }
                    })
                }
            });
            return false;
        });
        form.on('submit(demo2)', function (data) {
            $.ajax({
                url: '/admin/sysconfig/alterMenuInfo.html',
                type: 'post',
                data: data.field,
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    layer.close(loading);
                    var data = JSON.parse(e);
                    layer.msg(data.msg, {time: 2000}, function () {
                        if (data.errorcode == 0) {
                            parent.layer.closeAll();
                            parent.tableIns.reload();
                        }
                    })
                }
            });
            return false;
        });
    });

});