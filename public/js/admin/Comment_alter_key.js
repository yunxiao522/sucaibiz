$(function () {
    layui.use(['form'], function() {
        var form = layui.form;
        //自定义验证规则
        form.verify({
            title: function(value){
                if(value.length < 5){
                    return '标题至少得5个字符啊';
                }
            }
        });

        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:'/admin/alter_Comment_key.html',
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