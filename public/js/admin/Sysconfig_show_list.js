$(function () {
    $('#group').on('click', function () {
        //iframe窗
        layer.open({
            type: 2,
            title: '系统参数分组维护',
            shadeClose: true,
            scrollbar: true,
            shade: 0.5,
            resize: false,
            area: ['700px', '600px'],
            content: '/admin/show_sysconfig_group.html' //iframe的url
        });
        return false;
    });

    //新增系统参数
    $('#add').on('click', function () {
        //iframe窗
        layer.open({
            type: 2,
            title: '新增系统参数',
            shadeClose: true,
            scrollbar: true,
            shade: 0.5,
            resize: false,
            area: ['700px', '500px'],
            content: '/admin/add_Sysconfig.html' //iframe的url
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
            elem: '#sysconfig'                  //指定原始表格元素选择器（推荐id选择器）
            , height: vipTable.getFullHeight(35)    //容器高度
            , cols: [[                  //标题栏
                {type:'checkbox',fixed:'left'}
                , {field: 'id', title: 'ID', width: 80 ,align:'center'}
                , {field: 'description', title: '参数说明', width: 300 ,align:'center'}
                , {field: 'sort', title: '排序', width: 100 ,align:'center'}
                , {field: 'name', title: '变量名', width: 250 ,align:'center'}
                , {field: 'type', title: '参数类型', width: 180 ,align:'center'}
                , {field: 'class', title: '分类', width: 180 ,align:'center'}
                , {field: 'create_time', title: '创建时间', width: 180 ,align:'center'}
                , {field: 'alter_time', title: '修改时间', width: 180 ,align:'center'}
                , {fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
            ]]
            , id: 'dataCheck'
            , url: '/admin/get_Sysconfig_list.html'
            , method: 'get'
            , page: true
            , limits: [30, 60, 90, 150, 300]
            , limit: 30 //默认采用30
            , loading: false
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
                console.log(res);

                //得到当前页码
                console.log(curr);

                //得到数据总量
                console.log(count);
            }
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
                        url: '/admin/del_Sysconfig_info.html',
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
            }else if(obj.event == 'alter'){
                //iframe窗
                layer.open({
                    type: 2,
                    title: '修改系统参数',
                    shadeClose: true,
                    scrollbar: true,
                    shade: 0.5,
                    resize: false,
                    area: ['700px', '500px'],
                    content: ['/admin/alter_Sysconfig_info.html?id='+data.id, 'no'] //iframe的url
                });
                return false;
            }
        });

    });
});