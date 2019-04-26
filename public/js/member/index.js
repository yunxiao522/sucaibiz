$(function () {
    //监听一级导航里的a链接
    $('.nav ul li a').on('click',function () {
        $('.nav ul li a').css('color','#ffffff');
        //添加样式
        $(this).css('color','#11A28B');
        var type = this.dataset.type;
        var id  = $('input[name="id"]').val();
        //拼接url地址
        var url = '/member/index/showMain?type='+type+'&id='+id;
        load(url);
        return false;
    });
    function load(url) {
        $('#main').load(url,function () {
            //回调函数
            $('.load').hide();
            $('.loader').hide();
            $('.content').hide();
            (function ($) {
                $('.load').show();
                $('.loader').show();
            })(jQuery);
            $(document).ready(function () {
                setTimeout(function () {
                    $('.load').hide();
                    $('.loader').hide();
                    $('.content').show();
                },2000);
            });
            //绑定收藏导航链接事件
            $('.likenav ul li a').on('click' ,function () {
                $('.likenav ul li a').css('color','#000000');
                //添加样式
                $(this).css('color','#11A28B');
                //获取连接的数据
                var type = this.dataset.type;
                //组合url链接地址
                var url = '/member/index/showLikeList?id='+$('input[name="id"]').val()+'&type='+type;
                loadlikelist(url);
                return false;
            });
            //绑定我的关注关注按钮事件
            $('.att').on('click' ,function(){
                var that = this;
                var fensid = this.dataset.uid;
                var uid = $('input[name="uid"]').val();
                //发送ajax请求
                $.ajax({
                    url:'/member/options/attenoptions.html',
                    type:'post',
                    data:{fensid:fensid,uid:uid},
                    beforeSend:function(e){
                        loading = layer.load(3, {
                            shade: [0.1,'#fff'] //0.1透明度的白色背景
                        });
                    },
                    success:function(e){
                        layer.close(loading);
                        var data = JSON.parse(e);
                        if(data.errorcode == 0){
                            layer.msg(data.msg ,{time:2000} ,function () {
                                var html = $(that).children('.atten').html();
                                if(html == '已关注'){
                                    $(that).children('.atten').html('+关注');
                                }else{
                                    $(that).children('.atten').html('已关注');
                                }
                            });
                        }else{
                            layer.msg(data.msg ,{time:2000});
                        }
                    }
                })
                return false;
            });
            function loadlikelist(url){
                $('.likelist').load(url ,function () {
                    //回调函数
                    $('.load1').hide();
                    $('.loader1').hide();
                    $('.content1').hide();
                    (function ($) {
                        $('.load1').show();
                        $('.loader1').show();
                    })(jQuery);
                    $(document).ready(function () {
                        setTimeout(function () {
                            $('.load1').hide();
                            $('.loader1').hide();
                            $('.content1').show();
                        },2000);
                    });
                    //监听分页a链接点击事件
                    $('#paging a').on('click',function () {
                        var url = this.href;
                        loadlikelist(url);
                        return false;
                    });
                });
            }
        });
    }

});