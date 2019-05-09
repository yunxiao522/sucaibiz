$(function () {

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
                , {field: 'id', title: 'ID', width: 150, align: 'center'}
                , {field: 'status', title: '队列状态', width: 150, align: 'center'}
                , {field: 'queue_name', title: '队列名称', width: 200, align: 'center'}
                , {field: 'content', title: '队列内容', width: 700, align: 'center'}
                , {field: 'create_time', title: '创建队列时间', width: 200, align: 'center'}
                , {field: 'out_time', title: '出队时间', width: 200, align: 'center'}
                , {field: 'error_info',title:'错误原因',width:400 ,align:'left'}
                , {field: 'queue_type', title: '队列类型', width: 150, align: 'center'}
            ]]
            , id: 'dataCheck'
            , url: '/admin/get_queue_list.html'
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