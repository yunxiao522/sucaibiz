layui.use(['table', 'form', 'vip_table'], function () {

    // 操作对象
    var form = layui.form
        , table = layui.table
        , vipTable = layui.vip_table;

    // 表格渲染
    tableIns = table.render({
        elem: '#list'                  //指定原始表格元素选择器（推荐id选择器）
        , height: vipTable.getFullHeight(80)    //容器高度
        , cols: [[                  //标题栏
            {checkbox: true, sort: true, fixed: true, space: true}
            , {field: 'id', title: 'ID', width: 80}
            , {field: 'name', title: '角色名称', width: 200 ,align:'center'}
            , {field: 'level', title: '等级值', width: 100 , align:'center'}
            , {field: 'description', title: '描述', width: 700 , align:'center'}
            , {field: 'create_time', title:'创建时间',width:200,align:'center'}
            , {field: 'alter_time', title:'修改时间',width:200,align:'center'}
            , {fixed: 'right', title: '操作', width: 350, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
        ]]
        , id: 'dataCheck'
        , url: '/admin/user_role_List.html'
        , method: 'get'
        , page: true
        , limits: [50, 100, 150, 200, 250]
        , limit: 50 //默认采用30
        , loading: true
    });
    $('#add').on('click' ,function(){
        layer.open({
            type: 2,
            title: '修改账号类型',
            shadeClose: true,
            shade: false,
            maxmin: false , //开启最大化最小化按钮
            area: ['600px', '500px'],
            content: '/admin/user_role_add.html'
        });
    });
    //自定义验证规则
    form.verify({
        name: function(value){
            if(value == ''){
                return '角色名称不能为空';
            }
            if(value.length > 20){
                return '角色名不能超过20个字符';
            }
        },description: function(value){
            if(value.length > 40){
                return '角色描述不能超过40个字符';
            }
        }
    });
    //监听提交
    form.on('submit(demo1)', function(data){
        $.ajax({
            url:'/admin/user_role_add.html',
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
                        window.location='/admin/user_role.html';
                        return false;
                    }
                });
            }
        });
        return false;
    });
    //监听提交
    form.on('submit(demo2)', function(data){
        $.ajax({
            url:'/admin/user_role_alter.html',
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
                        window.parent.location='/admin/user_role.html';
                        return false;
                    }
                });
            }
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
                    url: '/admin/user_role_del.html',
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
                title: '修改账号类型',
                shadeClose: true,
                shade: false,
                maxmin: false , //开启最大化最小化按钮
                area: ['600px', '500px'],
                content: '/admin/user_role_alter.html?id='+data.id
            });
        }
    });
});