$(function () {
    layui.use(['tree', 'table', 'vip_table', 'layer' , 'vip_tab'], function () {

        // 操作对象
        var table = layui.table
            , vipTable = layui.vip_table
            , layer = layui.layer
            , $ = layui.jquery
            , viptab = layui.vip_tab;

        // 表格渲染
        var tableIns = table.render({
            elem: '#plan'                  //指定原始表格元素选择器（推荐id选择器）
            , height: vipTable.getFullHeight(32)    //容器高度
            , cols: [[                  //标题栏
                {type: 'checkbox', fixed: 'left'}
                , {field: 'id', title: 'ID', align:'center', width: 100}
                , {field: 'name', title: '计划名称', align:'center', width: 250}
                , {field: 'fun_name', title: '函数名称', align:'center', width: 250}
                , {field: 'status', title: '执行状态', align:'center', width: 100}
                , {field: 'execute_time', title: '执行时间', align:'center', width: 250}
                , {field: 'execute_type', title: '执行类型', align:'center', width: 100}
                , {field: 'num', title: '执行次数', align:'center', width: 100}
                , {field: 'end_time', title: '最后一次执行时间', align:'center', width: 200}
                , {field: 'condition', title: '状态', align:'center', width: 100}
                , {field: 'create_time', title: '创建时间', align:'center', width: 250}
                , {field: 'alter_time', title: '修改时间', align:'center', width: 250}
                , {fixed: 'right', title: '操作', width:200, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
            ]]
            , id: 'dataCheck'
            , url: '/admin/get_plan_list.html'
            , method: 'get'
            , page: true
            , limits: [20, 40, 60, 80, 100]
            , limit: 20 //默认采用30
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
                        url: '/admin/del_plan.html',
                        type: 'post',
                        data: {id: data.id},
                        beforeSend: function () {
                            loading = layer.load(0, {shade: false});
                        },
                        success: function (e) {
                            var data = JSON.parse(e);
                            layer.msg(data.msg, {time: 1000}, function () {
                                if (data.errorcode == 0) {
                                    location.href = "/admin/show_plan_list.html";
                                }
                            });
                        }
                    })
                }, function () {
                    layer.closeAll('dialog');
                });
            }else if(obj.event == 'alter') {
                //iframe窗
                layer.open({
                    type: 2,
                    title: '修改任务计划',
                    shadeClose: true,
                    shade: false,
                    maxmin: true, //开启最大化最小化按钮
                    area: ['100%', '100%'],
                    content: ['/admin/alter_plan.html?id=' + data.id, 'no'], //iframe的url，no代表不显示滚动条
                });
            }
        });
    });
    $('#add').on('click', function () {
        layer.open({
            type: 2,
            title: '新增任务计划',
            shadeClose: true,
            shade: false,
            maxmin: true, //开启最大化最小化按钮
            area: ['100%', '100%'],
            content: '/admin/add_plan.html'
        });
    });
});