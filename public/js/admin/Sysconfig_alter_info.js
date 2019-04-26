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
            description:function(value){
                if(value == ''){
                    return '参数说明不能为空哦';
                }else if(value.length >50){
                    return '参数说明不能超过50个字符哦';
                }
            }
            ,sort: function (value) {
                if(!isRealNum(value)){
                    return '排序必须为数字';
                }else if(value > 127){
                    return '排序不能超过127';
                }
            }
            ,value:function(value){
                if(value.length > 200){
                    return '默认值不能超过200个字符哦';
                }
            }
            ,name: function(value){
                if(value == ''){
                    return '变量名不能为空';
                }else if(value.length >100){
                    return '变量名不能超过100个字符哦';
                }
            }

        });

        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:'/admin/alter_Sysconfig_info.html',
                type:'post',
                data:$('#alter').serialize(),
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            parent.window.location='/admin/show_sysconfig_list.html';
                            return false;
                        }
                    });
                }
            });
            return false;
        });

    });
});