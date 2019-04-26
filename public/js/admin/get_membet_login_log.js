uid = $('input[name="id"]').val();
layui.use(['tree', 'table', 'vip_table', 'layer' , 'vip_tab'], function () {

    // 操作对象
    var table = layui.table
        , vipTable = layui.vip_table
        , layer = layui.layer
        , $ = layui.jquery
        , viptab = layui.vip_tab;

    // 表格渲染
    var tableIns = table.render({
        elem: '#log'                  //指定原始表格元素选择器（推荐id选择器）
        , cols: [[                  //标题栏
            {type:'checkbox',fiexd:'left'}
            , {field: 'login_time', title: '时间', align:'center', width: 200}
            , {field: 'login_ip', title: '登录ip', align:'center', width: 150}
            , {field: 'browser', title: '浏览器', align:'center', width: 195}
            , {field: 'method', title: '登录方式', align:'center', width: 100}
        ]]
        , id: 'dataCheck'
        , url: '/admin/get_member_login_log.html?id='+uid
        , method: 'get'
        , page: true
        , limits: [20, 40, 60, 80, 100]
        , limit: 20 //默认采用30
        , loading: true
    });
});