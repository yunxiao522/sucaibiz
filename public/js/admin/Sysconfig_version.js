$(function(){
    // layui方法
    layui.use(['table', 'vip_tab', 'vip_table','form','laydate','layedit','laypage'], function () {
        // 操作对象
        var table = layui.table
            , viptab = layui.vip_tab
            , vipTable = layui.vip_table
            , form = layui.form
            , laydate = layui.laydate
            , layedit = layui.layedit
            , laypage = layui.laypage;
        //执行一个laydate实例
        laydate.render({
            elem: '#pubdate' //指定元素
        });
        var index = layedit.build('demo'); //建立编辑器
        form.verify({
            number:function (value) {
                if(value == ''){
                    return '版本号不能为空';
                }
                if(value.length > 10){
                    return '版本号长度不能超过10个字符';
                }
            },
            pubdate:function (value) {
                if(value == ''){
                    return '发布时间不能为空';
                }
            }
        });
        //监听提交
        form.on('submit(formDemo)', function(data){
            var item = data.field;
            item.content = layedit.getContent(index);
            $.ajax({
                url:'/admin/versions/add.html',
                type:'post',
                data:item,
                beforeSend:function () {
                    loading = layer.load(0, {shade: false});
                },
                success:function (res) {
                    var data = JSON.parse(res);
                    layer.msg(data.msg,{time:2000},function(){
                        if(data.success){
                           $('.layui-form').reset();
                            tableIns.reload();
                        }
                    })
                },
                complete:function () {
                    layer.close(loading);
                }
            });
            return false;
        });
        // 表格渲染
        var tableIns = table.render({
            elem: '#right'                  //指定原始表格元素选择器（推荐id选择器）
            , height: vipTable.getFullHeight(25)    //容器高度
            , cols: [[                  //标题栏
                {field: 'id', title: 'ID', align: 'center', width: 100, sort: true}
                , {field: 'number', title: '版本号', align: 'center', width: 150}
                , {field: 'title', title: '版本描述', align: 'center', width: 200}
                , {field: 'pubdate', title: '发布时间', align: 'center', width: 200, sort: true}
                , {field: 'create_time',title:'创建时间',align:'center',width:200}
                , {fixed: 'right', title: '操作', width: 200, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
            ]]
            , id: 'dataCheck'
            , url: '/admin/versions/getVersions.html'
            , parseData: function (res) {
                return {
                    'code': res.data.code,
                    'msg': res.msg,
                    'count': res.data.count,
                    "data": res.data.data
                }
            }
            , method: 'get'
            , page:{
                elem:'right'
            }
            , limits: [20, 40, 60, 80, 100]
            , limit: 20 //默认采用30
            , loading: true
            , done:function(){
                var page = $('.layui-table-page');
                $('.layui-table-box').append(page);
            }

        });
        table.on('tool(demo)', function (obj) {
            var data = obj.data;
            if (obj.event == 'del') {
                //删除
                //询问框
                layer.confirm('确定要删除吗？', {
                    btn: ['确定', '取消'] //按钮
                }, function () {
                    $.ajax({
                        url: '/admin/versions/del.html',
                        type: 'post',
                        data: {id: data.id},
                        beforeSend: function () {
                            loading = layer.load(0, {shade: false});
                        },
                        success: function (e) {
                            var data = JSON.parse(e);
                            layer.msg(data.msg, {time: 1000}, function () {
                                if (data.errorcode == 0) {
                                    tableIns.reload();
                                }
                            });
                        },
                        complete: function () {
                            layer.close(loading);
                        }
                    })
                }, function () {
                    layer.closeAll('dialog');
                });
            }else if(obj.event == 'edit'){
                //iframe层
                layer.open({
                    type: 2,
                    title: '修改版本信息',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['800px', '600px'],
                    content: '/admin/versions/edit.html?id=' + data.id, //iframe的url
                    end:function(){
                        tableIns.reload();
                    }
                });
            }else if(obj.event == 'show'){
                //iframe层
                layer.open({
                    type: 2,
                    title: '版本信息',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['100%', '100%'],
                    content:'/version.html', //iframe的url
                });
            }
        });
    });
});