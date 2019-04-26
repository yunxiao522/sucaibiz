layui.use(['form'], function () {
    // 操作对象
    var form = layui.form;
});
//绑定登录按钮事件
$('#loging').on('click', function () {
    //验证用户名是否为空
    if ($('#username').val() == '') {
        layer.tips('输入的用户名不能为空', '#username', {tips: [3, 'orange']});
    } else if ($('#password').val() == '') {
        layer.tips('输入的密码不能为空', '#password', {tips: [3, 'orange']});
    } else {
        var username = $('input[name="username"]').val();
        var password = $('input[name="password"]').val();
        password = hex_sha1(password);
        $.ajax({
            url: '/admin/login.html',
            type: 'post',
            data: {username:username,password:password},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                layer.msg(data.msg, {time: 1000}, function () {
                    if (data.errorcode == 0) {
                        window.location.href = data.skip_url;
                    } else {

                    }
                });
            }
        })
    }
    return false;
});