$(function () {
    layui.use(['form'], function() {
        var form = layui.form;
        //自定义验证规则
        form.verify({
            filter_name: function(value){
                if(value == ''){
                    return '过滤名称不能为空';
                }else if(value.length >20){
                    return '过滤名称不能超过20个字符';
                }
            }
            ,content:function (value) {
                if(value == ''){
                    return '输入的关键词不能为空';
                }else if(value.length >50){
                    return '输入的关键词不能超过50个字符';
                }
            }
        });

        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:'/admin/add_Comment_key.html',
                type:'post',
                data:$('#alter').serialize(),
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            window.location='/admin/show_Comment_key.html';
                            return false;
                        }
                    });
                }
            });
            return false;
        });

    });
});