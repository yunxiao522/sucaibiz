$(function () {
    //判断用户cookie是否存在
    if($.cookie('member_info') != null){
        var user_info = JSON.parse($.cookie('member_info'));
        var user_url = "/member/index.html?id="+user_info.uid;
        var user_name = user_info.username;
        $('.handle').html("<a href='"+user_url+"'>"+user_name+"</a> | <a href=\"/loginout.html\" id='loginout'>退出</a>")
        user_id = user_info.uid;
    }else{
        user_id = 0;
    }
    //添加点击量
    $.ajax({
        url:'/index/index/click.html',
        type:'post'
    });
    //获取文档点击数
    $.ajax({
        url:'/index/article/getArticleInfo.html',
        type:'get',
        data:{
            id:$('input[name="id"]').val()
        },success:function (res) {
            var e = JSON.parse(res);
            if(e.success){
                var click = e.data.click;
                $($('.related .author')[1]).text('浏览：'+click);
            }
        }
    });
    //发送增加文档点击率
    var id = $('#id').val();
    $.ajax({
        url: '/article_incr.html',
        type: 'post',
        data: {id: id}
    });
    //增加文档相关tag点击量
    $.ajax({
        url: '/index/tag/incrByArticleId.html',
        type: 'post',
        data: {id: id}
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