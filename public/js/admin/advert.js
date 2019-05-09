$(function(){
    $('#add').on('click' ,function(){
        layer.open({
            type: 2,
            title: '添加模块',
            shadeClose: true,
            shade: false,
            maxmin: false, //开启最大化最小化按钮
            area: ['700px', '700px'],
            content: '/admin/advert_add.html'
        });
        return false;
    });

    layui.use(['table', 'form', 'vip_table'], function () {

        // 操作对象
        var form = layui.form
            , table = layui.table
            , vipTable = layui.vip_table;

        //自定义验证规则
        form.verify({
            ad_name: function(value){
                if(value.length == 0){
                    return '输入的广告名称不能为空';
                }
                if(value.length > 20){
                    return '输入的广告名称不能超过20个字符';
                }
            },
            class:function (value) {
                if(value.length == 0){
                    return '输入的分组名不能为空';
                }
                if(value.length > 20){
                    return '输入的广告分组不能超过20个字符';
                }
            },
            width:function (value) {
                if(value < 0){
                    return '输入的宽度不能小于0';
                }
            },
            height:function (value) {
                if(value <0){
                    return '输入的高度不能小于0';
                }
            },
            palcename:function(value){
                if(value.length > 30){
                    return '输入的广告说明不能超过30个字符';
                }
            }

        });
        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:'/admin/advert_add.html',
                type:'post',
                data:data.field,
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            parent.tableIns.reload();
                            parent.layer.closeAll();

                        }
                    });
                }
            });
            return false;
        });

        form.on('submit(demo2)', function(data){
            $.ajax({
                url:'/admin/advert_alter.html',
                type:'post',
                data:data.field,
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            parent.tableIns.reload();
                            parent.layer.closeAll();

                        }
                    });
                }
            });
            return false;
        });
        // 表格渲染
        tableIns = table.render({
            elem: '#dateTable'                  //指定原始表格元素选择器（推荐id选择器）
            , height: vipTable.getFullHeight(32)    //容器高度
            , cols: [[                  //标题栏
                {type:'checkbox',fixed:'left'}
                , {field: 'id', title: 'ID', width: 80}
                , {field: 'ad_name', title: '广告名称', width: 200, align: 'center'}
                , {field: 'width', title: '宽度', width: 150, align: 'center'}
                , {field: 'height', title: '高度', width: 120, align: 'center'}
                , {field: 'class', title: '分类', width: 150, align: 'center'}
                , {field: 'status', title: '状态', width: 150, align: 'center'}
                , {field: 'palcename', title: '说明', width: 250, align: 'center'}
                , {field: 'create_time', title: '添加时间', width: 200, align: 'center'}
                , {field: 'alter_time', title: '修改时间', width: 200, align: 'center'}
                , {fixed: 'right', title: '操作', width: 300, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
            ]]
            , id: 'dataCheck'
            , url: '/admin/advert_get_list.html'
            , parseData:function (res) {
                return {
                    'code':res.data.code,
                    'msg':res.msg,
                    'count':res.data.count,
                    "data":res.data.data
                }
            }
            , method: 'get'
            , page: true
            , limits: [50, 100, 150, 200, 250]
            , limit: 50 //默认采用30
            , loading: true
        });

        //监听工具条
        table.on('tool(demo)', function (obj) {
            var data = obj.data;
            if (obj.event == 'del') {
                //删除
                //询问框
                layer.confirm('确定要删除吗？', {
                    btn: ['确定', '取消'] //按钮
                }, function () {
                    $.ajax({
                        url: '/admin/advert_del.html',
                        type: 'post',
                        data: {id: data.id},
                        beforeSend: function () {
                            loading = layer.load(0, {shade: false});
                        },
                        success: function (e) {
                            layer.close(loading);
                            var data = JSON.parse(e);
                            layer.msg(data.msg, {time: 1000}, function () {
                                if (data.errorcode == 0) {
                                    obj.del();
                                }
                            });
                        }
                    })
                }, function () {
                    layer.closeAll('dialog');
                });
            } else if (obj.event == 'edit') {
                layer.open({
                    type: 2,
                    title: '添加模块',
                    shadeClose: true,
                    shade: false,
                    maxmin: false, //开启最大化最小化按钮
                    area: ['700px', '700px'],
                    content: '/admin/advert_alter.html?id='+data.id
                });
                return false;
            }
        });
    });
});