layui.use(['form'], function(){
    var form = layui.form;
    //自定义验证规则
    form.verify({
        level: function(value){
            if(value == ''){
                return '请选择用户等级';
            }
        }
    });
    //监听提交
    form.on('submit(demo1)', function(data){
        $.ajax({
            url: '/admin/mod_member_log.html',
            type: 'post',
            data: $('#mod').serialize(),
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.msg(data.msg, {time: 1000}, function () {
                    layer.close(loading);
                    if (data.errorcode == 0) {
                        parent.location.href="/admin/member_show.html";
                    }
                });
            }
        })
        return false;
    });
});