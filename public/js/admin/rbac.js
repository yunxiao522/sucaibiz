$(function () {
    layui.use(['table', 'form', 'vip_table'], function () {

        // 操作对象
        var form = layui.form
            , table = layui.table
            , vipTable = layui.vip_table;

        // 表格渲染
        tableIns = table.render({
            elem: '#dateTable'                  //指定原始表格元素选择器（推荐id选择器）
            , height: vipTable.getFullHeight(32)    //容器高度
            , cols: [[                  //标题栏
                {type:'checkbox',fexd:'left'}
                , {field: 'id', title: 'ID', width: 80}
                , {field: 'name', title: '模块名称', width: 200, align: 'center'}
                , {field: 'parent_id', title: '父级栏目', width: 150, align: 'center'}
                , {field: 'app', title: '所属应用', width: 120, align: 'center'}
                , {field: 'controller', title: '控制其名称', width: 150, align: 'center'}
                , {field: 'method', title: '方法名控制其名称', width: 150, align: 'center'}
                , {field: 'url', title: '访问地址', width: 200, align: 'center'}
                , {field: 'type', title: '类型', width: 150, align: 'center'}
                , {field: 'create_time', title: '添加时间', width: 200, align: 'center'}
                , {field: 'alter_time', title: '修改时间', width: 200, align: 'center'}
                , {fixed: 'right', title: '操作', width: 250, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
            ]]
            , id: 'dataCheck'
            , url: '/admin/user_model_list_json.html'
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
                        url: '/admin/user_model_del.html',
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
                    content: '/admin/user_model_alter.html?id='+data.id
                });
                return false;
            }
        });
        form.verify({
            name: function (value) {
                if (value == '') {
                    return '输入的模块名称不能为空';
                }
                if (value.length > 15) {
                    return '输入的模块名称不能超过15个字符';
                }
            },
            ico: function (value) {
                if( value.length >50){
                    return '输入的模块图标地址不能超过50个字符';
                }
            },
            app: function(value){
                if(value == ''){
                    return '输入的应用不能为空';
                }
            },
            controller:function(value){
                if(value.length >25){
                    return '输入的控制器名称不能超过25个字符';
                }
            },
            method:function(value) {
                if (value.length > 25) {
                    return '输入的方法名称不能超过25个字符';
                }
            },
            url:function(value){
                if(value.length > 100){
                    return '输入的链接地址不能超过100个字符';
                }
            },
            description:function(value){
                if(value.length > 100){
                    return '输入的描述信息不能超过100个字符';
                }
            }
        });
        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:'/admin/user_model_add.html',
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
                url:'/admin/user_model_alter.html',
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
        form.on('submit(demo3)', function(data){

            return false;
        });
        $('#add').on('click', function () {
            layer.open({
                type: 2,
                title: '添加模块',
                shadeClose: true,
                shade: false,
                maxmin: false, //开启最大化最小化按钮
                area: ['700px', '700px'],
                content: '/admin/user_model_add.html'
            });
            return false;
        });

        form.on('checkbox(power)', function(data){
            var son = data.elem.parentNode.parentNode.lastElementChild.children[0].getElementsByTagName('div');
            for(i = 0;i <son.length ;i++){
                if(data.elem.checked){
                    son[i].classList.add('layui-form-checked');
                }else{
                    son[i].classList.remove('layui-form-checked');
                }
            }

        });
        form.on('submit(demo4)', function(data){
            layer.alert(JSON.stringify(data.field), {
                title: '最终的提交信息'
            });
            return false;
        });


    });
});