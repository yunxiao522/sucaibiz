// layui方法
layui.use(['tree', 'table', 'vip_table', 'layer', 'vip_tab'], function () {

    // 操作对象
    var table = layui.table
        , vipTable = layui.vip_table
        , layer = layui.layer
        , viptab = layui.vip_tab;

    // 表格渲染
    var tableIns = table.render({
        elem: '#email'                  //指定原始表格元素选择器（推荐id选择器）
        , height: vipTable.getFullHeight(32)    //容器高度
        , cols: [[                  //标题栏
            {type:'checkbox',fixed:'left'}
            , {field: 'id', title: 'ID', align: 'center', width: 100}
            , {field: 'aid', title: '文档id', align: 'center', width: 100}
            , {field: 'title', title: '文档', align: 'center', width: 250}
            , {field: 'nickname', title: '会员昵称', align: 'center', width: 150}
            , {field: 'content', title: '评论内容', align: 'center', width: 600}
            , {field: 'praiser', title: '点赞', align: 'center', width: 70}
            , {field: 'oppose', title: '反对', align: 'center', width: 70}
            , {field: 'inform', title: '举报', align: 'center', width: 70}
            , {field: 'status', title: '状态', align: 'center', width: 70}
            , {field: 'create_time', title: '评论时间', align: 'center', width: 250}
            , {field: 'alter_time', title: '修改时间', align: 'center', width: 250}
            , {fixed: 'right', title: '操作', width: 400, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
        ]]
        , id: 'dataCheck'
        , url: '/admin/Comment/getCommentList.html'
        , parseData:function (res) {
            return {
                'code':res.data.code,
                'msg':res.msg,
                'count':res.data.count,
                "data":res.data.data
            }
        }
        , method: 'post'
        , page: true
        , limits: [20, 40, 60, 80, 100]
        , limit: 20 //默认采用30
        , loading: true
        , done: function (e) {
            //回调函数
            page = e.page;
            limit = e.limit;
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
                    url: '/admin/del_Comment_Info.html',
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
        } else if (obj.event == 'filter') {
            if (data.status == 1) {
                var status = 2;
            } else {
                var status = 1;
            }
            $.ajax({
                url: '/admin/alter_Comment_Status.html',
                type: 'post',
                data: {id: data.id, status: status},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            tableIns.reload({
                                    url: '/admin/Comment/getCommentList.html',
                                    where: {},
                                    page: {
                                        curr: 2
                                    }
                                }
                            );
                        }
                    });
                }
            });
        } else if (obj.event == 'reply') {
            tableIns.reload({
                where: {
                    'parent_id': data.id
                }
            });
        } else if (obj.event == 'uid') {
            tableIns.reload({
                where: {
                    'uid': data.uid
                }
            });
        } else if (obj.event == 'aid') {
            tableIns.reload({
                where: {
                    'aid': data.aid
                }
            });
        }
    });
    $('#add').on('click', function () {
        //iframe窗
        layer.open({
            type: 2,
            title: '评论关键词维护',
            shadeClose: true,
            scrollbar: true,
            shade: 0.5,
            resize: false,
            area: ['700px', '600px'],
            content: '/admin/show_Comment_key.html' //iframe的url
        });
        return false;
    });
    $('#filter').on('click', function () {
        tableIns.reload({
            where: {
                'status': 2,
            }
        });
    });
    $("#normal").on('click', function () {
        tableIns.reload({
            where: {
                'status': 1
            }
        });
    });
    $('#refresh').on('click', function () {
        tableIns.reload({
            url: '/admin/Comment/getCommentList.html',
            where: {}
        });
    });
    $('#keycache').on('click', function () {
        $.ajax({
            url: '/admin/refresh_Comment_key_cache.html',
            type: 'post',
            data: {},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                layer.close(loading);
                var data = JSON.parse(e);
                layer.msg(data.msg, {time: 1000}, function () {});
            }
        })
    });
    $('#delmore').on('click', function () {
        var checkstatus = table.checkStatus('dataCheck');
        var ids = [];
        if (checkstatus.data.length == 0) {
            layer.msg('请选择要删除的评论', {time: 2000});
        } else {
            for (var i = 0; i < checkstatus.data.length; i++) {
                ids.push(checkstatus.data[i].id);
            }
            $.ajax({
                url: '/admin/del_More_Comment_Info.html',
                type: 'post',
                data: {ids: ids},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    layer.close(loading);
                    var data = JSON.parse(e);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            location.href = "/admin/article/show.html";
                        }
                    });
                }
            })
        }
    });
});
