$(function(){
    layui.use(['form', 'layedit', 'laydate' ,'upload'], function(){
        var form = layui.form
            ,layer = layui.layer
            ,layedit = layui.layedit
            ,laydate = layui.laydate
            ,upload = layui.upload;

        //日期
        laydate.render({
            elem: '#date'
        });
        laydate.render({
            elem: '#date1'
        });

        //创建一个编辑器
        var editIndex = layedit.build('LAY_demo_editor');

        //自定义验证规则
        form.verify({
            title: function(value){
                if(value.length < 5){
                    return '标题至少得5个字符啊';
                }
            }
            ,pass: [/(.+){6,12}$/, '密码必须6到12位']
            ,content: function(value){
                layedit.sync(editIndex);
            }
        });

        //监听提交
        form.on('submit(demo1)', function(data){
            if(data.field.channel == 2){
                data.field.old = images_old;
                data.field.new = images_new;
            }else if(data.field.channel == 1 || data.field.channel == 3){
                data.field.content = layedit.getContent(editIndex);
            }else if(data.field.channel == 4){

            }

            $.ajax({
                url:'/admin/article/alterarticle.html',
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
                            window.location='/admin/article/show.html';
                            return false;
                        }
                    });
                }
            });
            return false;
        });

        //绑定上传缩略图按钮事件
        var uploadInst = upload.render({
            elem: '#litpic' //绑定元素
            ,url: '/admin/upload_litpic.html' //上传接口
            ,done: function(data){
                //上传完毕回调
                layer.msg(data.msg, {time: 1000}, function () {
                    $('#litpic_url').val(data.url);
                    $('#litpic_img').attr('src', data.url);
                    $('#litpic_id').val(data.id)
                })
            }
        });
        $('#litpic').on('click' ,function(){
            return false;
        });
        //绑定选择来源按钮事件
        $('#choice_source').on('click', function () {
            //iframe层
            layer.open({
                type: 2,
                title: '选择来源',
                shadeClose: true,
                shade: 0.8,
                area: ['400px', '250px'],
                content: "/admin/show_article_source.html" //iframe的url
            });
            return false;
        });
        //绑定选择作者按钮事件
        $('#choice_author').on('click', function () {
            //iframe层
            layer.open({
                type: 2,
                title: '选择作者',
                shadeClose: true,
                shade: 0.8,
                area: ['400px', '250px'],
                content: "/admin/show_article_author.html" //iframe的url
            });
            return false;
        });
        //绑定文档TAG标签失去焦点事件
        $('input[name="tag"]').on('blur', function () {
            var tag = $('input[name="tag"]').val();
            $('input[name="keywords"]').val(tag);
        });

        form.on('checkbox(attribute)', function (data) {
            if (data.value == 'f') {
                if (data.elem.checked) {
                    $('#litpic2').show();
                } else {
                    $('#litpic2').hide();
                }
            } else if (data.value == 's') {
                if (data.elem.checked) {
                    $('#litpic3').show();
                } else {
                    $('#litpic3').hide();
                }
            }
        });
        //上传幻灯片图像
        var uploadSlide = upload.render({
            elem: '.upload_slide',
            url: '/admin/upload_litpic.html',
            method:'post',
            auto:false,
            choose: function (obj) {  //上传前选择回调方法
                var flag = true;
                obj.preview(function (index, file, result) {
                    var img = new Image();
                    img.src = result;
                    img.onload = function () { //初始化夹在完成后获取上传图片宽高，判断限制上传图片的大小。
                        if (img.width == 800 && img.height == 450) {
                            obj.upload(index, file); //满足条件调用上传方法
                        } else {
                            flag = false;
                            layer.msg("上传的幻灯片图像大小必须是800*450");
                            return false;
                        }
                    };
                    return flag;
                });
            },
            done: function (res) {
                layer.msg(res.msg, {time: 1000}, function () {
                    $('#slide_id').val(res.id);
                    $('#slide_url').val(res.url);
                    $('#slide_img').attr('src', res.url);
                })
            }
        });
        $('.upload_slide').on('click', function () {
            return false;
        });
        //上传幻灯片图像
        var uploadRoll = upload.render({
            elem: '.upload_roll',
            url: '/admin/upload_litpic.html',
            method:'post',
            auto:false,
            choose: function (obj) {  //上传前选择回调方法
                var flag = true;
                obj.preview(function (index, file, result) {
                    var img = new Image();
                    img.src = result;
                    console.log(img.width,img.height);
                    img.onload = function () { //初始化夹在完成后获取上传图片宽高，判断限制上传图片的大小。
                        if (img.width == 500 && img.height == 192) {
                            obj.upload(index, file); //满足条件调用上传方法
                        } else {
                            flag = false;
                            layer.msg("上传的滚动图像大小必须是500*192");
                            return false;
                        }
                    };
                    return flag;
                });
            },
            done: function (res) {
                layer.msg(res.msg, {time: 1000}, function () {
                    $('#roll_id').val(res.id);
                    $('#roll_url').val(res.url);
                    $('#roll_img').attr('src', res.url);
                })
            }
        });
        $('.upload_roll').on('click', function () {
            return false;
        });

    });
});