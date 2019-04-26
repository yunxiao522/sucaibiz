$(function () {
    verfily_status = 0;
    username_status = 0;
    email_status = 0;
    $('input[name="verfily"]').on('blur', function () {
        var verfily = $('input[name="verfily"]').val();
        //发送ajax验证验证码
        $.ajax({
            url: '/checkverfily.html',
            type: 'post',
            data: {verfily: verfily},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                if(data.errorcode == 0){
                    verfily_status = 1;
                }
                layer.msg(data.msg, {time: 1000});
            }
        });
    });
    //检查会员账号是否可用
    $('input[name="username"]').on('blur' ,function () {
        var username = $('input[name="username"]').val();
        //发送请求验证用户名是否可用
        $.ajax({
            url: '/checkusername.html',
            type: 'post',
            data: {username: username},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                if(data.errorcode == 1){
                    layer.tips(data.msg, 'input[name="username"]');
                }else{
                    username_status = 1;
                }
            }
        });
    });
    //检查会员邮箱是否是唯一
    $('input[name="email"]').on('blur' ,function () {
        var email = $('input[name="email"]').val();
        //发送请求验证用户名是否可用
        $.ajax({
            url: '/checkemail.html',
            type: 'post',
            data: {email: email},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                if(data.errorcode == 1){
                    layer.tips(data.msg, 'input[name="email"]');
                }else{
                    email_status = 1;
                }
            }
        });
    });
    $('input[name="btn"]').on('click', function () {
        var deal = $('input[name="deal"]').is(':checked');
        var username = $('input[name="username"]').val();
        var email = $('input[name="email"]').val();
        var password = $('input[name="password"]').val();
        var vpassword = $('input[name="vpassword"]').val();
        //检查用户名
        if(username_status == 0){
            layer.tips('账号重复，请更换账号', 'input[name="username"]');
            return false;
        }
        //检查邮箱
        if(email_status == 0){
            layer.tips('一个邮箱只能注册一个账号', 'input[name="email"]');
            return false;
        }
        //检查验证码
        var verfily = $('input[name="verfily"]').val();
        if(verfily == ''){
            layer.tips('请输入验证码', 'input[name="verfily"]');
        }
        if(verfily_status == 0){
            layer.tips('验证码输入不正确，请检查', 'input[name="verfily"]');
            return false;
        }

        //验证输入的数据不能为空
        if(username == '' || username.length >20){
            layer.tips('输入的用户名不能为空，并且不能超过20个字符哦','input[name="username"]');
        }else if(email == '' || !email.match(/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((\.[a-zA-Z0-9_-]{2,3}){1,2})$/)){
            layer.tips('输入的邮箱不能为空，并且要符合邮箱格式','input[name="email"]');
        }else if(password == '' || password.length < 6){
            layer.tips('输入的密码不能为空,并且长度至少要6位','input[name="password"]');
        }else if(vpassword == ''){
            layer.tips('重复输入的密码不能为空','input[name="vpassword"]');
        }else if (deal) {
            if (password != vpassword) {
                layer.msg('两次输入的密码不一致', {time: 3000});
            } else {
                if(verfily_status == 1){
                    //设置按钮不可点击,避免重复提交数据
                    $('input[name="btn"]').attr('disabled' ,true);
                    //发送ajax请求
                    $.ajax({
                        url: '/register.html',
                        type: 'post',
                        data: {username:username,email:email,password:hex_sha1(password),vpassword:hex_sha1(vpassword)},
                        beforeSend: function () {
                            loading = layer.load(0, {shade: false});
                        },
                        success: function (e) {
                            var data = JSON.parse(e);
                            layer.close(loading);
                            layer.msg(data.msg, {time: 1000} ,function () {
                                if(data.errorcode == 0){
                                    window.location.href=data.url;
                                }else{
                                    $('input[name="btn"]').attr('disabled' ,false);
                                }
                            });

                        }
                    });
                }else{
                    layer.msg('输入的验证码不正确',{time:2000});
                }
            }
        } else {
            layer.msg('请先同意协议内容', {time: 3000});
        }
    });

});