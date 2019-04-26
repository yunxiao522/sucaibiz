$(function () {
    phone_verfiy = 0;
    nickname_status = 0;
    phone_status = 0;
    realname_status = 0;
    $('#btn').on('click', function () {
        //发送短信验证码方法
        var token = $('input[name="token"]').val();
        var phone = $('input[name="phone"]').val();
        var uid = $('input[name="id"]').val();
        var phone_rule = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1})|(16[0-9]{1}))+\d{8})$/;
        if (phone == '') {
            layer.tips('输入的手机号不能为空哦.', 'input[name="phone"]');
        } else if (!phone_rule.test(phone)) {
            layer.tips('输入的手机号格式不正确.', 'input[name="phone"]');
        } else if (phone.length != 11) {
            layer.tips('输入的手机号格式不正确.', 'input[name="phone"]');
        } else {

            $.ajax({
                url: '/memberSms',
                type: 'post',
                data: {token: token, phone: phone ,uid:uid},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    layer.msg(data.msg, {time: 1000});
                    if(data.errorcode == 0){
                        settime(this);
                    }
                }
            });
        }
        return false;
    });
    //验证手机验证码是否正确
    $('input[name="phone-code"]').on('blur', function () {
        var token = $('input[name="token"]').val();
        var verfiycode = $('input[name="phone-code"]').val();
        if (verfiycode == '') {
            layer.tips('输入接收到的手机验证码.', 'input[name="phone-code"]');
        } else {
            $.ajax({
                url: '/checkPhoneVerfiy.html',
                type: 'post',
                data: {token: token, verfiycode: verfiycode},
                success: function (e) {
                    var data = JSON.parse(e);
                    if (data.errorcode == 1) {
                        $('.verfiy').html('<i class="i-error"></i>手机验证码输入不正确');
                        phone_verfiy = 1;
                    } else {
                        $('.verfiy').html('<span class="success">手机验证码输入正确</span>');
                        phone_verfiy = 0;
                    }
                }
            })
        }

    });
    //检查昵称是否唯一
    $('input[name="nickname"]').on('blur', function () {
        var nickname = $('input[name="nickname"]').val();
        if (nickname.length > 20) {
            $('.nickname').html('<span class="hint">输入的昵称不能超过20个字符哦</span>');
            nickname_status = 1;
        } else if (nickname == '') {
            $('.nickname').html('<span class="error">输入的昵称不能为空哦</span>');
            nickname_status = 1;
        } else {
            //发送ajax获取检查结果
            $.ajax({
                url: '/checknickname.html',
                type: 'post',
                data: {nickname: nickname},
                success: function (e) {
                    var data = JSON.parse(e);
                    if (data.errorcode == 1) {
                        $('.nickname').html('<span class="error">输入的昵称已经被使用，请更换</span>');
                        nickname_status = 1;
                    } else {
                        $('.nickname').html('<span class="success">输入的你昵称可以使用</span>');
                        nickname_status = 0;
                    }
                }
            });
        }

    });
    //显示昵称规则
    $('input[name="nickname"]').on('focus', function () {
        $('.nickname').html('<span class="hinit">可使用随意字符，但不能超过20个字符</span>');
    });
    //显示真实姓名规则
    $('input[name="realname"]').on('focus', function () {
        $('.realname').html('<span class="hinit">输入的真实姓名不能超过20个字符</span>');
    });
    //验证输入的真实姓名
    $('input[name="realname"]').on('blur', function () {
        var realname = $('input[name="realname"]').val();
        if (realname.length > 20) {
            realname_status = 1;
            $('.realname').html('<span class="error">输入的真实姓名不能超过20个字符</span>');
        } else if (realname == '') {
            realname_status = 1;
            $('.realname').html('<span class="error">输入的真实姓名不能为空</span>');
        } else {
            realname_status = 0;
            $('.realname').html('<span class="success">输入正确</span>');
        }
    });
    //显示手机号码规则
    $('input[name="phone"]').on('focus', function () {
        $('.phone').html('<span class="hinit">请输入11位大陆手机号码</span>');
    });
    //验证手机号码
    $('input[name="phone"]').on('blur', function () {
        var phone = $('input[name="phone"]').val();
        var phone_rule = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1})|(16[0-9]{1}))+\d{8})$/;
        if (phone.length != 11) {
            $('.phone').html('<span class="error">输入的手机号码要为11位大陆手机号</span>');
        } else if (!phone_rule.test(phone)) {
            $('.phone').html('<span class="error">请输入正确的手机号</span>');
        } else {
            //验证手机号码是否唯一
            $.ajax({
                url: '/checkphone.html',
                type: 'post',
                data: {phone:phone},
                success: function (e) {
                    var data = JSON.parse(e);
                    if (data.errorcode == 1) {
                        $('.phone').html('<span class="error">输入的手机号已经被使用，请更换</span>');
                        phone_status = 1;
                    } else {
                        $('.phone').html('<span class="success">输入正确</span>');
                        phone_status = 0;
                    }
                }
            });
        }
    });
    $('input').on('blur', function () {
        $(this).parent('div').addClass('b');
    });
    //发送短信按钮事件
    var countdown = 120;
    function settime() {
        if (countdown == 0) {
            $('#btn').attr("disabled", false);
            $('#btn').val("获取验证码");
            countdown = 120;
            return;
        } else {
            $('#btn').attr("disabled", true);
            $('#btn').val("发送成功，" + countdown + "秒后再试");
            countdown--;
        }
        ;
        setTimeout(function (obj) {
            settime(obj)
        }, 1000);
    }

    $('#btn2').on('click', function () {
        $("#btn2").attr("disabled", true);
        //发送ajax提交数据
        if(nickname_status !=0){
            $('.nickname').html('<span class="error">请检查输入的昵称</span>');
        }else if(realname_status !=0){
            $('.realname').html('<span class="error">请检查输入的真实姓名</span>');
        }else if(phone_status !=0){
            $('.phone').html('<span class="error">请检查输入的手机号</span>');
        }else if(phone_verfiy !=0){
            $('.verfiy').html('<span class="error">请检查输入的手机验证码</span>');
        }else{
            $.ajax({
                url:'',
                type:'post',
                data:$('#register2').serialize(),
                success: function (e) {
                    if(e == ''){
                        layer.close(loading);
                        layer.msg('更新账号信息成功',{time:1000});
                    }else{
                        var data = JSON.parse(e);
                        console.log(data);
                        layer.close(loading);
                        layer.msg(data.msg, {time: 1000}, function () {
                            if(data.errorcode != 1){
                                window.location.href = data.url;
                            }
                        });
                    }
                }
            });
        }
        return false;
    });
})
;