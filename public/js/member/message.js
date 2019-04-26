$(function () {
    $('.msglist').hide();

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
        //获取class值
        var ss = this.dataset.type;
        //跳转页面
        window.location.href = '/member/message/msg.html?class=' +ss;
        return false;
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

});