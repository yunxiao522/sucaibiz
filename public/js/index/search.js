$(function(){
    $('#login').on('click',function(){
        layer.open({
            type: 2,
            title: '登录',
            shadeClose: true,
            shade: 0.8,
            area: ['500px', '500px'],
            content: '/login.html?url=/search.html' //iframe的url
        });
        return false;
    });
    $(document).on('click','#loginout',function () {
        $.ajax({
            url:'/loginout',
            type:'post',
            data:{
                url:'/search.html'
            },
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                if(data.success){
                    window.location.href = data.url;
                }
            },
            complete:function(){
                layer.close(loading);
            }
        });
        return false;
    });
    $('.t').on('click',function(){
        var column = $(this).parent('li').siblings();
        column.each(function () {
            $(this).removeClass('this');
        });
        $(this).parent('li').addClass('this');
        var type = $(this).parent('li')[0].dataset.type;
        $("input[name='type']").val(type);
        return false;
    });
    refreshType();
    function refreshType(){
        var column = $('.type').children('ul').children('li');
        var type = $("input[name='type']").val();
        column.each(function () {
            $(this).removeClass('this');
            var t = this.dataset.type;
            if(type == t){
                $(this).addClass('this');
                $("input[name='type']").val(t);
            }
        });
    };
});