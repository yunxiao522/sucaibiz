$(function () {
    //检查是否有登录账号
    if($.cookie('member_info') != null){
        var user_info = JSON.parse($.cookie('member_info'));
        var user_url = "/member/index.html?id="+user_info.uid;
        var user_name = user_info.username;
        $('.nologin .clue').text('昵称：'+user_name);
        $('.nologin button').addClass('push');
        $('.nologin button').removeClass('login');
        $('.nologin button').text('发表评论');
        $('.handle').html("<a href='"+user_url+"'>"+user_name+"</a> | <a href=\"/loginout.html\" id='loginout'>退出</a>");
        user_id = user_info.uid;
    }else{
        user_id = 0;
    }
    var info = $('.info');
    $(document).scroll(function () {
        var height = info.position().top - $(document).scrollTop();
        if(height <= 0){
            $('.hint').slideDown(500);
        }else{
            $('.hint').slideUp(500);
        }
        var height1 = $('.article_info').position().top - $(document).scrollTop();
        if(height1 <= 50){
            $('.article_info .user_info').css('position','fixed');
            $('.article_info .user_info').css('top','60px');
            $('.article_info .user_info').css('border-top','1px solid #EFEFEF');
            $('.comment .ad').css('position','fixed');
            $('.comment .ad').css('top','320px');
        }else{
            $('.article_info .user_info').css('position','relative');
            $('.article_info .user_info').css('top',0);
            $('.article_info .user_info').css('border-top','none');
            $('.comment .ad').css('position','relative');
            $('.comment .ad').css('top',0);
        }
        var height2 = $('.comment-list .end').offset().top - $(document).scrollTop();
        if(height2 <= 450){
            getComment();
        }
    });
    //文档信息导航栏点击事件
    $('.concern .head ul li').on('click',function () {
        var list = $(this).siblings();
        $.each(list,function () {
            $(this).removeClass('this');
        });
        $(this).addClass('this');
    });
    //发送请求获取文档收藏状态
    $.ajax({
        url:'/index/like/getLikeStatus.html',
        async:'true',
        type:'get',
        data:{
            id:$('input[name="id"]').val(),
            uid:user_id
        },
        success:function(res){
            var e = JSON.parse(res);
            if(e.data.status){
                $('.collect').text('已收藏');
            }
        }
    });
    $('.page .page-prev').height($('.images').height());
    $('.page .page-next').height($('.images').height());
    $('.page').height($('.images').height());
    $('.page .flip').css('top',($('.images').height() + $('.flip').height())/2);
    $('.page-prev').mouseover(function () {
        $('.prev').css('background-position','235px 0');
    });
    $('.page-prev').mouseout(function () {
        $('.prev').css('background-position','0 0');
    });
    $('.page-next').mouseover(function () {
        $('.next').css('background-position','75px 0');
    });
    $('.page-next').mouseout(function () {
        $('.next').css('background-position','155px 0');
    });
    $('.page').mouseover(function () {
        $('.page-next').show();
        $('.page-prev').show();
    });
    $('.page').mouseout(function () {
        $('.page-next').hide();
        $('.page-prev').hide();
    });
    //收藏按钮点击事件
    $('.collect').on('click',function () {
        //判断用户是否登录
        if(user_id == 0){
            $('.login').click();
            return false;
        }
        var id = $('input[name="id"]').val();
        var data={
            uid:user_id,
            id:id
        };
        $.ajax({
            url:'/index/like/collect.html',
            type:'post',
            data:data,
            beforeSend:function () {
                loading = layer.load(1, {shade: [0.1,'#fff']});
            },success:function(res){
                var e = JSON.parse(res);
                if(e.success){
                    if(e.code == 0){
                        $('.collect').text('已收藏');
                    }else{
                        $('.collect').text('收藏');
                    }
                }
            },complete:function () {
                layer.close(loading);
            }
        })
    });
    //下载原图按钮点击事件
    $('.down').on('click',function () {
        var aid = this.dataset.aid;
        var page = this.dataset.page;
        var url = this.dataset.url;
        var xhr = new XMLHttpRequest();
        var fileName = $('article .title').text() + '_'+ page + '.' + url.substring(url.lastIndexOf('.') + 1); // 文件名称
        xhr.open('post', '/index/down/down.html?aid='+ aid +'&page='+page +'&url='+url);
        xhr.responseType = 'arraybuffer';
        xhr.onreadystatechange = function(e){
        };
        xhr.onload = function(e) {
            if (this.status === 200) {
                var type = xhr.getResponseHeader('Content-Type');
                var blob = new Blob([this.response], {type: type});
                if (typeof window.navigator.msSaveBlob !== 'undefined') {
                    /*
                     * IE workaround for "HTML7007: One or more blob URLs were revoked by closing
                     * the blob for which they were created. These URLs will no longer resolve as
                     * the data backing the URL has been freed."
                     */
                    window.navigator.msSaveBlob(blob, fileName)
                } else {
                    var URL = window.URL || window.webkitURL;
                    var objectUrl = URL.createObjectURL(blob);
                    if (fileName) {
                        var a = document.createElement('a');
                        // safari doesn't support this yet
                        if (typeof a.download === 'undefined') {
                            window.location = objectUrl
                        } else {
                            a.href = objectUrl;
                            a.download = fileName;
                            document.body.appendChild(a);
                            a.click();
                            a.remove()
                        }
                    } else {
                        window.location = objectUrl
                    }
                }
            }
        };
        xhr.send()
    });
    //发表评论按钮点击事件
    $(document).on('click','.push',function () {
        var content = $('textarea[name="content"]').val();
        if(content == ''){
            layer.msg('请输入评论内容',{time:2});
            return false;
        }
        //组合数据发表请求
        $.ajax({
            url:'/member/comment/push',
            type:'post',
            data:{
                aid:$('input[name="id"]').val(),
                pid:0,
                content:content
            },beforeSend:function () {
                loading = layer.load(3, {shade: [0.1,'#fff']});
            },success:function (res) {
                var e = JSON.parse(res);
            },complete:function () {
                layer.close(loading);
            }
        })
    });
    //登录按钮点击事件
    $(document).on('click','.login',function () {
        //iframe窗
        layer.open({
            type: 2,
            title: false,
            closeBtn: 1, //不显示关闭按钮
            shade: [0],
            area: ['400px', '400px'],
            anim: 2,
            content: ['/login.html?url='+window.location.pathname, 'no'], //iframe的url，no代表不显示滚动条
        });
    });
    getComment();
    //获取评论列表数据
    function getComment(){
        var page = $('.comment-list')[0].dataset.page;
        var order = $('.comment-list')[0].dataset.order;
        var maxpage = $('.comment-list')[0].dataset.maxpage;
        if(parseInt(page) > parseInt(maxpage)){
            return false;
        }
        $.ajax({
            url:'/index/comment/getlist.html',
            type:'get',
            async:true,
            data:{
                page:page,
                aid:$('input[name="id"]').val(),
                pid:0,
                order:order
            },beforeSend:function () {
                $('.comment-list .end').remove();
                loading = layer.load(3, {shade: [0.1,'#fff']});
            },success:function (res) {
                var e = JSON.parse(res);
                $('.comment-list')[0].dataset.page = parseInt(e.data.current_page) + 1;
                $('.comment-list')[0].dataset.maxpage = parseInt(e.data.max_page);
                if(e.success) {
                    $.each(e.data.data, function () {
                        var text = '<li>\n' +
                            '                <div class="left">\n' +
                            '                    <div class="face">\n' +
                            '                        <img src="'+ this.face +'" alt="'+ this.user_info.nickname +'">\n' +
                            '                        <div class="level">Lv.'+ this.user_info.level +'</div>\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="right">\n' +
                            '                    <div class="head">\n' +
                            '                        <div class="name"> '+ this.user_info.nickname +' </div>\n' +
                            '                        <div class="device">荣耀10</div>\n' +
                            '                        <div class="site">素材站' + this.city + '网友</div>\n' +
                            '                        <div class="time">'+ this.create_time +'</div>\n' +
                            '                        <div class="floor">'+ this.tier +'楼</div>\n' +
                            '                    </div>\n' +
                            '                    <div class="content">\n' + this.content +'</div>\n' +
                            '                    <div class="operate">\n' +
                            '                        <div class="report"><a href="" data-id="'+ this.id +'" >举报</a></div>\n';
                        if(this.praiser_status){
                            text += '<div class="reprise"><a href="" data-id="'+ this.id +'" data-type="'+ this.praiser_status +'">取消支持('+ this.praiser +')</a></div>';
                        }else{
                            text += '<div class="reprise"><a href="" data-id="'+ this.id +'" data-type="'+ this.praiser_status +'">支持('+ this.praiser +')</a></div>';
                        }
                        if(this.oppose_status){
                            text += '<div class="oppose"><a href="" data-id="'+ this.id +'" data-type="'+ this.oppose_status +'">取消反对('+ this.oppose +')</a></div>';
                        }else{
                            text += '<div class="oppose"><a href="" data-id="'+ this.id +'" data-type="'+ this.oppose_status +'">反对('+ this.oppose +')</a></div>';
                        }
                        text += '<div class="revert"><a href="" data-id="'+ this.id +'" data-pid="'+ this.id +'">回复</a></div>';
                        text += '</div><div class="clear"></div>';
                        var pid = this.id;
                        var tier = this.tier;
                        if(this.son_list.count != 0){
                            text += '<div class="reply"><ul>';
                            $.each(this.son_list.data,function () {
                                text += '<li><div class="left"><div class="face">' +
                                    '<img src="'+ this.face +'" alt="'+ this.user_info.nickname +'">' +
                                    '<div class="level">Lv.'+ this.user_info.level +'</div></div></div>' +
                                    '<div class="right"><div class="head">' +
                                    '<div class="name">'+ this.user_info.nickname +'</div>' +
                                    '<div class="device">荣耀10</div>' +
                                    '<div class="site">素材站'+ this.city +'网友</div>' +
                                    '<div class="time">'+ this.create_time +'</div>' +
                                    '<div class="floor">'+ tier +'#'+ this.tier +'</div></div>' +
                                    '<div class="content">' + this.content + '</div>' +
                                    '<div class="operate"><div class="report"><a href="" data-id="'+ this.id +'">举报</a></div>';
                                if(this.praiser_status){
                                    text += '<div class="reprise"><a href="" data-id="'+ this.id +'" data-type="'+ this.praiser_status +'">取消支持('+ this.praiser +')</a></div>';
                                }else{
                                    text += '<div class="reprise"><a href="" data-id="'+ this.id +'" data-type="'+ this.praiser_status +'">支持('+ this.praiser +')</a></div>';
                                }
                                if(this.oppose_status){
                                    text += '<div class="oppose"><a href="" data-id="'+ this.id +'" data-type="'+ this.oppose_status +'">取消反对('+ this.oppose +')</a></div>';
                                }else{
                                    text += '<div class="oppose"><a href="" data-id="'+ this.id +'" data-type="'+ this.oppose_status +'">反对('+ this.oppose +')</a></div>';
                                }
                                text += '<div class="revert"><a href="" data-id="'+ this.id +'" data-pid="'+ pid +'">回复</a></div>';
                                text += '</div><div class="clear"></div></div><div class="clear"></div></li>';
                            });
                            text += '</ul>';
                            if(this.son_list.current_page != this.son_list.max_page){
                                text +='<div style="width:100%;height:30px;line-height:30px;text-align:center;font-size:14px;">还有'+ (parseInt(this.son_list.count) - 5) +'条回复, <a href="" data-id="'+ this.id +'" class="show_son">点击查看</a></div> '
                            }
                            text +='</div>';
                        }
                        text += '</div><div class="clear"></div></li>';
                        $('.comment-list').append(text);
                    });
                    $('.comment-list').append('<div class="clear end"></div>');
                }
            },complete:function () {
                layer.close(loading);
            }
        });
    }
    //评论支持操作
    $(document).on('click','.reprise a',function () {
        //判断用户是否登录
        if(user_id == 0){
            $('.login').click();
            return false;
        }
        var id = this.dataset.id;
        var type = this.dataset.type;
        var that = this;
        $.ajax({
            url:'/member/comment/praiser.html',
            type:'post',
            data:{
                id:id
            },beforeSend:function () {
                loading = layer.load(3, {shade: [0.1,'#fff']});
            },success:function (res) {
                var e = JSON.parse(res);
                if(e.success){
                    if(type == 'true'){
                        that.dataset.type = false;
                        $(that).text('支持('+e.data.num+')');
                    }else{
                        that.dataset.type = true;
                        $(that).text('取消支持('+e.data.num+')');
                    }
                }
                layer.msg(e.msg,{time:2000});
            },complete:function () {
                layer.close(loading);
            }
        });
        return false;
    });
    //评论反对操作
    $(document).on('click','.oppose a',function () {
        //判断用户是否登录
        if(user_id == 0){
            $('.login').click();
            return false;
        }
        var id = this.dataset.id;
        var type = this.dataset.type;
        var that = this;
        $.ajax({
            url:'/member/comment/oppose.html',
            type:'post',
            data:{
                id:id
            },beforeSend:function () {
                loading = layer.load(3,{shade: [0.1,'#fff']});
            },success:function (res) {
                var e = JSON.parse(res);
                if(e.success){
                    if(type == 'true'){
                        that.dataset.type = false;
                        $(that).text('反对('+ e.data.num +')');
                    }else{
                        that.dataset.type = true;
                        $(that).text('取消反对('+ e.data.num +')');
                    }
                }
                layer.msg(e.msg,{time:2000});
            },complete:function () {
                layer.close(loading);
            }
        });
        return false;
    });
    //评论举报操作
    $(document).on('click','.report a',function () {
        //判断用户是否登录
        if(user_id == 0){
            $('.login').click();
            return false;
        }
        var  id =  this.dataset.id;
        $.ajax({
            url:'/member/comment/inform.html',
            type:'post',
            data:{
                id:id
            },beforeSend:function () {
                loading = layer.load(3,{shade:[0.1,'#fff']});
            },success:function (res) {
                var e = JSON.parse(res);
                layer.msg(e.msg,{time:2000});
            },complete:function () {
                layer.close(loading);
            }
        });
        return false;
    });
    //评论刷新按钮点击事件
    $(document).on('click','.refresh-comment',function () {
        $('.comment-list li').remove();
        $('.comment-list .clear').remove();
        $('.comment-list')[0].dataset.page = 1;
        $('.comment-list')[0].dataset.max_page = 9999;
        getComment();
        return false;
    });
    //评论排序点击事件
    $(document).on('click','input[name="order"]',function () {
        var order = $(this).val();
        $('.comment-list')[0].dataset.order = order;
        $('.comment-list li').remove();
        $('.comment-list .clear').remove();
        $('.comment-list')[0].dataset.page = 1;
        $('.comment-list')[0].dataset.max_page = 9999;
        getComment();
    });
    //点击查看回复评论点击事件
    $(document).on('click','.show_son',function () {
        var id = this.dataset.id;
        var that = this;
        $.ajax({
            url: '/index/comment/getlist.html',
            type: 'get',
            async: true,
            data: {
                page: 1,
                aid: $('input[name="id"]').val(),
                pid: id
            }, beforeSend: function () {
                $(that).parent().hide();
                loading = layer.load(3, {shade: [0.1, '#fff']});
            }, success: function (res) {
                var e = JSON.parse(res);
                if (e.success) {
                    $(that).parent().siblings('ul').children('li').remove();
                    $.each(e.data.data, function () {
                        var text = '<li>\n' +
                            '                <div class="left">\n' +
                            '                    <div class="face">\n' +
                            '                        <img src="' + this.face + '" alt="' + this.user_info.nickname + '">\n' +
                            '                        <div class="level">Lv.' + this.user_info.level + '</div>\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '                <div class="right">\n' +
                            '                    <div class="head">\n' +
                            '                        <div class="name"> ' + this.user_info.nickname + ' </div>\n' +
                            '                        <div class="device">荣耀10</div>\n' +
                            '                        <div class="site">素材站' + this.city + '网友</div>\n' +
                            '                        <div class="time">' + this.create_time + '</div>\n' +
                            '                        <div class="floor">' + this.tier + '楼</div>\n' +
                            '                    </div>\n' +
                            '                    <div class="content">\n' + this.content + '</div>\n' +
                            '                    <div class="operate">\n' +
                            '                        <div class="report"><a href="" data-id="' + this.id + '" >举报</a></div>\n';
                        if (this.praiser_status) {
                            text += '<div class="reprise"><a href="" data-id="' + this.id + '" data-type="' + this.praiser_status + '">取消支持(' + this.praiser + ')</a></div>';
                        } else {
                            text += '<div class="reprise"><a href="" data-id="' + this.id + '" data-type="' + this.praiser_status + '">支持(' + this.praiser + ')</a></div>';
                        }
                        if (this.oppose_status) {
                            text += '<div class="oppose"><a href="" data-id="' + this.id + '" data-type="' + this.oppose_status + '">取消反对(' + this.oppose + ')</a></div>';
                        } else {
                            text += '<div class="oppose"><a href="" data-id="' + this.id + '" data-type="' + this.oppose_status + '">反对(' + this.oppose + ')</a></div>';
                        }
                        text += '<div class="revert"><a href="" data-id="'+ this.id +'" data-pid="'+ id +'">回复</a></div>';
                        text += '</div><div class="clear"></div>';
                        text += '</div><div class="clear"></div></li>';
                        $(that).parent().siblings('ul').append(text);
                    });
                    $(that).parent().siblings('ul').append('<div class="clear"></div>');
                }
            }, complete: function () {
                layer.close(loading);
            }
        });
        return false;
    });
    //评论回复点击事件
    $(document).on('click','.revert a',function () {
        //判断用户是否登录
        if(user_id == 0){
            $('.login').click();
            return false;
        }
        var id = this.dataset.id;
        var pid = this.dataset.pid;
        $(this).parent().parent().after('<div class="revert-box"><textarea class="revert-content" placeholder="政治、色情、喷骂、引战、水军、广告等违法违规行为将被封号。"></textarea><div class="close">X</div></textarea><div class="revert-more"><div class="nickname">昵称：'+ user_name +'</div><button class="revert-button" data-id="'+ id +'" data-pid="'+ pid +'">回复</button></div></div>');
        return false;
    });
    //评论回复关闭按钮点击事件
    $(document).on('click','.revert-box .close',function () {
        $(this).parent().remove();
    });
    //评论回复按钮点击事件
    $(document).on('click','.revert-button',function () {
        var id = this.dataset.id;
        var content = $(this).parent().siblings('textarea').val();
        var pid = this.dataset.pid;
        var that = this;
        if(content == ''){
            layer.msg('请输入回复内容',{time:2000});
            return false;
        }
        $.ajax({
            url:'/member/comment/push.html',
            type:'post',
            data:{
                content:content,
                aid:$('input[name="id"]').val(),
                pid:id,
                ppid:pid
            },beforeSend:function () {
                loading = layer.load(3, {shade: [0.1, '#fff']});
            },success:function (res) {
                var e = JSON.parse(res);
                if(e.success){
                    $(that).parent().parent().remove();
                }
                layer.msg(e.msg,{time:2000});
            },complete:function () {
                layer.close(loading);
            }
        });
        return false;
    });
    //监听浏览器大小变化
    $(window).resize(function () {
        var width = $(window).width();
        var left = 1200 + ((width - 1200)/2);
        $('.operation').css('left',left);
    });
    //返回顶部按钮点击事件
    $(document).on('click','.operation .top',function () {
        $('body,html').animate({
            scrollTop:'1px'
        },900);
    });
    //意见反馈点击事件
    $('.feedback').parent().on('click',function () {
        layer.open({
                type: 2,
                title:false,
                shade: [0],
                area: ['400px', '300px'],
                anim: 2,
                content: ['/index/feedback/frame.html', 'no'], //iframe的url，no代表不显示滚动条)
            });
        return false;
    });
    //禁止ctrl+n和 禁止ctrl+r和 禁止shift+f10 禁止鼠标右键or左右键 和禁止f5
    var oLastBtn = 0, bIsMenu = false;
    function nocontextmenu() {
        event.cancelBubble = true;
        event.returnValue = false;
        return false;
    }
    function norightclick(e) {
        if (window.Event) {
            if (e.which != 1) {
                return false;
            }
        } else if (event.button != 1) {
            event.cancelBubble = true;
            event.returnValue = false;
            return false;
        }
    }
    document.oncontextmenu = nocontextmenu;
    document.onmousedown = norightclick;
    function onKeyDown() {
        if ((event.altKey) || ((event.keyCode == 8) && (event.srcElement.type != "text" && event.srcElement.type != "textarea" && event.srcElement.type != "password")) || ((event.ctrlKey) && ((event.keyCode == 78) || (event.keyCode == 82))) || (event.keyCode == 116)) {
            event.keyCode = 0;
            event.returnValue = false;
        }
    }
});