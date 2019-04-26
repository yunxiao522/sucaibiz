$(function () {
    $('.msglist').hide();
    $('.smenu').hide();
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
        window.location.href = '/member/information/myarticle.html?class=' +ss;
        return false;
    });

    //绑定数据中心页面我获得数据各个提示
    $('.pop').hover(function () {
        layer.tips('总人气与全部作品人气、粉丝数、主页访问数均相关，被取消关注也有可能降低哦！' ,$('.pop').children('.h') ,{tips:3,maxWidth:1000,maxHeight:350});
    });
    $('.fens').hover(function () {
        layer.tips('当前获得的粉丝数' ,$('.fens').children('.h') ,{tips:3,maxWidth:1000,maxHeight:350});
    });
    $('.index').hover(function () {
        layer.tips('当前获得的个人主页的访客数' ,$('.index').children('.h') ,{tips:3,maxWidth:1000,maxHeight:350});
    });
    $('.production').hover(function () {
        layer.tips('当前发布中的作品/文章获得推荐总数（不包含匿名推荐）' ,$('.production').children('.h') ,{tips:3,maxWidth:1000,maxHeight:350});
    });
    //绑定选择按钮事件
    $('.select').on('mouseover' ,function () {
       $(this).siblings('.smenu').show();
    });
    $('.select').on('mouseout' ,function () {
        $(this).siblings('.smenu').hide();
    });
    $('.smenu').on('mouseover' ,function () {
        $(this).show();
    });
    $('.smenu').on('mouseout' ,function () {
        $(this).hide();
    });
    //绑定我的数据模块头部ul li 鼠标放上样式及点击后的事件
    $('.tli').children('li').on('mouseover',function () {
        if(!$(this).hasClass('this')){
            $(this).css('border-bottom' ,'2px #000000 solid');
        }
    });
    $('.tli').children('li').on('mouseout' ,function(){
        $(this).css('border-bottom' ,'');
    });
    $('.tli').on('click' ,function () {
        var xd = $(this).siblings();
        for(var i = 0;i < xd.length ;i++){
           $(xd[i]).children('li').removeClass('this');
        }
        $(this).children('li').addClass('this');
        return false;
    });


});