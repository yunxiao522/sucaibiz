$(function () {
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
            elem: '#log'                  //指定原始表格元素选择器（推荐id选择器）
            , height: vipTable.getFullHeight(32)    //容器高度
            , cols: [[                  //标题栏
                {type:'checkbox',fixed:'left'}
                , {field: 'id', title: 'ID', width: 80, align: 'center'}
                , {field: 'username', title: '会员账号', width: 200, align: 'center'}
                , {field: 'nickname', title: '会员昵称', width: 200, align: 'center'}
                , {field: 'method', title: '登录方式', width: 150, align: 'center'}
                , {field: 'login_ip', title: '登录ip地址', width: 200, align: 'center'}
                , {field: 'browser', title: '使用的浏览器', width: 200, align: 'center'}
                , {field: 'login_time', title: '登录时间', width: 200, align: 'center'}
            ]]
            , id: 'dataCheck'
            , url: '/admin/get_login_log.html'
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
            , limits: [30, 60, 90, 150, 300]
            , limit: 30 //默认采用30
            , loading: false
        });
    });
});