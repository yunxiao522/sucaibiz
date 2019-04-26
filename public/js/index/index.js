
$(function () {
    //记录访问信息
    Sessionid = $.cookie('PHPSESSID');
    url = window.location.href;
    fromurl = document.referrer;
    $.ajax({
        url:'/index/index/visit.html',
        type:'post',
        data:{
            id:0,
            type:3,
            uid:user_id,
            url:url,
            source:fromurl,
            sessionid:Sessionid
        }
    })
    $('#slider').nivoSlider();
    $("img").lazyload({skip_invisible:false});
})
;