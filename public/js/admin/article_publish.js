//初始化数据
$(function () {
    $('#docu').hide();
    $('#atlas').hide();
    $('#resource').hide();
    var click = Math.ceil(Math.random() * 100);
    $('input[name="click"]').val(click);
    $('#sourceimgone').hide();
    $('#litpic2').hide();
    $('#litpic3').hide();


    // function setload(){
    //     var h = $(document.body).height();
    //     var w = $(document.body).width();
    //     $('#loading').width(w);
    //     $('#loading').height(h);
    // }
    // //动态调整加载隐藏层大小
    // $(window).resize(function(){
    //     setload()
    // });
    // setload();
    //
    //
    // setInterval(function () {
    //     var num =$('#bar').attr('aria-valuenow');
    //     if(num*1 < 100){
    //         num = num*1 + 1;
    //     }
    //     $('#bar').attr('aria-valuenow' ,num);
    // },500);

});
layui.use(['form', 'layedit', 'laydate', 'upload'], function () {
    var form = layui.form
        , layer = layui.layer
        , layedit = layui.layedit
        , laydate = layui.laydate
        , upload = layui.upload;

    //日期
    laydate.render({
        elem: 'input[name="pudate"]',
        type: 'datetime'
    });

    //创建一个编辑器
    var editIndex = layedit.build('LAY_demo_editor');

    //自定义验证规则
    form.verify({
        title: function (value) {
            if (value.length > 80) {
                return '输入的文档标题不能超过80个字符';
            }
            if (value == '') {
                return '输入的文档标题不能为空';
            }
        }
        , litpic: function (value) {
            if (value == '') {
                return '请上传文档缩略图';
            }
            if (value.length > 100) {
                return '文档的缩略图链接不能超过100个字符';
            }
        }
        , keywords: function (value) {
            if (value.length > 60) {
                return '输入的文档关键词不能超过60个字符';
            }
        }
        , source: function (value) {
            if (value == '') {
                return '输入的文档来源不能为空';
            }
            if (value.length > 50) {
                return '输入的文档来源不能超过50个字符';
            }
        }
        , author: function (value) {
            if (value == '') {
                return '输入的作者不能为空';
            }
            if (value.length > 30) {
                return '输入的作者不能超过30个字符';
            }
        }
        , column: function (value) {
            if (value == 0) {
                return '请选择文档栏目';
            }
        }
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

    //获取文章token
    var token = $('input[name="token"]').val();

    //上传缩略图
    var uploadInst = upload.render({
        elem: '.update_litpic' //绑定元素
        , url: '/admin/upload_litpic.html' //上传接口
        , done: function (data) {
            layer.msg(data.msg, {time: 1000}, function () {
                $('#litpic_id').val(data.id);
                $('#litpic_url').val(data.url);
                $('#litpic_img').attr('src', data.url);
            })
        }
        , error: function () {
        }
    });
    $('.update_litpic').on('click', function () {
        return false;
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
                img.onload = function () { //初始化夹在完成后获取上传图片宽高，判断限制上传图片的大小。
                    if (img.width == 500 && img.height == 192) {
                        obj.upload(index, file); //满足条件调用上传方法
                    } else {
                        flag = false;
                        layer.msg("上传的滚动图像大小必须是800*450");
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
    //上传单张资源展示图方法
    var uploadsource = upload.render({
        elem: '.update_source' //绑定元素
        , url: '/admin/upload_source_one.html?token=' + token //上传接口
        , done: function (data) {
            layer.msg(data.msg, {time: 1000}, function (e) {
                if (data.errorcode == 0) {
                    source_img.push(data.info);
                    console.log(source_img);
                }
            })
        }
        , error: function () {
        }
    });
    $('.update_source').on('click', function () {
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
    //绑定文档标题失去焦点事件
    $('input[name="title"]').on('blur', function () {
        var title = $('input[name="title"]').val();
        if (title.length > 0) {
            layer.closeAll();
            //发送post请求，检测是否已经存在该标题文档
            $.ajax({
                url: '/admin/examine_article_title.html',
                type: 'post',
                data: {title: title},
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.close(loading);
                    if (data.errorcode == 0) {
                        layer.tips('已经存在相应的文档', "input[name='title']", {tips: [1, '#3595CC'], time:5000});
                    }
                }
            })
        }
    });
    //绑定文档TAG标签失去焦点事件
    $('input[name="tag"]').on('blur', function () {
        var tag = $('input[name="tag"]').val();
        $('input[name="keywords"]').val(tag);
    });

    form.on('select(column)', function (data) {
        var column_id = data.value
        //发送ajax获取栏目所属的文档类型
        $.ajax({
            url: '/admin/get_article_column_type.html',
            type: 'post',
            data: {column_id: column_id},
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                if (data.errorcode == 0) {
                    $('input[name="column_type"]').val(data.channel_type);
                    $('input[name="template"]').val(data.template);
                    if (data.channel_type == 1) {
                        //文章
                        $('#docu').show();
                        $('#atlas').hide();
                        $('#resource').hide();
                    } else if (data.channel_type == 2) {
                        //图集
                        $('#atlas').show();
                        $('#docu').hide();
                        $('#resource').hide();
                    } else if (data.channel_type == 3) {
                        //专题
                    } else if (data.channel_type == 4) {
                        //资源
                        $('#resource').show();
                        $('#atlas').hide();
                        $('#docu').hide();
                    }
                } else {
                    layer.msg('获取文档栏目类型数据失败，请联系管理员。', {time: 3000}, function () {
                        publish = false;
                    })
                }
            }
        })
    });

    form.on('switch(sourceimg)', function (data) {
        console.log(data.elem.checked);
        if (data.elem.checked == false) {
            $('#sourceimgone').show();
            $('#sourceimgmore').hide();
        } else {
            $('#sourceimgone').hide();
            $('#sourceimgmore').show();
        }
    });

    //监听tag输入框值
    $('input[name="tag"]').on('input',function(){
        var column_id = $('select[name="column"] option:selected').val();
        if(column_id == 0){
            return false;
        }
        var tag = $(this).val();
        if(tag == ''){
            return false;
        }
        var tag_arr = tag.split(",");
        $.ajax({
            url:'/admin/pushGetTag.html',
            type:'get',
            data:{
                column_id:column_id,
                tag:tag_arr[tag_arr.length - 1]
            },beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (data) {
                var e = JSON.parse(data);
                $('#taglist').empty();
                if(e.success && e.data.length != 0){
                    $.each(e.data,function () {
                        var text = '<option value="'+this.tag_name+'">'+ this.tag_name +'</option>';
                        $('#taglist').append(text);
                    });
                }
            },complete:function (e) {
                layer.close(loading);
            }
        })
    });

    $(document).on('focus','#taglist',function(){
        console.log(1);
    });

    //监听提交
    form.on('submit(demo2)', function (data) {
        data.field.content = layedit.getContent(editIndex);
        data.field.images = images;
        data.field.source_img = source_img;
        $.ajax({
            url: '/admin/article/publish.html',
            type: 'post',
            data: data.field,
            beforeSend: function () {
                loading = layer.load(0, {shade: false});
            },
            success: function (e) {
                var data = JSON.parse(e);
                layer.close(loading);
                layer.msg(data.msg, {time: 1000}, function () {
                    if (data.errorcode == 0) {
                        window.location = '/admin/article/show.html';
                        return false;
                    }
                });
            }
        });
        return false;
    });
});

