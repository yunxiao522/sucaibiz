$(function () {
    $('.info').show();
    $('.infoface').hide();
    $('.msglist').hide();
    $('.hint').hide();
    //监听账号设置分组
    $('.seetingli').on('mouseover', function () {
        $(this).children('li').css('border-bottom', '2px #000000 solid');
        return false;
    });
    $('.seetingli').on('mouseout', function () {
        if (!$(this).children('li').hasClass('this')) {
            $(this).children('li').css('border-bottom', '2px #ffffff solid');
        }
        return false;
    });
    $('.seetingli').on('click', function () {
        //删除所有li的class属性中的this
        $(this).parent().find('li').removeClass('this');
        //向自己子元素的li添加class属性的this
        $(this).children('li').addClass('this');
        $(this).children('li').css('border-bottom', '');
        var type = this.dataset.type;
        if (type == 'info') {
            $('.info').show();
            $('.infoface').hide();
        } else if (type == 'face') {
            $('.info').hide();
            $('.infoface').show();
        }
        return false;
    });
    //绑定操作div中a链接事件
    $('.options a').on('click', function () {
        var type = this.dataset.type;
        if (type == 'email') {
            var url = '/member/accounts/alterEmail.html';
            var title = '修改邮箱';
        } else if (type == 'phone') {
            var url = '/member/accounts/alterPhone.html';
            var title = '修改手机号';
        }
        layer.open({
            type: 2,
            title: title,
            shadeClose: true,
            shade: 0.8,
            area: ['500px', '300px'],
            content: url
        });
        return false;
    });
    //绑定保存消息按钮事件
    $('#btn1').on('click', function () {
        //发送ajax请求
        $.ajax({
            url: '/member/accounts/setting.html',
            type: 'post',
            data: $("#infoform").serialize(),
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                layer.msg(data.msg, {time: 2000} ,function () {
                    if(data.errorcode == 0){
                        window.location.reload();
                    }
                });

            }
        });
        return false;

    });
    //绑定修改按钮事件
    $('.alterpasswordbtn').on('click', function () {
        //验证数据
        var oldpass = $('input[name="oldpass"]').val();
        if (oldpass == '') {
            layer.tips('输入的原始密码不能为空哦', $('input[name="oldpass"]'));
            return false;
        }
        var newpass = $('input[name="newpass"]').val();
        if (newpass == '') {
            layer.tips('输入的新密码不能为空哦', $('input[name="newpass"]'));
            return false;
        }
        var repass = $('input[name="repass"]').val();
        if (repass == '') {
            layer.tips('输入的重复密码不能为空哦', $('input[name="repass"]'));
            return false;
        }
        //对密码进行加密
        $('.pass').each(function () {
            $(this).val(hex_sha1($(this).val()));
        });

        //发送ajax链接
        $.ajax({
            url: '/member/accounts/password.html',
            type: 'post',
            data: $('#password').serialize(),
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                if (data.errorcode == 0) {
                    layer.msg(data.msg, {time: 2000}, function () {
                        window.location.reload();
                    });
                }
            }
        });
        return false;
    });
    //绑定绑定和解绑按钮事件
    $('.binging').on('click', function () {
        var ss = this.dataset.class;
        var type = this.dataset.type;
        //发送ajax请求
        $.ajax({
            url: '/member/accounts/relevance.html',
            type: 'post',
            data: {type: type, class: ss},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                if (data.errorcode == 0) {
                    layer.msg(data.msg, {time: 2000}, function () {
                        window.location.reload();
                    });
                } else if (data.errorcode == 2) {
                    layer.open({
                        type: 2,
                        title: data.title,
                        shadeClose: true,
                        shade: false,
                        maxmin: true, //开启最大化最小化按钮
                        area: ['893px', '600px'],
                        content: data.url,
                        end: function () {
                            location.reload();
                        }
                    });
                }
            }
        })
    });
    //绑定我的内容div事件
    //搜索按钮
    $('.search').on('click', function () {
        window.location.href = '/search.html';
    });
    $('.search').on('mouseover' ,function () {
       $(this).children('img').attr('src' ,'/public/png/search1-color.png');
    });
    $('.search').on('mouseout' ,function () {
        $(this).children('img').attr('src' ,'/public/png/search1.png');
    });
    $('.msg').on('mouseover' ,function () {
        $(this).children('img').attr('src' ,'/public/png/msg1-color.png');
    });
    $('.msg').on('mouseout' ,function () {
        $(this).children('img').attr('src' ,'/public/png/msg1.png');
    });
    //消息按钮
    $('.msg').on('click', function () {
        var type = this.dataset.type;
        if(type == 'hide'){
            $('.msg').css('background' ,'#393D49');
            $('.msglist').show();
            this.dataset.type = 'show';
        }else if(type == 'show'){
            $('.msg').css('background' ,'#13A38C');
            $('.msglist').hide();
            this.dataset.type = 'hide';
        }
    });
    $(document).mouseup(function(e){
        var _con = $('.msglist'); // 设置目标区域
        if(!_con.is(e.target) && _con.has(e.target).length === 0){
            $(".msglist").hide();
            $('.msg').css('background' ,'#13A38C');
        }
    });
    $('.msg').on('hover',function () {
        $('.msg').css('background' ,'#393D49');
    });
    layui.use(['upload'], function () {
        var upload = layui.upload;
        //绑定上传头像事件
        //执行实例
        var uid = $('input[name="id"]').val();
        var uploadInst = upload.render({
            elem: '#uploadface' //绑定元素
            , url: '/member/accounts/uploadFace.html' //上传接口
            , data: {uid: uid}
            , accept: 'images'
            , acceptMime: 'image/*'
            , done: function (res) {
                layer.msg(res.msg, {time: 3000}, function () {
                    if (res.errorcode == 0) {
                        $('.faceimg').attr('src', res.url);
                    }
                })
            }
            , error: function () {
                //请求异常回调
            }
        });
        $('#uploadface').on('click', function () {
            return false;
        })
    });


});