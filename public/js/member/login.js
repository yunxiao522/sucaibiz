$(function () {
    //检查浏览器是否支持cookie
    function check() {
        if (window.navigator.cookieEnabled) {
            return true;
        }
        else {
            return false;
        }
    }
    $('input[name="sm1"]').on('click', function () {
        var username = $('input[name="username"]').val();
        var password = $('input[name="password"]').val();
        if (username == '') {
            layer.tips('用户名不能为空', $('input[name="username"]'));
        } else if (password == '') {
            layer.tips('用户密码不能为空', $('input[name="password"]'));
        } else {
            var pwd = hex_sha1(password);
            //发送ajax请求
            $.ajax({
                url: '/login.html',
                type: 'post',
                data: {username: username, password: pwd ,cookie_status:check() ,url:$('input[name="url"]').val()},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    if (e == '') {
                        layer.close(loading);
                        layer.msg('登录失败', {time: 1000});
                    } else {
                        var data = JSON.parse(e);
                        layer.close(loading);
                        layer.msg(data.msg, {time: 1000}, function () {
                            if (data.errorcode != 1) {
                                parent.location.href = data.url;
                                window.parent.layer.closeAll();
                            }
                        });
                    }
                }
            });
        }
        return false;
    });
});