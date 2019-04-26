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
layui.use(['form','upload'], function(){
    var form = layui.form
        ,upload = layui.upload;
    //执行实例
    var uploadInst = upload.render({
        elem: '#test1' //绑定元素
        ,url: '/admin/update_member_level_img.html' //上传接口
        ,method:'post'
        ,done: function(res){
            //上传完毕回调
            if(res.errorcode == 0){
                layer.msg('上传成功',{time:1000} ,function () {
                    $('#level_img').attr('src',res.url);
                    $('input[name="level_img"]').attr('value',res.url);
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
        name: function(value){
            if(value.length == 0 || value.length > 20){
                return '等级名称不能为空，并且不能超过20个字符';
            }
        }
        ,rank: function(value){
            if(!isRealNum(value)){
                return '输入的等级值只能为数字';
            }
        }
        ,description:function (value) {
            if(value.length > 100){
                return '输入的等级说明不能超过100个字符';
            }
        }
    });
    //监听提交
    form.on('submit(demo2)', function(data){
        $.ajax({
            url:'/admin/add_member_level.html',
            type:'post',
            data:$('#addlevel').serialize(),
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.msg(data.msg, {time: 1000}, function () {
                    layer.close(loading);
                    if (data.errorcode == 0) {
                        parent.location.href="/admin/member_level.html";
                    }
                });
            }
        });
        return false;
    });
});