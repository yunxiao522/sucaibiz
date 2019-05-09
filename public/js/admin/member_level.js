$('#add').on('click',function(){
    //iframe窗
    layer.open({
        type: 2,
        title: '新建会员等级',
        shadeClose: true,
        shade: 0.8,
        area: ['600px', '400px'],
        content: '/admin/add_member_level.html' //iframe的url
    });
    return false;
});
height = $('body').height() - $('#header').height();
// layui方法
layui.use(['table', 'vip_table', 'layer' , 'vip_tab'], function () {

    // 操作对象
    var table = layui.table
        , vipTable = layui.vip_table
        , layer = layui.layer
        , $ = layui.jquery
        , viptab = layui.vip_tab;

    // 表格渲染
    var tableIns = table.render({
        elem: '#level'                  //指定原始表格元素选择器（推荐id选择器）
        , height: vipTable.getFullHeight(32)    //容器高度
        , cols: [[                  //标题栏
            {type:'checkbox',fixed:'left'}
            , {field: 'id', title: 'ID', align:'center', width: 100}
            , {field: 'level_name', title: '等级名称', align:'center', width: 150}
            , {field: 'level_img', title: '图标', align:'center', width: 200,templet:'<div><img src="{{d.level_img}}" class="layui-table-link" style="width:122px;height:30px;"></img></div>'}
            , {field: 'ranks', title: '等级值', align:'center', width: 200}
            , {field: 'create_time', title: '创建时间', align:'center', width: 180}
            , {field: 'alter_time', title: '修改时间', align:'center', width: 180}
            , {fixed: 'right', title: '操作', width:300, align: 'center', toolbar: '#barOption'} //这里的toolbar值是模板元素的选择器
        ]]
        , id: 'dataCheck'
        , url: '/admin/get_member_level_list.html'
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
                    url: '/admin/del_member_level.html',
                    type: 'post',
                    data: {id: data.id},
                    beforeSend: function () {
                        loading = layer.load(0, {shade: false});
                    },
                    success: function (e) {
                        var data = JSON.parse(e);
                        layer.msg(data.msg, {time: 1000}, function () {
                            if (data.errorcode == 0) {
                                location.href = "/admin/member_level.html";
                            }
                        });
                    }
                })
            }, function () {
                layer.closeAll('dialog');
            });
        }else if(obj.event == 'detail'){
            //页面层
            layer.open({
                type: 2,
                title: '修改会员等级',
                shadeClose: true,
                shade: 0.8,
                area: ['600px', '400px'],
                content: '/admin/get_member_level.html?id='+data.id //iframe的url
            });
        }else if(obj.event == 'alter'){
            //iframe窗
            layer.open({
                type: 2,
                title: '修改会员等级',
                shadeClose: true,
                shade: 0.8,
                area: ['600px', '400px'],
                content: '/admin/alter_member_level.html?id='+data.id //iframe的url
            });
        }
    });
});