$(function () {
    layui.use(['form', 'upload','table','vip_table'], function(){
        var form = layui.form
            ,layer = layui.layer
            , table = layui.table
            , vipTable = layui.vip_table
            ,upload = layui.upload;


        //自定义验证规则
        form.verify({
            width:function (value) {
                if(value <0 || value >200){
                    return '输入的宽度只能在0-200之间';
                }
            }
            ,height:function (value) {
                if(value <0 || value >200){
                    return '输入的高度只能在0';
                }
            }
            ,n:function (value) {
                if(value.length > 20){
                    return '输入的水印文字不能超过20个字符哦';
                }
            }
            ,size:function (value) {
                if(value <0 ||value >50){
                    return '输入的水印文字大小只能介于0-50之间';
                }
            }
            ,color:function (value) {
                if(value.length >7){
                    return '输入的水印文字颜色不能超过7个字符';
                }
            }
            ,x:function (value) {
                if(value <0 || value >200){
                    return '输入的距x轴只能在0-200之间';
                }
            }
            ,y:function (value) {
                if(value <0 || value >200){
                    return '输入的距y轴只能在0-200之间';
                }
            }
        });
    // 表格渲染
        var tableIns = table.render({
            elem: 'article'                  //指定原始表格元素选择器（推荐id选择器）
            , height: 600    //容器高度
            , width: 500
            , cols: [[                  //标题栏
                {field: 'user_name', title: '账号', width: 120 ,align:'center'}
                , {field: 'nick_name', title: '名称', width: 120 ,align:'center'}
                , {field: 'content', title: '操作内容', width: 200 ,align:'center'}
                , {field: 'create_time', title: '操作时间', width: 200 ,align:'center'}
            ]]
            , id: 'dataCheck'
            , url: '/admin/get_log_operate.html?class=water'
            , method: 'get'
            , page: true
            , limits: [30, 60, 90, 150, 300]
            , limit: 30 //默认采用30
            , loading: false
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
                console.log(res);

                //得到当前页码
                console.log(curr);

                //得到数据总量
                console.log(count);
            }
        });
        //上传水印图像
        var uploadImg = upload.render({
            elem: '#test1' //绑定元素
            , url: '/admin/update_sysconfig_water.html' //上传接口
            , data: {id: $('input[name ="id"]').val(), type: 'img'}
            , accept: 'images'
            , done: function (res) {
                loading = layer.load(0, {shade: false});
                layer.close(loading);
                layer.msg(res.msg, {time: 1000}, function () {
                    if(res.errorcode == 0){
                        $('#water_img').attr('src' ,res.url);
                    }
                })
            }
        });
        //上传水印文字
        var uploadFont = upload.render({
            elem: '#test2' //绑定元素
            ,url: '/admin/update_sysconfig_water.html' //上传接口
            ,data:{id:$('input[name ="id"]').val(),type:'font'}
            ,accept:'file'
            ,done: function(res){
                loading = layer.load(0, {shade: false});
                layer.close(loading);
                layer.msg(res.msg, {time: 1000})
            }
        });

        //监听提交
        form.on('submit(demo1)', function(data){
            $.ajax({
                url:'/admin/show_sysconfig_water.html',
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
                            window.location='/admin/show_sysconfig_water.html';
                            return false;
                        }
                    });
                }
            });
            return false;
        });
    });
});
