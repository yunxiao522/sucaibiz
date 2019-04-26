$(function () {
    layui.use(['form', 'layedit', 'laydate'], function(){
        var form = layui.form
            ,layer = layui.layer
            ,layedit = layui.layedit
            ,laydate = layui.laydate;

        // //日期


        //创建一个编辑器
        var editIndex = layedit.build('LAY_demo_editor');

        //自定义验证规则
        form.verify({
            name: function(value){
                if(value = ''){
                    return '计划名称不能为空';
                }else if(value.length > 20){
                    return '计划名称不能超过20个字符';
                }
            }
            ,fun_name: function (value) {
                if(value = ''){
                    return '函数名称不能为空';
                }else if(value.length >50){
                    return '函数名称不能超过50个字符';
                }
            }
            ,time: function (value) {
                if(value = ''){
                    return '计划名称不能为空';
                }else if(value.length > 20){
                    return '计划名称不能超过20个字符';
                }
            }
        });
        form.on('select(execute_type)', function(data){
            if(data.value == '6'){
                $('input[name="date"]').attr('placeholder',"yyyy-MM-dd H:i:s");
            }else if(data.value == '1'){
                $('input[name="date"]').attr('placeholder',"分钟数");
            }else if(data.value == '2'){
                $('input[name="date"]').attr('placeholder',"h:i:s");
            }else if(data.value == '3'){
                $('input[name="date"]').attr('placeholder',"星期几$h:i:s");
            }else if(data.value == '4'){
                $('input[name="date"]').attr('placeholder',"d$h:i:s");
            }else if(data.value == '5'){
                $('input[name="date"]').attr('placeholder',"m-d$h:i:s");
            }
        });
        //监听提交
        form.on('submit(demo1)', function(data){
            data.field.description = layedit.getContent(editIndex);
            $.ajax({
                url:'/admin/add_plan.html',
                type:'post',
                data:data.field,
                beforeSend: function () {
                    loading = layer.load(0, {shade: false});
                },
                success: function (e) {
                    var data = JSON.parse(e);
                    layer.msg(data.msg, {time: 1000}, function () {
                        if (data.errorcode == 0) {
                            parent.location.href="/admin/show_plan_list.html";
                        }
                    });
                },complete:function(){
                    layer.close(loading);
                }
            });
            return false;
        });


    });
});