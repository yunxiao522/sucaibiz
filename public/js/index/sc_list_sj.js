$(function () {
   //搜索按钮点击事件
   $('#search').on('click',function () {
        var keyword = $('input[name="keyword"]').val();
        if(keyword != ''){
            window.open('/search?keyword='+keyword);
        }
   });
   //处理导航标题高度问题
    var column_list_height = $('.column').height();
    $('.column .nav-title').css('height',column_list_height+'px');
    $('.column .nav-title').css('line-height',column_list_height+'px');
    var column_list_height = $('.color').height();
    $('.color .nav-title').css('height',column_list_height+'px');
    $('.color .nav-title').css('line-height',column_list_height+'px');
    //生成随机颜色代码
    function getRandomColor() {
        var r = Math.floor(Math.random()*256);
        var g = Math.floor(Math.random()*256);
        var b = Math.floor(Math.random()*256);

        if(r < 16){
            r = "0"+r.toString(16);
        }else{
            r = r.toString(16);
        }
        if(g < 16){
            g = "0"+g.toString(16);
        }else{
            g = g.toString(16);
        }
        if(b < 16){
            b = "0"+b.toString(16);
        }else{
            b = b.toString(16);
        }
        return "#"+r+g+b;
    }
    //获取热门标签列表
    var hot_tag = $('.column_tag ul li');
    $.each(hot_tag,function () {
        var color = getRandomColor();
        $(this).css('background',color);
    });
    //处理热门文档
    var hot_article = $('.hot_article ul li');
    $.each(hot_article,function () {
        var num = parseInt($(this).children('.rank').text());
        if(num <= 3){
            $(this).children('.rank').css('background','red');
        }
    });
});