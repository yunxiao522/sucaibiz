$(function () {
    //判断用户cookie是否存在
    if($.cookie('member_info') != null){
        var user_info = JSON.parse($.cookie('member_info'));
        var user_url = "/member/index.html?id="+user_info.uid;
        var user_name = user_info.username;
        $('.handle').html("<a href='"+user_url+"'>"+user_name+"</a> | <a href=\"/loginout.html\">退出</a>");
        user_id = user_info.uid;
    }else{
        user_id = 0;
    }
    Sessionid = $.cookie('PHPSESSID');
    url = window.location.href;
    id = $('input[name="id"]').val();
    fromurl = document.referrer;
    //记录访问信息
    $.ajax({
        url:'/index/index/visit.html',
        type:'post',
        data:{
            id:id,
            type:2,
            uid:user_id,
            url:url,
            source:fromurl,
            sessionid:Sessionid
        }
    })
});