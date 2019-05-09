layui.use(['form', 'layedit', 'laydate' ,'upload'], function(){
    var form = layui.form
        ,layer = layui.layer
        ,layedit = layui.layedit
        ,laydate = layui.laydate
        ,upload = layui.upload;
    //执行实例
    var uploadInst = upload.render({
        elem: '#test1' //绑定元素
        ,url: '/admin/update_member_face.html' //上传接口
        ,method:'post'
        ,data:{id:$('input[name="id"]').val()}
        ,done: function(res){
            //上传完毕回调
            if(res.success){
                layer.msg('上传成功',{time:1000} ,function () {
                    $('#face_img').attr('src',res.data.url);
                });
            }else{
                layer.msg('上传失败',{time:2000});
            }

        }
        ,error: function(){
            //请求异常回调
            layer.msg('上传失败',{time:2000});
        }
    });
    //自定义验证规则
    form.verify({
        nickname: function(value) {
            if (value == '') {
                return '昵称不能为空哦';
            }
            if (value.length > 20) {
                return '昵称不能超过20个字符';
            }
        },
        password: function (value) {
            if(value != ''){
                if(value.length <6){
                    return '输入密码不能小于6个字符哦';
                }
                if(value.length >20){
                    return '输入的密码不能超过20个字符哦';
                }
                var reg = new RegExp(/^(?![^a-zA-Z]+$)(?!\D+$)/);
                if (!reg.test(value)){
                    return '输入的密码必须包含字母和数字哦';
                }
            }
        },
        type: function (value) {
            if(value == ''){
                return '请选择账号的类型';
            }
        },
        realname: function (value) {
            if(value != '' && value.length > 20){
                return '输入的真实姓名不能超过20个字符哦';
            }
        },
        qq:function (value) {
            var reg = new RegExp("[1-9][0-9]{4,}");
            if(value != '' && !reg.test(value)){
                return '输入的QQ账号不正确,请检查';
            }
        },
        email: function (value) {
            var reg = new RegExp("^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$");
            if(value != '' && !reg.test(value)){
                return '输入的邮箱格式不正确，请检查。';
            }
        },
        tel: function (value) {
            var reg = new RegExp("^((13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\\d{8}$");
            if(value != '' && !reg.test(value)){
                return '输入的手机号格式不正确，请检查';
            }
        }
    });
    //检查昵称是否唯一

    form.on('submit(demo1)', function(data){
        var password = $('input[name="password"]').val();
        var verifypassword = $('input[name="verifypassword"]').val();
        if(password != ''){
            if(password != verifypassword){
                layer.tips('两次输入的密码不一致，请检查', 'input[name="password"]' ,{time:4000});
                return false;
            }
        }
        $.ajax({
            url:'/admin/alter_member.html',
            type:'post',
            data:data.field,
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.msg(data.msg, {time: 1000}, function () {
                    layer.close(loading);
                    if (data.errorcode == 0) {
                        parent.location.href="/admin/member_show.html";
                    }
                });
            }
        });
        return false;
    });
});
$(function () {
    $('#verify').hide();
    //监听输入密码
    $('input[name="password"]').on('blur',function () {
        var password = $('input[name="password"]').val();
        if(password != ''){
            $('#verify').show();
        }else{
            $('#verify').hide();
        }
    });
});

