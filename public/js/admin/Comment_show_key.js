// layui方法
layui.use(['tree', 'table', 'vip_table', 'layer' , 'vip_tab'], function () {

    // 操作对象
    var table = layui.table
        , vipTable = layui.vip_table
        , layer = layui.layer
        , viptab = layui.vip_tab;

    // 表格渲染
    var tableIns = table.render({
        elem: '#commentkey'                  //指定原始表格元素选择器（推荐id选择器）
        , height: vipTable.getFullHeight(27)    //容器高度
        , cols: [[                  //标题栏
            {type:'checkbox',fixed:'left'}
            , {field: 'id', title: 'ID', align:'center', width: 80}
            , {field: 'filter_name', title: '过滤名', align:'center', width: 100}
            , {field: 'content', title: '过滤词', align:'center', width: 100}
            , {field: 'create_time', title: '创建时间', align:'center', width: 200}
            , {field: 'alter_time', title: '修改时间', align:'center', width: 200}
            , {fixed: 'right', title: '操作', width:150, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
        ]]
        , id: 'keylist'
        , url: '/admin/get_Comment_key.html'
        , method: 'get'
        , page: true
        , limits: [20, 40, 60, 80, 100]
        , limit: 20 //默认采用30
        , loading: true
    });

    //监听工具条
    table.on('tool(demo)', function (obj) {
        var data = obj.data;
        var url = window.location.href;
        if (obj.event == 'del') {
            //删除
            //询问框
            layer.confirm('确定要删除吗？', {
                btn: ['确定', '取消'] //按钮
            }, function () {
                $.ajax({
                    url: '/admin/del_Comment_key.html',
                    type: 'post',
                    data: {id: data.id},
                    beforeSend: function () {
                        loading = layer.load(0, {shade: false});
                    },
                    success: function (e) {
                        var data = JSON.parse(e);
                        layer.close(loading);
                        layer.msg(data.msg, {time: 1000}, function () {
                            if (data.errorcode == 0) {
                                var keyword = $('input[name="keyword"]').val();
                                tableIns.reload({
                                    url: '/admin/get_Comment_key.html?keyword='+keyword
                                });
                                return false;
                            }
                        });
                    }
                })
            }, function () {
                layer.closeAll('dialog');
            });
        }else if(obj.event == 'alter') {
            window.location = '/admin/alter_Comment_key.html?id='+data.id;
        }
    });
    //绑定搜索按钮事件
    $('#so').on('click',function () {
        var keyword = $('input[name="keyword"]').val();
        tableIns.reload({
            url: '/admin/get_Comment_key.html?keyword='+keyword
        });
        return false;
    });
    //绑定新增按钮事件
    $('#add').on('click' ,function () {
        window.location='/admin/add_Comment_key.html';
        return true;
    });
});
