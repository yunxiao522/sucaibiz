$(function () {
    function getArticleComment(article_id, page, type, obj) {
        var hash = md5(article_id);
        var order = $('input[type=checkbox]:checked').val();
        if (type == 'getone') {
            var comment_id = obj.parent().attr('comment_id');
            var data = {hash: hash, order: order, comment_id: comment_id ,type:type};
        } else if (type == 'getmore') {
            var comment_id = obj.parent().attr('comment_id');
            var data = {hash: hash, order: order, comment_id: comment_id ,type:type};
        } else if(type == '') {

        }
        $.ajax({
            url: '/getcomment.html',
            type: 'post',
            data: data,
            success: function (e) {

            }
        });

    }
});