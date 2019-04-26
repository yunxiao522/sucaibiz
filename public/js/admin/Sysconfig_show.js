$(function () {
    $('#show').on('click' ,function () {
        layer.open({
            type: 2,
            title: '系统参数列表',
            shadeClose: true,
            shade: false,
            maxmin: true, //开启最大化最小化按钮
            area: ['100%', '100%'],
            content: '/admin/show_sysconfig_list.html'
        });
    });
    // layui方法
    layui.use(['table', 'form', 'layer', 'vip_table' , 'element'], function () {

        // 操作对象
        var form = layui.form
            , table = layui.table
            , layer = layui.layer
            , vipTable = layui.vip_table
            , $ = layui.jquery;
        var element = layui.element;

        //监听Tab切换，以改变地址hash值
        element.on('tab(docDemoTabBrief)', function(){
            delete cols;
            var hash = this.getAttribute('lay-id');
        });
        //监听提交
        form.on('submit(demo1)', function(data){

            console.log(data);
            $.ajax({
                url:'/admin/alter_Sysconfig_value.html',
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
                            window.location='/admin/sysconfig_show.html';
                            return false;
                        }
                    });
                }
            });

            return false;
        });
    });

});