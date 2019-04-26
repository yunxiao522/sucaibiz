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
            name: function(value){
                if(value == ''){
                    return '分组名称不能为空';
                }else if(value.length >20){
                    return '分组名称不能超过20个字符';
                }
            }
            ,sort: function (value) {
                if(!isRealNum(value)){
                    return '排序必须为数字';
                }else if(value > 127){
                    return '排序不能超过127';
                }
            }
        });

        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:'/admin/add_sysconfig_group.html',
                type:'post',
                data:$('#add').serialize(),
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            window.location='/admin/show_sysconfig_group.html';
                            return false;
                        }
                    });
                }
            });
            return false;
        });

    });
});