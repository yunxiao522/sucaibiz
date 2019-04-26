$(function () {
   //添加点击量
   $.ajax({
       url:'/index/index/click.html',
       type:'post'
   });
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
});