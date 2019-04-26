$(function () {
    var id = $('#id').val();
    $.ajax({
        url:'/tag_incr.html',
        type:'post',
        data:{id:id}
    })
});