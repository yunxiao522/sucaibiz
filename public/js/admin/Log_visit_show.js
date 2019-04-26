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
                {field:'id',title:'id',width:100,align:'center'}
                , {field: 'nickname', title: '会员名称', width: 100, align: 'center'}
                , {field: 'ip', title: '访问者ip地址', width: 150, align: 'center'}
                , {field: 'column_title', title: '所属栏目', width: 100, align: 'center'}
                , {field: 'column_id', title: '所属id', width: 80, align: 'center'}
                , {field: 'article_title', title: '文档名称', width: 300, align: 'center'}
                , {field: 'article_id', title: '文档id', width: 80, align: 'center'}
                , {field: 'url', title: '访问的地址', width: 350, align: 'center'}
                , {field: 'source', title: '入口地址', width: 350, align: 'center'}
                , {field: 'device', title: '使用的设备', width: 100, align: 'center'}
                , {field: 'create_time', title: '访问时间', width: 200, align: 'center'}
            ]]
            , id: 'dataCheck'
            , url: '/admin/log/getVisit.html'
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
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
                console.log(res);

                //得到当前页码
                console.log(curr);

                //得到数据总量
                console.log(count);
            }
        });
        $('.refresh').on('click',function () {
            var keyword = $('input[name="keyword"]').val();
            tableIns.reload({
                where:{
                    keyword:keyword
                }
            });
        });
    });
});