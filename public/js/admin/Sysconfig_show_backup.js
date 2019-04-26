$(function () {
    $('#add').on('click', function () {
        $.ajax({
            url: '/admin/create_backup.html',
            type: 'post',
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                layer.close(loading);
                var data = JSON.parse(e);
                layer.msg(data.msg, {time: 3000});
            }
        });
        return false;
    });

    // layui方法
    layui.use(['table', 'form', 'layer', 'vip_table'], function () {

        // 操作对象
        var form = layui.form
            , table = layui.table
            , layer = layui.layer
            , vipTable = layui.vip_table
            , $ = layui.jquery;
        // 表格渲染
        var tableIns = table.render({
            elem: '#backup'                  //指定原始表格元素选择器（推荐id选择器）
            , height: vipTable.getFullHeight(32)    //容器高度
            , cols: [[                  //标题栏
                {type:'checkbox',fixed:'left'}
                , {field: 'id', title: 'ID', width: 80, align: 'center'}
                , {field: 'file_name', title: '备份名称', width: 300, align: 'center'}
                , {field: 'file_path', title: '储存位置', width: 400, align: 'center'}
                , {field: 'status', title: '备份状态', width: 100, align: 'center'}
                , {field: 'num', title: '回滚次数', width: 100, align: 'center'}
                , {field: 'size', title: '大小(单位:Mb)', width: 150, align: 'center'}
                , {field: 'create_time', title: '创建时间', width: 180, align: 'center'}
                , {field: 'roll_time', title: '最后一次回滚时间', width: 180, align: 'center'}
                , {fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
            ]]
            , id: 'dataCheck'
            , url: '/admin/get_backup_list.html'
            , method: 'get'
            , page: true
            , limits: [30, 60, 90, 150, 300]
            , limit: 30 //默认采用30
            , loading: false
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
                        url: '/admin/del_backup.html',
                        type: 'post',
                        data: {id: data.id ,is_oss: data.is_oss},
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
            }else if(obj.event == 'alter'){

            }
        });
        $('#refresh').on('click' ,function () {
            tableIns.reload({});
        });
    });
});