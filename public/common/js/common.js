$.sc = {
    ajax: function(u, t, d, c, a, b, l) {
        if (a == true) {
            d.token = localStorage.getItem('token');
        }
        if (t == 'put') {
            d._method = 'PUT';
            t = 'post';
        }
        if (t == 'delete') {
            d._method = 'DELETE';
            t = 'post';
        }
        $.ajax({
            url: u,
            type: t,
            data: d,
            async: true,
            beforeSend: function() {
                if (b != undefined) {
                    b();
                } else {
                    loading = layer.load(0, {shade: false});
                }
            },
            success: function(res) {
                var e = JSON.parse(res);
                c(e, d);
            },
            complete: function() {
                if (l != undefined) {
                    l();
                } else {
                    layer.close(loading);
                }

            }
        })
    },
};