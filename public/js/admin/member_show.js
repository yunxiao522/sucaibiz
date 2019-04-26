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
        elem: '#member'                  //指定原始表格元素选择器（推荐id选择器）
        , height: vipTable.getFullHeight(32)    //容器高度
        , cols: [[                  //标题栏
            {type: 'checkbox', fixed: 'left'}
            , {field: 'id', title: 'ID', align:'center', width: 100}
            , {field: 'username', title: '账号', align:'center', width: 150}
            , {field: 'level', title: '等级', align:'center', width: 50}
            , {field: 'nickname', title: '昵称', align:'center', width: 120}
            , {field: 'type', title: '账号类型', align:'center', width: 120}
            , {field: 'create_time', title: '注册时间', align:'center', width: 180}
            , {field: 'status', title: '账号状态', align:'center', width: 100}
            , {field: 'email', title: '邮箱', align:'center', width: 280}
            , {field: 'phone', title: '手机号', align:'center', width: 150}
            , {field: 'experience', title: '经验值', align:'center', width: 100}
            , {field: 'gold', title: '拥有金币', align:'center', width: 150}
            , {field: 'qq', title: 'qq号', align:'center', width: 150}
            , {fixed: 'right', title: '操作', width:550, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
        ]]
        , id: 'dataCheck'
        , url: '/admin/get_member_list.html'
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
                    url: '/admin/del_member.html',
                    type: 'post',
                    data: {id: data.id},
                    beforeSend: function () {
                        loading = layer.load(0, {shade: false});
                    },
                    success: function (e) {
                        var data = JSON.parse(e);
                        layer.msg(data.msg, {time: 1000}, function () {
                            if (data.errorcode == 0) {
                                location.href = "/admin/member_show.html";
                            }
                        });
                    }
                })
            }, function () {
                layer.closeAll('dialog');
            });
        }else if(obj.event == 'detail'){
            //iframe窗
            layer.open({
                type: 2,
                title: false,
                closeBtn: 1, //不显示关闭按钮
                shade: [0],
                area: ['900px', '550px'],
                anim: 2,
                content: ['/admin/show_member_info.html?id='+data.id, 'no'], //iframe的url，no代表不显示滚动条
            });
        }else if(obj.event == 'alter'){
            //iframe窗
            layer.open({
                type: 2,
                title: '修改会员账号信息',
                shadeClose: true,
                shade: false,
                maxmin: true, //开启最大化最小化按钮
                area: ['893px', '600px'],
                content: '/admin/alter_member.html?id='+data.id //iframe的url
            });
        }else if(obj.event == 'log'){
            //iframe窗
            layer.open({
                type: 2,
                title: '会员登录日志',
                shadeClose: true,
                shade: 0.8,
                area: ['700px', '400px'],
                content: '/admin/show_member_login_log.html?id='+data.id //iframe的url
            });
        }else if(obj.event == 'level'){
            //iframe窗
            layer.open({
                type: 2,
                title: '修改会员等级',
                shadeClose: true,
                shade: 0.8,
                area: ['300px', '200px'],
                content: '/admin/mod_member_log.html?id='+data.id //iframe的url
            });
        }else if(obj.event == 'doc'){

        }else if(obj.event == 'comment'){

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
        area: ['100%', '100%'],
        content: '/admin/add_member.html' //iframe的url
    });
    return false;
});