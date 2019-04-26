$(function () {
    $('.msglist').hide();
    $('.mymenu').hide();
    //定时获取新消息列表
    setInterval(getMsg() ,500);
    getMsg();
    function getMsg() {
        //发送ajax消息
        $.ajax({
            url:'/member/message/getmsg.html',
            type:'get',
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                if (data.errorcode == 0) {
                    //组合消息数据
                    var list = data.data;
                    var html = '';
                    for(var i = 0;i < list.length ; i++){
                        html += "<a href='/member/message/show.html?id="+ list[i].id +"'><div class='mlist'>" +
                            "<div class='title'>" +
                            list[i].title+
                            "</div>"+
                            "<div class='time'>" +
                            list[i].create_time+
                            "</div>"+
                            "</div></a>";
                    }
                    $('.main').html(html);
                    $('.hint').show();
                }else if(data.errorcode == 2){
                    $('.main').css('background' ,'url(/public/png/empty-msg.png) center no-repeat');
                    $('.main').html("<div style='width:100%;height:1px;'></div><div style='text-align:center;width:100%;margin-top:210px;'>没有新消息哦</div>");
                    $('.hint').hide();
                }
            }
        })
    }
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
    //绑定查看全部单击事件
    $('.bottom').on('click',function () {
        window.location.href = '/member/message/msg.html';
    });
    //头像按钮
    $('.myinfo').on('mouseover' ,function () {
        $('.mymenu').show();
    });
    $('.myinfo').on('mouseout' ,function () {
        $('.mymenu').hide();
    });
    $('.mymenu').on('mouseover' ,function () {
        $('.myinfo').css('background' ,'#393D49');
        $('.mymenu').show();
    });
    $('.mymenu').on('mouseout' ,function () {
        $('.myinfo').css('background' ,'#13A38C');
        $('.mymenu').hide();
    });
    function load(url) {
        $('.matter').load(url,function () {

        })
    }
});