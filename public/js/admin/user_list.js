// layui方法
layui.use(['table', 'form', 'layer', 'vip_table'], function () {

    // 操作对象
    var form = layui.form
        , table = layui.table
        , layer = layui.layer
        , vipTable = layui.vip_table
        , $ = layui.jquery;

    // 表格渲染
    tableIns = table.render({
        elem: '#dateTable'                  //指定原始表格元素选择器（推荐id选择器）
        , height: vipTable.getFullHeight(105)    //容器高度
        , cols: [[                  //标题栏
            {type:'checkbox',fixed:'left'}
            , {field: 'id', title: 'ID', width: 80}
            , {field: 'user_name', title: '账号', width: 200 ,align:'center'}
            , {field: 'nick_name', title: '昵称', width: 200 , align:'center'}
            , {field: 'real_name', title: '真实姓名', width: 200 , align:'center'}
            , {field: 'phone', title: '手机号', width: 200 , align:'center'}
            , {field: 'email', title: '邮箱', width: 280 , align:'center'}
            , {field: 'type', title: '账号类型', width: 200 , align:'center'}
            , {field: 'state', title: '账号状态', width: 200 , align:'center'}
            , {field: 'create_time', title: '添加时间', width: 200 , align:'center'}
            , {field: 'alter_time', title: '修改时间', width: 200 , align:'center'}
            , {fixed: 'right', title: '操作', width: 350, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
        ]]
        , id: 'dataCheck'
        , url: '/admin/user_list_json.html'
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
    // 刷新
    form.on('submit(demo2)', function (data) {
        tableIns.reload({
            where:data.field
        });
        return false;
    });
    //添加
    $('.add').on('click' ,function(){
        layer.open({
            type: 2,
            title: '添加管理员账号',
            shadeClose: true,
            shade: false,
            maxmin: false , //开启最大化最小化按钮
            area: ['500px', '600px'],
            content: '/admin/user/add.html'
        });
        return false;
    });
    //删除
    $('.del').on('click' ,function(){
        //询问框
        layer.confirm('确定删除吗？', {
            btn: ['确定','取消'] //按钮
        }, function(){
            layer.msg('的确很重要', {icon: 1});
        });
        return false;
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
                    url: '/admin/user/del.html',
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
            layer.open({
                type: 2,
                title: '修改管理员账号',
                shadeClose: true,
                shade: false,
                maxmin: false , //开启最大化最小化按钮
                area: ['500px', '600px'],
                content: '/admin/user/alter.html?id='+data.id
            });
            return false;
        }else if(obj.event == 'power'){
            layer.open({
                type: 2,
                title: '修改账号类型',
                shadeClose: true,
                shade: false,
                maxmin: false , //开启最大化最小化按钮
                area: ['300px', '200px'],
                content: '/admin/user/alterPower.html?id='+data.id
            });
            return false;
        }else if(obj.event == 'state'){
            var id = data.id;
            var state = data.state;
            if(state == '启用'){
                state = 2;
            }else{
                state = 1;
            }
            $.ajax({
                url: '/admin/user/alterState.html',
                type: 'post',
                data: {id:id,state:state},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    layer.close(loading);
                    var data = JSON.parse(e);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            tableIns.reload();
                        }
                    });
                }
            })
        }else if(obj.event == 'password'){
            layer.open({
                type: 2,
                title: '修改账号类型',
                shadeClose: true,
                shade: false,
                maxmin: false , //开启最大化最小化按钮
                area: ['400px', '300px'],
                content: '/admin/user/alterPassword.html?id='+data.id
            });
        }
    });
});