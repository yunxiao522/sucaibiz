$(function () {
    function isRealNum(val){
        // isNaN()函数 把空串 空格 以及NUll 按照0来处理 所以先去除
        if(val === "" || val ==null){
            return false;
        }
        if(!isNaN(val)){
            return true;
        }else{
            return false;
        }
    }
    layui.use(['form'], function() {
        var form = layui.form;
        //自定义验证规则
        form.verify({
            level_name: function (value) {
                if (value.length == 0 || value.length > 20) {
                    return '积分等级名称不能为空，并且不能超过20个字符';
                }
            }
            , min_integral: function (value) {
                if (value == ''){
                    return '输入的积分值不能为空';
                }
                if (!isRealNum(value)) {
                    return '输入的等级值只能为数字';
                }
                if(value > 1000000 || value <0){
                    return '输入的等级值只能在0-1000000之间';
                }
            }
            , max_integral: function(value) {
                if (value == ''){
                    return '输入的等级值只能为数字';
                }
                if (!isRealNum(value)) {
                    return '输入的等级值只能为数字';
                }
                if(value > 1000000 || value <0){
                    return '输入的等级值只能在0-1000000之间';
                }
            }
            , star_num: function (value) {
                if(value < 0 && value > 100 ){
                    return '输入的星星数量只能在0-100之间';
                }
            }
            , description: function (value) {
                if (value.length > 100) {
                    return '输入的等级说明不能超过100个字符';
                }
            }
        });
        //监听提交
        form.on('submit(demo2)', function (data) {
            $.ajax({
                url: '/admin/alter_integral_info.html',
                type: 'post',
                data: $('#addlevel').serialize(),
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.msg(data.msg, {time: 1000}, function () {
                        layer.close(loading);
                        if (data.errorcode == 0) {
                            parent.location.href = "/admin/show_integral_list.html";
                        }
                    });
                }
            });
            return false;
        });
    });
});