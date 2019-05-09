// layui方法
layui.use(['tree', 'table', 'vip_table', 'layer' , 'vip_tab'], function () {

    // 操作对象
    var table = layui.table
        , vipTable = layui.vip_table
        , layer = layui.layer
        , $ = layui.jquery
        , viptab = layui.vip_tab;

    // 表格渲染
    var tableIns = table.render({
        elem: '#email'                  //指定原始表格元素选择器（推荐id选择器）
        , height: vipTable.getFullHeight(32)    //容器高度
        , cols: [[                  //标题栏
            {type: 'checkbox', fixed: 'left'}
            , {field: 'id', title: 'ID', align:'center', width: 100}
            , {field: 'nickname', title: '会员', align:'center', width: 150}
            , {field: 'phone', title: '手机号', align:'center', width: 150}
            , {field: 'title', title: '主题', align:'center', width: 250}
            , {field: 'sms_code', title: '短信模板编号', align:'center', width: 200}
            , {field: 'create_time', title: '发送时间', align:'center', width: 250}
            , {field: 'status', title:'状态' ,align:'center',width:100}
            , {fixed: 'right', title: '操作', width:200, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
        ]]
        , id: 'dataCheck'
        , url: '/admin/get_sms_list.html'
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
                    url: '/admin/del_email_info.html',
                    type: 'post',
                    data: {id: data.id},
                    beforeSend: function () {
                        loading = layer.load(0, {shade: false});
                    },
                    success: function (e) {
                        var data = JSON.parse(e);
                        layer.msg(data.msg, {time: 1000}, function () {
                            if (data.errorcode == 0) {
                                location.href = "/admin/show_email_list.html";
                            }
                        });
                    }
                })
            }, function () {
                layer.closeAll('dialog');
            });
        }else if(obj.event == 'details') {
            //iframe窗
            layer.open({
                type: 2,
                title: '邮件详细信息',
                shadeClose: true,
                shade: false,
                maxmin: true, //开启最大化最小化按钮
                area: ['893px', '400px'],
                content: ['/admin/show_sms_info.html?id=' + data.id, 'no'], //iframe的url，no代表不显示滚动条
            });
        }
    });
});
$('#add').on('click',function () {
    //iframe窗
    layer.open({
        type: 2,
        title: '创建会员账号',
        shadeClose: true,
        shade: 0.5,
        area: ['600px', '400px'],
        content: '/admin/add_integral_info.html' //iframe的url
    });
    return false;
});