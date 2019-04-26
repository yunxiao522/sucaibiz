$(function () {
    layui.use(['form'], function () {
        var form = layui.form;
        //监听提交
        form.on('submit(demo2)', function (data) {
            $.ajax({
                url: '/admin/make_article_html.html',
                type: 'post',
                data: data.field
            });
            var getinfo = setInterval(function () {
                $.ajax({
                    url: '/admin/html/getinfo.html',
                    type: 'get',
                    data: {token:token},
                    success: function (e) {
                        var data = JSON.parse(e);
                        if (data.errorcode == 0) {
                            $('#bar').attr('aria-valuenow' ,data.num);
                            $('#info').html(data.info);
                            if(data.num == 100){
                                clearInterval(getinfo);
                            }
                        }
                    }
                });
            },5000);
            return false;
        });
        form.verify({
            column:function (value) {
                if(value == 0){
                    return '请选择栏目';
                }
            },
            start:function (value) {
                if(value == ''){
                    return '输入的起始id不能为空';
                }
                if(value <0){
                    return '输入的起始id不能小于0';
                }
            },
            end:function (value) {
                if(value == ''){
                    return '输入截止id不能为空';
                }
                if(value <0){
                    return '输入的截止id不能为空';
                }
            },
            token:function (value) {
                if(value == ''){
                    return '输入的参数不完整';
                }
            }
        });
        //获取操作token
        var token = $('input[name="token"]').val();

        //获取操作token
        var token = $('input[name="token"]').val();
    });

    $("#change-color .bar").hover(function () {
        // $(this).toggleClass('active');
        $(this).find('.front').toggleClass('shine');
    });
});