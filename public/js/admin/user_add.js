// layui方法
layui.use(['form'], function () {

    // 操作对象
    var form = layui.form;
    form.verify({
        username:function(value){
            if(value == ''){
                return '输入的账号不能为空';
            }
            if(value.length<5){
                return '输入的账号长度不能少于5个字符';
            }
            if(value.length>20){
                return '输入的账号长度不能多于20个字符';
            }
            var rule = /[a-zA-Z0-9]/;
            if(!rule.test(value)){
                return '输入的账号只能包含字母和数字';
            }
        },password:function(value){
            if(value == ''){
                return '输入的密码不能为空';
            }
        },verfypassword:function(value){
            if(value == ''){
                return '输入的验证码不能为空';
            }
        },nickname:function(value){
            if(value == ''){
                return '输入的账号昵称不能为空';
            }
            if(value.length>20){
                return '输入的账号昵称不能超过20个字符';
            }
        },level:function(value){
            if(value == 0){
                return '请选择账号等级';
            }
        },realname:function(value){
            if(value.length > 15){
                return '输入的真实的姓名不能超过15个字符';
            }
        }
    });
    // you code ...
    //监听提交
    form.on('submit(demo1)', function(data){
        var password = $('input[name="password"]').val();
        var verfypass = $('input[name="verfypassword"]').val();
        if(password != verfypass){
            layer.msg('两次输入的密码不一致',{time:1000});
            return false;
        }else{
            data.field.password = hex_sha1(password);
            data.field.verfypassword = hex_sha1(verfypass);
        }
        $.ajax({
            url:'/admin/user/add.html',
            type:'post',
            data:data.field,
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                layer.msg(data.msg, {time: 1000}, function () {
                    if (data.errorcode == 0) {
                        parent.tableIns.reload();
                        parent.layer.closeAll();
                    }
                });
            }
        });
        return false;
    });
    //监听提交
    form.on('submit(demo2)', function(data){
        $.ajax({
            url:'/admin/user/alter.html',
            type:'post',
            data:data.field,
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                layer.msg(data.msg, {time: 1000}, function () {
                    if (data.errorcode == 0) {
                        parent.tableIns.reload();
                        parent.layer.closeAll();

                    }
                });
            }
        });
        return false;
    });

});