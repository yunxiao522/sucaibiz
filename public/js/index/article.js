$(function () {
    //发送增加文档点击率
    var id = $('#id').val();
    $.ajax({
        url: '/article_incr.html',
        type: 'post',
        data: {id: id}
    });
    $.ajax({
        url: '/tag',
        type: 'post',
        data: {id: id}
    });
    //增加文档相关tag点击量
    $.ajax({
        url: '/index/tag/incrByArticleId.html',
        type: 'post',
        data: {id: id}
    });
    //判断用户cookie是否存在
    if ($.cookie('member_info') != null) {
        user_info = JSON.parse($.cookie('member_info'));
        var user_url = "/member/index.html?id=" + user_info.uid;
        var user_name = user_info.username;
        $('.handle').html("<a href='" + user_url + "'>" + user_name + "</a> | <a href=\"/loginout.html\">退出</a>")
        $('.info').html('<div class="info">签名：' + user_name + '</div><div style="float:right;"><button id = "btn" class="btn">写好了</button></div>');
        user_id = user_info.uid;
    }else{
        user_id = 0;
    }
    token = $('#token').val();
    //发送评论方法
    $('#btn').on('click', function () {
        var content = $('#comment').val();
        $.ajax({
            url: '/index/comment/addComment.html',
            type: 'post',
            data: {token: token, content: content, parent_id: 0, uid: user_info.uid},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                layer.msg(data.msg, {time: 1000}, function () {
                    if (data.errorcode == 0) {
                        location.reload();
                        return false;
                    }
                });
            }
        });
        return false;
    });
    //监听刷新评论按钮事件
    $('.refresh').on('click' ,function () {
        $('#comment_all').html('');
        getComment(1 ,10 ,true ,'comment');
        return false;
    });
    //根据评论排序方式获取评论
    $('input[name="order"]').on('change' ,function () {
        var val = $("input[name='order']:checked").val();
        if(val == 1){
            var order = true;
        }else{
            var order = false;
        }
        getComment(1 ,10 ,order ,'comment');
    });
    function getComment(page ,limit ,order ,type) {
        //获取评论列表
        $.ajax({
            url:'/index/comment/getcomment.html',
            type:'post',
            data:{token:token ,page:page ,limit:limit ,order:order ,type:type},
            success:function (e) {
                $('#comment_all').html(e);
            }
        });
    }
    getComment(1 ,10 ,true ,'comment');
    //检查浏览器是否支持cookie
    function check() {
        if (window.navigator.cookieEnabled) {
            return true;
        }
        else {
            return false;
        }
    }

    $('#login').on('click', function () {
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
                data: {username: username, password: pwd, cookie_status: check()},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    if (e == '') {
                        layer.close(loading);
                        layer.msg('登录失败', {time: 1000});
                    } else {
                        var data = JSON.parse(e);
                        console.log(data);
                        layer.close(loading);
                        layer.msg(data.msg, {time: 1000}, function () {
                            location.reload();
                        });
                    }
                }
            });
        }
        return false;
    });

    Sessionid = $.cookie('PHPSESSID');
    url = window.location.href;
    fromurl = document.referrer;
    //记录访问信息
    $.ajax({
        url:'/index/index/visit.html',
        type:'post',
        data:{
            id:id,
            type:1,
            uid:user_id,
            url:url,
            source:fromurl,
            sessionid:Sessionid
        }
    })
});