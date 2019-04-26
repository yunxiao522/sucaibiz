$(function () {
    var show_num = 4;
    //获取图集图片总数据
    var num = $('#num').val();
    var page = $('#page').val();
    $('.page').hide();
    $('.next').hide();

    setTimeout(function () {
        //获取展示图像的高度
        var img_height = $('.imgz').height();
        var margin_top = (img_height - 75)/2;
        //设置上一页和下一页距离上边框的高度
        $('.page').css('margin-top' ,margin_top);
        $('.next').css('margin-top' ,margin_top);
        $('.page').show();
        $('.next').show();
        //控制上一页和下一页的显示和隐藏
        if(page == 1){
            $('.page').hide();
        }

        if(page == num){
            $('.next').hide();
        }

    },500);

    //绑定下载按钮事件
    $('#down').on('click' ,function () {
       return false;
    });

    //缩略图滚动事件
    $(".view").jCarouselLite({
        btnNext: ".v_r",
        btnPrev: ".v_l",
        circular: false,
        visible: 4,
        speed:800,
        start:page-1,

    });
    //绑定下载按钮事件
    $('#down').on('click' ,function(){
        //示范一个公告层
        layer.open({
            type: 1
            ,title: false //不显示标题栏
            ,closeBtn: false
            ,area: '300px;'
            ,shade: 0.8
            ,id: 'LAY_layuipro' //设定一个id，防止重复弹出
            ,btn: ['下载图集','下载壁纸']
            ,btnAlign: 'c'
            ,moveType: 0 //拖拽模式，0或者1
            ,content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">点击下载壁纸即可下载当前壁纸。<br>点击下载图集，需登录后才可下载图集。<br></div>'
            ,success: function(layero){
                var btn = layero.find('.layui-layer-btn');
                //绑定下载图集按钮事件
                btn.find('.layui-layer-btn0').on('click' ,function(){
                   //判断用户是否登录
                    if ($.cookie('member_info') != null) {
                        user_info = JSON.parse($.cookie('member_info'));
                        var uid = user_info.uid;
                        layer.open({
                            type: 2,
                            title: '图集下载',
                            shadeClose: true,
                            shade: 0.8,
                            area: ['960px', '700px'],
                            content: '/down.html?aid='+$('input[name="id"]').val() + '&uid=' + uid
                        })
                    }else{
                        layer.open({
                            type: 2,
                            title: '素材站登录',
                            shadeClose: true,
                            shade: 0.8,
                            area: ['600px', '500px'],
                            content: '/login.html?url='+window.location
                        })
                    }
                });
                //绑定下载壁纸按钮事件
                btn.find('.layui-layer-btn1').on('click' ,function(){
                    downloadImage($('input[name="imgurl"]').val());
                });
            }
        });
    });
    function downloadImage(src) {
        //获取文件名
        var str = src.split('/');
        var length = str.length;
        var filename = str[length - 1];
        $('#downImg').attr('src', src);
        $('#downImg').attr('target', '_blank');
        var img = $('#downImg').attr("src");
        var alink = document.createElement("a");
        alink.href = img;
        alink.download = filename;
        alink.click();
    }
});