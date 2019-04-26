window.onload = function () {
    $.init();
    //暂时关闭我的消息访问链接点击跳转
    $(document).on('click', '.msgurl', function () {
        layer.open({content: '敬请期待', skin: 'msg', time: 2});
        return false;
    });
    //搜索页输入框监听事件
    $(document).on('input propertychange', "input[name='keyword']", function () {
        if ($(this).val() == '') {
            $j('.btn').addClass('back');
            $j('.btn').html('返回');
        } else {
            $j('.btn').removeClass('back');
            $j('.btn').html('搜索');
        }
    });

    //初始化搜索事件
    function initSearch() {
        //初始化分页
        $('.search-type').children('ul')[0].dataset.page = 1;
        //初始化最大分页数
        $('.search-type').children('ul')[0].dataset.maxpage = 9999;
        //清空搜索内容列表
        $('.search-content-list ul li').remove();
        //隐藏我是底线
        $('.end-line').hide();
    }

    //搜索分类点击事件
    $(document).on('click', '.search-type ul li', function () {
        var type = $('.search-type ul li');
        type.each(function () {
            $(this).children('.solid').removeClass('this');
        });
        $(this).children('.solid').addClass('this');
        //获取搜索栏目类型
        var type_id = this.dataset.type;
        $(this).parent('ul')[0].dataset.type = type_id;
        initSearch();
        search();
    });

    //获取搜索热门词
    function getHotSearch() {
        $.ajax({
            url: '/wap/search/gethotsearch.html',
            type: 'get',
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                if (e.success && e.data.count != 0) {
                    var data = e.data.data;
                    $.each(data, function () {
                        $('.search-hot-list').append('<li class="search-li">' + this.keyword + '</li>')
                    });
                }
            },
            complete: function () {
                layer.close(loading);
            }
        });
    };

    //获取搜索历史记录
    function getHistorySearch() {
        //获取用户id
        var uid = localStorage.getItem('uid');
        if (uid != null) {
            //获取历史记录
            $.ajax({
                url: '/wap/search/getHistorySearch.html',
                type: 'get',
                data: {
                    uid: uid
                },
                beforeSend: function () {
                    loading = layer.open({type: 2, shade: false});
                },
                success: function (res) {
                    var e = JSON.parse(res);
                    if (e.success && e.data.count != 0) {
                        $('.search-history').show();
                        var data = e.data.data;
                        $.each(data, function () {
                            $('.search-history-list').append('<li class="search-li" >' + this.keyword + '</li>');
                        })
                    }
                },
                complete: function () {
                    layer.close(loading);
                }
            });
        }
    }

    //热门搜索记录和历史搜索记录点击事件
    $(document).on('click', '.search-li', function () {
        var keyword = $(this).html();
        $("input[name='keyword']").val(keyword);
        initSearch();
        search();
    });

    //搜索
    function search() {
        //获取分页
        var page = $('.search-type').children('ul')[0].dataset.page;
        //获取最大分页数
        var max_page = $('.search-type').children('ul')[0].dataset.maxpage;
        if (parseInt(page) >= parseInt(max_page)) {
            return false;
        }
        //获取分类类型
        var type = $('.search-type').children('ul')[0].dataset.type;
        //获取搜索词
        var keyword = $("input[name='keyword']").val();
        //获取用户id
        var uid = localStorage.getItem('uid');
        if (keyword != '') {
            $.ajax({
                url: '/wap/search/search.html',
                type: 'get',
                data: {
                    keyword: keyword,
                    type: type,
                    page: page,
                    uid: uid
                },
                beforeSend: function (res) {
                    //隐藏热门搜索
                    $('.search-hot').hide();
                    //隐藏历史搜索
                    $('.search-history').hide();
                    //隐藏我是底线
                    $('.end-line').hide();
                    //显示搜索加载动画
                    $('.search-loading').show();
                    //隐藏空列表
                    $('.search-empty').hide();
                    //删除search-clear
                    $('.search-clear').remove();
                    //显示搜索列表
                    $('.search-content-list').show();
                },
                success: function (res) {
                    var e = JSON.parse(res);
                    if (e.success) {
                        $('.search-type').children('ul')[0].dataset.maxpage = e.data.max_page;
                        $('.search-type').children('ul')[0].dataset.page = parseInt(e.data.current_page) + 1;
                        if (e.data.count != 0) {
                            $('.search-content-list').show();
                            $.each(e.data.data, function () {
                                if (this.type == 1) {
                                    //组合item内容
                                    var item = '<li class="sj">' +
                                        '<img src="' + this.litpic + '" alt="">' +
                                        '</li>';
                                    $('.search-content-list ul').append(item);
                                } else if (this.type == 2) {
                                    //组合item内容
                                    var item = '<li class="zx">' +
                                        '<img src="' + this.litpic + '" alt="' + this.title + '">' +
                                        '<div class="zx-info"><div class="zx-title">' + this.title + '</div><div class="zx-more"><div class="zx-time">' + this.pubdate + '</div><div class="zx-column">' + this.column + '</div></div></div>' +
                                        '</li>';
                                    $('.search-content-list ul').append(item);
                                } else if (this.type == 3) {
                                    //组合item内容
                                    var item = '<li class="bz">' +
                                        '<img src="' + this.litpic + '" alt="' + this.title + '">' +
                                        '<div class="bz-title">' + this.title + '</div>' +
                                        '</li>';
                                    $('.search-content-list ul').append(item);
                                }
                            });
                            $('.search-content-list ul').append('<div style="width:100%;height:1px;clear:both;" class="search-clear"></div>');
                            if (parseInt(e.data.max_page) - 1 == parseInt(e.data.current_page) || parseInt(e.data.max_page) == 1) {
                                $('.end-line').show();
                            }
                        } else {
                            $('.search-content-list').hide();
                            $('.search-empty').show();
                        }
                    }
                },
                complete: function () {
                    //隐藏搜索加载动画
                    $('.search-loading').hide();
                }
            });
        } else {
            //显示热门搜索
            $('.search-hot').show();
        }
    };

    //清空搜索历史记录按钮的点击事件
    $(document).on('click', '.search-history-clear', function () {
        //获取用户id
        var uid = localStorage.getItem('uid');
        if (uid != null) {
            //发送请求
            $.ajax({
                url: '/wap/search/delHistorySearch.html',
                type: 'post',
                data: {
                    uid: uid
                },
                beforeSend: function () {
                    loading = layer.open({type: 2, shade: false});
                },
                success: function (res) {
                    var e = JSON.parse(res);
                    layer.open({content: e.msg, skin: 'msg', time: 2});
                    if (e.success) {
                        $('.search-history').hide();
                    }
                },
                complete: function () {
                    layer.close(loading);
                }
            })
        }
    });
    //搜索按钮绑定事件
    $(document).on('click', '#so', function () {
        initSearch();
        search();
    });
    //账号与隐私页面我的设备点击事件
    $(document).on('click', '.info-device', function () {
        //页面层
        layer.open({
            type: 1
            , content: '<div class="info-device-title">设备纠错</div><di</div>'
            , anim: 'up'
            , style: 'left:0; width:80%;height: 200px; padding:10px 0; border:none;'
        });
    });
    //账号也隐私页面发言显示设备小尾巴开关点击事件
    $(document).on('click', '#set_show_device', function () {
        var set_status = $(this).prop('checked');
        if (set_status) {
            localStorage.setItem('show_device_status', 1);
        } else {
            localStorage.setItem('show_device_status', 2);
        }
    });
    //账号与隐私页面发言显示地理位置开关点击事件
    $(document).on('click', '#set_show_site', function () {
        var set_status = $(this).prop('checked');
        if (set_status) {
            localStorage.setItem('show_site_status', 1);
        } else {
            localStorage.setItem('show_site_status', 2);
        }
    });
    //switch开关点击事件
    $(document).on('change', '.mui-switch', function () {
        var checked_status = $(this).prop('checked');
        if (checked_status) {
            $(this).attr('checked', '');
        } else {
            $(this).attr('checked', 'checked');
        }
    });
    //登录页面点击查看密码事件
    $(document).on('click', '.show-password', function () {
        if (this.dataset.type == 'hide') {
            this.dataset.type = 'show';
            $(this).children('img').attr('src', '/public/jpg/mobile/hide-password.png');
            $(this).siblings('input').attr('type', 'text');
        } else {
            this.dataset.type = 'hide';
            $(this).children('img').attr('src', '/public/jpg/mobile/show-password.png');
            $(this).siblings('input').attr('type', 'password');
        }
    });
    //登录页面登录方法
    $(document).on('click', '.logining', function () {
        var username = $("input[name='username']").val();
        var password = $("input[name='password']").val();
        var pwd = hex_sha1(password);
        $.ajax({
            url: '/wap/login/login.html',
            type: 'post',
            data: {
                username: username,
                password: pwd
            },
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false})
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                setTimeout(function () {
                    if (e.success) {
                        localStorage.uid = e.data.token;
                        if (e.url == null) {
                            history.back();
                        } else {
                            $.router.load(e.url);
                        }

                    }
                }, 2000);
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //修改按钮点击事件
    $(document).on('click', '.edit-username-button', function () {
        //获取昵称
        var nickname = $('.edit-nickname-input').val();
        edit_user_info({nickname: nickname}, function (e) {
            history.back();
        })
    });
    //修改QQ账号页面按钮点击事件
    $(document).on('click', '.edit-qq-button', function () {
        var qq = $('.edit-qq-input').val();
        edit_user_info({qq: qq}, function (e) {
            layer.open({content: e.msg, skin: 'msg', time: 2});
            setTimeout(function () {
                if (e.success) {
                    history.back();
                }
            }, 2000);
        });
    });
    //修改密码按钮点击事件
    $(document).on('click', '.edit-password-button', function () {
        //获取输入的密码
        var newpwd = $("input[name='newpwd']").val();
        var oldpwd = $("input[name='oldpwd']").val();
        var verifypwd = $("input[name='verifypwd']").val();
        if (oldpwd == '') {
            layer.open({content: '原密码不能为空', skin: 'msg', time: 2});
            return false;
        }
        if (newpwd == '') {
            layer.open({content: '新密码不能为空', skin: 'msg', time: 2});
            return false;
        }
        if (verifypwd == '') {
            layer.open({content: '二次输入的密码不能为空', skin: 'msg', time: 2});
            return false;
        }
        //获取用户id
        var uid = localStorage.getItem('uid');
        //发送请求
        $.ajax({
            url: '/wap/user/editPassword.html',
            type: 'post',
            data: {
                uid: uid,
                newpwd: hex_sha1(newpwd),
                oldpwd: hex_sha1(oldpwd),
                verifypwd: hex_sha1(verifypwd)
            },
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                setTimeout(function () {
                    if (e.success) {
                        history.back();
                    }
                }, 2000);
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //换绑手机页获取手机验证码按钮点击事件
    $(document).on('click', '.edit-phone-code-getcode button', function () {
        //获取换绑后的手机号码
        var phone = $("input[name='phone']").val();
        //判断换绑后的手机号和原有手机号是否一致
        var old_phone = $('.edit-phone-current')[0].dataset.phone;
        if (parseInt(phone) == parseInt(old_phone)) {
            layer.open({content: '换绑后的手机号不能是原手机号', skin: 'msg', time: 2});
            return false;
        }
        //获取用户id
        var uid = localStorage.getItem('uid');
        //发送手机验证码请求
        $.ajax({
            url: '/wap/user/sendPhoneCode.html',
            type: 'post',
            data: {
                phone: phone,
                uid: uid
            },
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                if (e.success) {
                    var time = 60;
                    var current_time = 0;
                    var send = setInterval(function () {
                        current_time++;
                        if (current_time >= 60) {
                            current_time = 0;
                            $('.edit-phone-code-getcode button').html('获取验证码');
                            $('.edit-phone-code-getcode button').attr('disabled', false);
                            $('.edit-phone-code-getcode button').css('color', 'red');
                            clearInterval(send);
                        } else {
                            $('.edit-phone-code-getcode button').html((60 - current_time) + '秒后重发');
                            $('.edit-phone-code-getcode button').attr('disabled', true);
                            $('.edit-phone-code-getcode button').css('color', '#eeeeee');
                        }
                    }, 1000);
                }
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //换绑邮箱页面获取邮箱验证码按钮点击事件
    $(document).on('click', '.edit-email-code-getcode button', function () {
        //获取换绑后的邮箱地址
        var email = $("input[name='email']").val();
        //判断换绑后的邮箱地址和原有邮箱地址是否一致
        var old_email = $('.edit-email-current')[0].dataset.email;
        if (old_email == email) {
            layer.open({content: '换绑后的邮箱地址不能是原邮箱地址', skin: 'msg', time: 2});
            return false;
        }
        //获取用户id
        var uid = localStorage.getItem('uid');
        //发送邮箱验证码请求
        $.ajax({
            url: '/wap/user/sendEmailCode.html',
            type: 'post',
            data: {
                email: email,
                uid: uid
            },
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                if (e.success) {
                    var time = 60;
                    var current_time = 0;
                    var send = setInterval(function () {
                        current_time++;
                        if (current_time >= 60) {
                            current_time = 0;
                            $('this').html('获取验证码');
                            $('this').attr('disabled', false);
                            $('this').css('color', 'res');
                        } else {
                            $('this').html((60 - current_time) + '秒后重发');
                            $('this').attr('disabled', true);
                            $('this').css('color', '#eeeeee');
                        }
                    }, 1000);
                }
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //分享点击事件
    $(document).on('click', '.share', function () {
        //判断浏览器类型
        var ua = navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger/i) == "micromessenger") {
            layer.open({content: '请点击右上角分享按钮进行分享', skin: 'msg', time: 2});
            return false;
        }
        $('.shade').show();
        $('.am-share').show();
        $('.bar').css('z-index', '-1');
        $(".am-share").addClass("am-modal-active");
        if ($(".sharebg").length > 0) {
            $(".sharebg").addClass("sharebg-active");
        } else {
            $("body").append('<div class="sharebg"></div>');
            $(".sharebg").addClass("sharebg-active");
        }
        $(".sharebg-active,.shade").click(function () {
            $('.shade').hide();
            $(".am-share").removeClass("am-modal-active");
            setTimeout(function () {
                $(".sharebg-active").removeClass("sharebg-active");
                $(".sharebg").remove();
                $('.am-share').hide();
                $('.bar').css('z-index', '15');
            }, 300);
        })
    });

    //修改手机号码确认按钮
    $(document).on('click', '.edit-phone-button', function () {
        //获取用户id
        var uid = localStorage.getItem('uid');
        //获取换绑后的手机号码
        var phone = $("input[name='phone']").val();
        //获取手机的code
        var code = $("input[name='code']").val();
        $.ajax({
            url: '/wap/user/editUserPhone.html',
            type: 'post',
            data: {
                phone: phone,
                uid: uid,
                code: code
            },
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                setTimeout(function () {
                    if (e.success) {
                        history.back();
                    }
                });
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //修改邮件地址确认按钮
    $(document).on('click', '.edit-email-button', function () {
        //获取用户id
        var uid = localStorage.getItem('uid');
        //获取换绑后的邮箱地址
        var email = $("input[name='email']").val();
        //获取email的code
        var code = $("input[name='code']").val();
        $.ajax({
            url: '/wap/user/editUserEmail.html',
            type: 'post',
            data: {
                email: email,
                code: code,
                uid: uid
            },
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                setTimeout(function () {
                    if (e.success) {
                        history.back();
                    }
                })
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //评论支持点击事件
    $(document).on('click', '.praise', function () {
        var comment_id = this.dataset.id;
        //判断用户登录状态
        var uid = checkLogin();
        var data = {
            uid: uid,
            comment_id: comment_id
        };
        var that = this;
        //组合数据发送请求
        $.ajax({
            url: '/wap/comment/praiser.html',
            type: 'post',
            data: data,
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                if (e.success) {
                    if (e.data.type == 1) {
                        var text = '取消(' + e.data.num + ')';
                    } else {
                        var text = '支持(' + e.data.num + ')';
                    }
                    $(that).html(text);
                }
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //评论反对点击事件
    $(document).on('click', '.oppose', function () {
        var comment_id = this.dataset.id;
        //判断用户登录状态
        var uid = checkLogin();
        var data = {
            uid: uid,
            comment_id: comment_id
        };
        var that = this;
        $.ajax({
            url: '/wap/comment/oppose.html',
            type: 'post',
            data: data,
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false});
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                if (e.success) {
                    if (e.data.type == 1) {
                        var text = '取消(' + e.data.num + ')';
                    } else {
                        var text = '反对(' + e.data.num + ')';
                    }
                    $(that).html(text);
                }
            },
            complete: function () {
                layer.close(loading);
            }
        })
    });
    //监听页面跳转的pageid
    $(document).on("pageInit", function (e, pageId, $page) {
        //获取用户id
        var uid = localStorage.getItem('uid');
        //使用switch替换if else,提高效率
        switch (pageId) {
            case 'search':
                //获取热门搜索词
                getHotSearch();
                //隐藏搜索历史
                $('.search-history').hide();
                //获取历史搜索记录
                getHistorySearch();
                //隐藏搜索列表
                $('.search-content-list').hide();
                //监听搜索内容滚动事件
                $('.search-content-list').scroll(function () {
                    //判断元素是否存在，避免控制台报错
                    if ($('.search-clear').length > 0) {
                        var top = $('.search-clear').position().top;
                        var height = $(window).height();
                        if (top <= height && $j('.search-loading').is(':hidden')) {
                            search();
                        }
                    }
                });
                break;
            case 'me':
                get_user_info(function (e) {
                    if (e.success) {
                        $('.me-login').attr('href', 'html/user_info.html');
                        $('.me-face-img img').attr('src', e.data.face);
                        $('.me-face-name').html(e.data.nickname);
                    }
                });
                break;
            case 'user_info':
                //获取本地存储的设备名称
                var device_name = localStorage.getItem('device_name');
                if (device_name == null) {
                    var device_info = $.device;
                    device_name = $.device.os + $.device.osVersion;
                    localStorage.setItem('device_name', device_name);
                }
                //分配设备信息到页面
                $('.info-device').children('.info-cont').html(device_name);
                //获取发言是否显示小尾巴设置
                var show_device_status = localStorage.getItem('show_device_status');
                if (show_device_status == null) {
                    //初始化设置
                    localStorage.setItem('show_device_status', 1);
                }
                //分配设置信息到页面
                if (parseInt(show_device_status) == 1) {
                    $('#set_show_device').attr('checked', 'checked');
                } else {
                    $('#set_show_device').removeAttr('checked');
                }
                //获取发言是否显示地理位置设置
                var show_site_status = localStorage.getItem('show_site_status');
                if (show_site_status == null) {
                    //初始化设置
                    localStorage.setItem('show_site_status', 1);
                }
                //分配设置信息到页面
                if (parseInt(show_site_status) == 1) {
                    $('#set_show_site').attr('checked', 'checked');
                } else {
                    $('#set_show_site').removeAttr('checked');
                }
                get_user_info(function (e) {
                    if (e.success) {
                        $('.base').children('.info-title').html('ID：' + e.data.id);
                        $('.info-face')[0].dataset.id = e.data.token;
                        $('.base').children('.info-cont').html('注册时间：' + e.data.create_time);
                        $('.user_name').children('.info-cont').html(e.data.nickname);
                        $('.info-face').children('.info-cont').children('img').attr('src', e.data.face);
                        $('.info-phone').children('.info-center').html(e.data.phone);
                        $('.info-email').children('.info-center').html(e.data.email);
                    }
                });
                break;
            case 'edit_username':
                get_user_info(function (e) {
                    if (e.success) {
                        $('.edit-nickname-input').val(e.data.nickname);
                    }
                });
                break;
            case 'more_info':
                get_user_info(function (e) {
                    if (e.success) {
                        $('#sex').children('.info-cont').html(e.data.sex);
                        $('#qq').children('.info-cont').html(e.data.qq);
                    }
                });
                $j.scpicker('#sex', ['男', '女'], function (e) {
                    edit_user_info({sex: e}, function () {
                        $('#sex').children('.info-center').html(e);
                    });
                }, $('.info-sex').children('.info-center').html());
                break;
            case 'edit_qq':
                get_user_info(function (e) {
                    if (e.success) {
                        $('.edit-qq-input').val(e.data.qq);
                    }
                });
                break;
            case 'edit_phone':
                get_user_info(function (e) {
                    if (e.success) {
                        $('.edit-phone-current').html('您当前的手机号:' + e.data.phone);
                        $('.edit-phone-current')[0].dataset.phone = e.data.phone;
                    }
                });
                break;
            case 'edit_email':
                get_user_info(function (e) {
                    if (e.success) {
                        $('.edit-email-current').html('您当前的邮箱:' + e.data.email);
                        $('.edit-email-current')[0].dataset.email = e.data.email;
                    }
                });
                break;
            case 'comment':
                if (uid == null) {
                    $.router.back();
                    return false;
                }
                $('.comment-loading').hide();
                $('.end-line').hide();
                //初始化分页数据
                $('.comment-nav').children('ul')[0].dataset.page = 1;
                $('.comment-nav').children('ul')[0].dataset.count = 9999;
                //清空列表数据
                $('.comment-nav').children('ul').html();
                //获取默认数据
                var nav = $('.comment-nav').children('ul').children('li');
                $j.each(nav, function () {
                    if ($(this).hasClass('this')) {
                        var type = this.dataset.type;
                        getMyComment(type);
                    }
                });
                //绑定导航栏点击事件
                $(document).on('click', '.comment-nav ul li', function () {
                    var navli = $(this).siblings();
                    //循环节点列表
                    $j.each(navli, function () {
                        $(this).removeClass('this');
                    });
                    $(this).addClass('this');
                    //获取评论类型
                    var type = this.dataset.type;
                    //初始化分页数据
                    $('.comment-nav').children('ul')[0].dataset.page = 1;
                    $('.comment-nav').children('ul')[0].dataset.count = 9999;
                    //清空列表数据
                    $('.comment-list').children('ul').find('li').remove();
                    getMyComment(type);
                });

                //获取我的评论函数
            function getMyComment(type) {
                //获取当前页数及总页数
                var page = $('.comment-nav').children('ul')[0].dataset.page;
                var count = $('.comment-nav').children('ul')[0].dataset.count;
                if (page > count) {
                    return false;
                }
                //组合数据，发送请求
                var data = {
                    uid: uid,
                    type: type,
                    page: page,
                };
                $.ajax({
                    url: '/wap/comment/getMyComment.html',
                    type: 'post',
                    data: data,
                    beforeSend: function () {
                        $('.comment-loading').show();
                        $('.end-line').hide();
                        $('.comment-list').children('ul').find(".comment-clear").remove();
                    },
                    success: function (res) {
                        var e = JSON.parse(res);
                        $('.comment-nav').children('ul')[0].dataset.page = parseInt(e.data.current_page) + 1;
                        $('.comment-nav').children('ul')[0].dataset.count = e.data.max_page;
                        $('.comment-nav').children('ul')[0].dataset.type = data.type;
                        if (e.data.current_page == e.data.max_page) {
                            $('.end-line').show();
                        }
                        ;
                        //数据渲染到页面
                        var length = Object.keys(e.data.data).length;
                        for (var i = 0; i < length; i++) {
                            if (type == 1 || type == 2) {
                                var text = '<li>\n' +
                                    '                        <div class="comment-list-head">\n' +
                                    '                            <div class="comment-list-head-left">\n' +
                                    '                                <div class="comment-list-head-left-face">\n' +
                                    '                                    <img src="' + e.data.data[i].face + '" alt="' + e.data.data[i] + '">\n' +
                                    '                                </div>\n' +
                                    '                                <div class="comment-list-head-left-name m">我</div>\n' +
                                    '                            </div>\n' +
                                    '                            <div class="comment-list-head-right">' + e.data.data[i].tier + '楼</div>\n' +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-info">\n' +
                                    '                            <div class="comment-list-info-site">素材站' + e.data.data[i].city + '</div>\n' +
                                    '                            <div class="comment-list-info-time">' + e.data.data[i].create_time + '</div>\n' +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-content">\n' +
                                    '                            ' + e.data.data[i].content +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-operate">\n' +
                                    '                            <div class="comment-list-operate-oppose oppose" data-id="' + e.data.data[i].id + '">' + e.data.data[i].oppose + '</div>\n' +
                                    '                            <div class="comment-list-operate-praise praise" data-id="' + e.data.data[i].id + '">' + e.data.data[i].praiser + '</div>\n' +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-article">\n' +
                                    '                            原文：「' + e.data.data[i].article_info.title + '」\n' +
                                    '                        </div>\n' +
                                    '                    </li>';
                            } else if (type == 3) {
                                var text = '<li>\n' +
                                    '                        <div class="comment-list-head">\n' +
                                    '                            <div class="comment-list-head-left">\n' +
                                    '                                <div class="comment-list-head-left-face">\n' +
                                    '                                    <img src="' + e.data.data[i].face + '" alt="' + e.data.data[i] + '">\n' +
                                    '                                </div>\n' +
                                    '                                <div class="comment-list-head-left-name m">' + e.data.data[i].nickname + '</div>\n' +
                                    '                            </div>\n' +
                                    '                            <div class="comment-list-head-right">' + e.data.data[i].tier + '楼</div>\n' +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-info">\n' +
                                    '                            <div class="comment-list-info-site">素材站' + e.data.data[i].city + '</div>\n' +
                                    '                            <div class="comment-list-info-time">' + e.data.data[i].create_time + '</div>\n' +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-content">\n' +
                                    '                            ' + e.data.data[i].content +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-operate">\n' +
                                    '                            <div class="comment-list-operate-oppose oppose" data-id="' + e.data.data[i].id + '">' + e.data.data[i].oppose + '</div>\n' +
                                    '                            <div class="comment-list-operate-praise praise" data-id="' + e.data.data[i].id + '">' + e.data.data[i].praiser + '</div>\n' +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-main">\n' +
                                    '                            <div class="comment-list-head">\n' +
                                    '                            <div class="comment-list-head-left">\n' +
                                    '                                <div class="comment-list-head-left-face">\n' +
                                    '                                    <img src="' + e.data.data[i].face + '" alt="' + e.data.data[i] + '">\n' +
                                    '                                </div>\n' +
                                    '                                <div class="comment-list-head-left-name m">我</div>\n' +
                                    '                            </div>\n' +
                                    '                                <div class="comment-list-head-right">' + e.data.data[i].tier + '楼</div>\n' +
                                    '                            </div>\n' +
                                    '                            <div class="comment-list-info">\n' +
                                    '                                <div class="comment-list-info-site">素材站' + e.data.data[i].main.city + '</div>\n' +
                                    '                                <div class="comment-list-info-time">' + e.data.data[i].main.create_time + '</div>\n' +
                                    '                            </div>\n' +
                                    '                            <div class="comment-list-content">\n' +
                                    '                                ' + e.data.data[i].main.content +
                                    '                            </div>\n' +
                                    '                            <div class="comment-list-operate">\n' +
                                    '                                <div class="comment-list-operate-oppose oppose" data-id="' + e.data.data[i].main.id + '">' + e.data.data[i].main.oppose + '</div>\n' +
                                    '                                <div class="comment-list-operate-praise praise" data-id="' + e.data.data[i].main.id + '">' + e.data.data[i].main.praiser + '</div>\n' +
                                    '                            </div>\n' +
                                    '                        </div>\n' +
                                    '                        <div class="comment-list-article">\n' +
                                    '                            原文：「' + e.data.data[i].article_info.title + '」\n' +
                                    '                        </div>\n' +
                                    '                    </li>';
                            }
                            $('.comment-list').children('ul').append(text);
                        }
                        $('.comment-list').children('ul').append('<div style="width:100%;height:1px;clear:both;" class="comment-clear"></div>');

                    },
                    complete: function () {
                        $('.comment-loading').hide();
                    }
                });
            }

                //监听搜索内容滚动事件
                $('.comment-list').scroll(function () {
                    //判断元素是否存在，避免控制台报错
                    if ($('.comment-clear').length > 0) {
                        var top = $('.comment-clear').position().top;
                        var height = $(window).height();
                        if (top <= height && $j('.comment-loading').is(':hidden')) {
                            console.log(1);
                            var type = $('.comment-nav').children('ul')[0].dataset.type;
                            getMyComment(type);
                        }
                    }
                });
                break;
            case 'callback':
                if (uid == null) {
                    $.router.back();
                    return false;
                }
                break;
            case 'download':
                if (uid == null) {
                    $.router.back();
                    return false;
                }
                getMydown();

                //获取我的下载数据方法
            function getMydown() {
                var page = $('.download-content')[0].dataset.page;
                var count = $('.download-content')[0].dataset.max;
                if (page > count) {
                    return false;
                }
                var data = {
                    uid: uid,
                    page: page
                };
                $.ajax({
                    url: '/wap/download/getMyDownload.html',
                    type: 'post',
                    data: data,
                    beforeSend: function () {
                        $('.download-loading').show();
                        $('.end-line').hide();
                        $('.download-content').children('ul').find(".download-clear").remove();
                    },
                    success: function (res) {
                        var e = JSON.parse(res);
                        $('.download-content')[0].dataset.page = parseInt(e.data.current_page) + 1;
                        $('.download-content')[0].dataset.max = e.data.max_page;
                        if (e.data.current_page == e.data.max_page) {
                            $('.end-line').show();
                        }
                        ;
                        var length = Object.keys(e.data.data).length;
                        for (var i = 0; i < length; i++) {
                            var text = '<li>\n' +
                                '                        <div class="down-litpic">\n' +
                                '                            <img src="' + e.data.data[i].file_url + '" alt="">\n' +
                                '                        <div class="down-title">\n' +
                                '                            [' + e.data.data[i].column + ']' + e.data.data[i].article + '\n' +
                                '                        </div>\n' +
                                '                        </div>\n' +
                                '                    </li>';
                            $('.download-content').children('ul').append(text);
                        }
                        $('.download-content').children('ul').append('<div style="width:100%;height:1px;clear:both;" class="download-clear"></div>');
                    },
                    complete: function () {
                        $('.download-loading').hide();
                    }
                })
            }

                //监听下载列表滚动事件
                $('.download-content').scroll(function () {
                    //判断元素是否存在，避免控制台报错
                    if ($('.download-clear').length > 0) {
                        var top = $('.download-clear').position().top;
                        var height = $(window).height();
                        if (top <= height && $j('.download-loading').is(':hidden')) {
                            getMydown();
                        }
                    }
                });
                break;
            case 'like':
                //获取默认收藏类型
                var list = $('.like-nav').children('ul').children('li');
                $j.each(list, function () {
                    if ($(this).hasClass('this')) {
                        var type = this.dataset.type;
                        //获取默认类型的收藏列表数据
                        init_my_like(type);
                        return false;
                    }
                });
                //监听收藏列表滚动事件
                $('.like-content').scroll(function () {
                    //判断元素是否存在，避免控制台报错
                    if ($('.clear').length > 0) {
                        var top = $('.clear').position().top;
                        var height = $(window).height();
                        if (top <= height && $j('.loading').is(':hidden')) {
                            getMydown();
                        }
                    }
                });
                break;
            case 'msg':
                //获取默认消息类型
                var list = $('.msg-nav').children('ul').children('li');
                $j.each(list, function () {
                    if ($(this).hasClass('this')) {
                        var type = this.dataset.type;
                        //获取默认消息类型的数据
                        init_my_msg(type);
                        return false;
                    }
                });
                //监听消息列表滚动事件
                $('.msg-content').scroll(function () {
                    //判断元素是否存在，避免控制台报错
                    if ($('.clear').length > 0) {
                        var top = $('.clear').position().top;
                        var height = $(window).height();
                        if (top <= height && $j('.loading').is(':hidden')) {
                            var type = $('.msg-nav')[0].dataset.type;
                            getMyMsg(type);
                        }
                    }
                });
                break;
            case 'msg-detail':
                //获取地址栏参数
                var params = getParams();
                if (params.id == undefined) {
                    layer.open({content: e.msg, skin: 'msg', time: 2});
                    $.router.back();
                    return false;
                }
                var id = params.id;
                //组合数据发送请求获取数据
                var data = {
                    id: id,
                    uid: uid
                };
                $.ajax({
                    url: '/wap/msg/getMsgContent.html',
                    type: 'get',
                    data: data,
                    beforeSend: function () {
                        loading = layer.open({type: 2, shade: false})
                    },
                    success: function (res) {
                        var e = JSON.parse(res);
                        console.log(e);
                    },
                    complete: function () {
                        layer.close(loading);
                    }
                });
                break;
            case 'set_column':
                //获取地址栏参数
                var params = getParams();
                var id = params.id;
                var type = params.type;
                if(id == undefined || type == undefined){
                    layer.open({content:'缺少参数',skin:'msg',time:2});
                    $.router.back();
                    return false;
                }
                //根据类型获取地址栏参数
                switch(type){
                    case 'sj':
                        var column = localStorage.getItem('phone_column_list');
                        var column_reject = localStorage.getItem('phone_column_list_reject');
                        var column_raw = localStorage.getItem('phone_column_list_raw');
                        break;
                    case 'zx':
                        var column = localStorage.getItem('zx_column_list');
                        var column_reject = localStorage.getItem('zx_column_list_reject');
                        var column_raw = localStorage.getItem('zx_column_list_raw');
                        break;
                }
                //处理获得到的数据
                var column_list = JSON.parse(column);
                var column_reject_list = JSON.parse(column_reject);
                var column_raw_list = JSON.parse(column_raw);
                new_column_list = getArr(JSON.parse(JSON.stringify(column_raw_list)));
                new_column_reject_list = getArr(JSON.parse(JSON.stringify(column_reject_list)));
                //渲染设置的栏目列表
                //清空原有数据
                $('.me-column li').remove();
                $('.me-column .clear').remove();
                //循环渲染数据
                $j.each(column_raw_list, function () {
                    var text = '<li data-id="' + this.id + '">' + this.type_name + '<img class="close-column" src="/public/jpg/mobile/column-close.png"></li>'
                    $('.me-column').append(text);
                });
                $('.me-column').append('<div class="clear" style="width:100%;height:1px;clear:both;"></div>');
                //渲染我排除的手机栏目列表
                //清空原有数据
                $('.me-column-reject li').remove();
                //循环渲染数据
                $j.each(column_reject_list, function () {
                    var text = '<li data-id="' + this.id + '">' + this.type_name + '</li>';
                    $('.me-column-reject').append(text);
                });
                //删除按钮点击事件
                $('.close-column').on('click', function () {
                    var id = $(this).parent('li')[0].dataset.id;
                    if (id == 'undefined' || id == 24) return false;
                    $j.each(new_column_list, function () {
                        if (this.id == id) {
                            new_column_list.splice(new_column_list.indexOf(this), 1);
                            new_column_reject_list.push(this);
                            var text = '<li data-id="' + this.id + '" class="add">' + this.type_name + '</li>';
                            $('.me-column-reject').append(text);
                        }
                    });
                    $(this).parent().remove();
                });
                //我排除的栏目列表li点击事件
                $(document).on('click', '.me-column-reject .add', function () {
                    var id = this.dataset.id;
                    $j.each(new_column_reject_list, function () {
                        if (this.id == id) {
                            new_column_reject_list.splice(new_column_reject_list.indexOf(this), 1);
                            new_column_list.push(this);
                            var text = '<li data-id="' + this.id + '">' + this.type_name + '<img class="close-column" src="/public/jpg/mobile/column-close.png"></li>'
                            $('.me-column .clear').remove();
                            $('.me-column').append(text);
                            $('.me-column').append('<div class="clear" style="width:100%;height:1px;clear:both;"></div>');
                        }
                    });
                    $(this).remove();
                    $('.me-column .close-column').show();
                });
                //编辑保存按钮点击事件
                $('.set-column-edit').on('click', function () {
                    if (this.dataset.type == 1) {
                        $('.me-column .close-column').show();
                        $(this).html('保存');
                        this.dataset.type = 2;
                        $('.me-column-reject li').addClass('add');
                        return false;
                    } else {
                        $('.me-column .close-column').hide();
                        $(this).html('编辑');
                        this.dataset.type = 1;
                        //根据类型保存数据到本地存储
                        switch(type){
                            case 'sj':
                                localStorage.setItem('phone_column_list_reject', JSON.stringify(new_column_reject_list));
                                localStorage.setItem('phone_column_list_raw', JSON.stringify(new_column_list));
                                break;
                            case 'zx':
                                localStorage.setItem('zx_column_list_reject', JSON.stringify(new_column_reject_list));
                                localStorage.setItem('zx_column_list_raw', JSON.stringify(new_column_list));
                                break;
                        }
                        $('.me-column-reject li').removeClass('add');
                    }
                });
                break;
            case 'index':
                //首页回调函数，判断是否存在手机导航条，存在则刷新内容
                if ($('.sj-class').length > 0) {
                    var phone_column_list = localStorage.getItem('phone_column_list_raw');
                    var phone_column_arr = JSON.parse(phone_column_list);
                    $('.sj-class li').remove();
                    $j.each(phone_column_arr, function () {
                        if (this.id == undefined) {
                            var text = '<li data-id="' + this.id + '" class="this">' + this.type_name + '</li>';
                        } else {
                            var text = '<li data-id="' + this.id + '">' + this.type_name + '</li>';
                        }
                        $('.sj-class').children('ul').append(text);
                    });
                    //监听下载列表滚动事件
                    $('.sj-content').scroll(function () {
                        //判断元素是否存在，避免控制台报错
                        if ($('.clear').length > 0) {
                            var top = $('.clear').position().top;
                            var height = $(window).height();
                            if (top <= height && $j('.loading').is(':hidden')) {
                                var t = $('.sj-content')[0].dataset.type;
                                getPhoneList(t);
                            }
                        }
                    });
                    //获取默认类型获取数据
                    $j.each($('.sj-class li'), function () {
                        if ($(this).hasClass('this')) {
                            var type =this.dataset.id;
                            init_get_phone_list(type);
                        }
                    });
                }
                if ($('.zx-class').length > 0) {
                    var zx_column_list = localStorage.getItem('zx_column_list_raw');
                    var zx_column_arr = JSON.parse(zx_column_list);
                    $('.zx-class li').remove();
                    $j.each(zx_column_arr, function () {
                        if (this.id == 24) {
                            var text = '<li data-id="' + this.id + '" class="this">' + this.type_name + '</li>';
                        } else {
                            var text = '<li data-id="' + this.id + '">' + this.type_name + '</li>';
                        }
                        $('.zx-class').children('ul').append(text);
                    });
                    //监听下载列表滚动事件
                    $('.zx-content').scroll(function () {
                        //判断元素是否存在，避免控制台报错
                        if ($('.clear').length > 0) {
                            var top = $('.clear').position().top;
                            var height = $(window).height();
                            if (top <= height && $j('.loading').is(':hidden')) {
                                var t = $('.zx-content')[0].dataset.type;
                                get_zx_list(t);
                            }
                        }
                    });
                    //获取默认类型获取数据
                    $j.each($('.zx-class li'), function () {
                        if ($(this).hasClass('this')) {
                            var t = this.dataset.id;
                            init_get_zx_list(t);
                        }
                    });
                }
                break;
            case 'sj-detail':
                //初始化位置信息
                var scroll_top = 0;
                $j('.sj-content').scrollTop();
                //获取参数
                var params = getParams();
                var id = params.id;
                if(id == undefined){
                    layer.open({content:'参数错误',skin:'msg',time:2});
                    $.router.back();
                    return false;
                }
                var p = params.p;
                get_phone_detail(id,p);
                //监听页面滚动
                $('.sj-detail-content').scroll(function () {
                    var top = $('.images').position().top;
                    //底部栏
                    if((top * -1) > (scroll_top * -1)){
                        $('.article-bottom').removeClass('article-bottom-out');
                        $('.article-bottom').addClass('article-bottom-active');
                    }else{
                        $('.article-bottom').removeClass('article-bottom-active');
                        $('.article-bottom').addClass('article-bottom-out');
                    }
                    //顶部标题栏
                    if(top == 0){
                        $('.sj-detail-title').removeClass('sj-detail-title-active');
                        $('.sj-detail-title').addClass('sj-detail-title-out');
                    }else{
                        $('.sj-detail-title').removeClass('sj-detail-title-out');
                        $('.sj-detail-title').addClass('sj-detail-title-active');
                    }
                    scroll_top = top;
                });
                $(document).on('click','.sj-detail-related ul li',function () {
                    var id = this.dataset.id;
                    get_phone_detail(id,0);
                });
                break;
            case 'perfect-info':
                var params = getParams();
                var token = params.token;
                if(token == undefined){
                    $.router.back();
                    return false;
                }
                $('input[name="token"]').val(token);
                break;
            case 'article-comment':
                var params = getParams();
                var id = params.id;
                $.ajax({
                    url:'http://api.sucai.biz/v1/article/getTitle/'+id,
                    type:'get',
                    dataType:'json'
                    ,beforeSend:function(){
                        loading = layer.open({type: 2, shade: false});
                    },success:function(res){
                        var e = JSON.parse(res);
                        console.log(e);
                    },complete:function () {
                        layer.close(loading);
                    }
                })
                break;
        }

    });
    //我的收藏导航点击事件
    $(document).on('click', '.like-nav ul li', function () {
        var nav = $(this).siblings('li');
        $j.each(nav, function () {
            if ($(this).hasClass('this')) {
                $(this).removeClass('this');
            }
        });
        $(this).addClass('this');
        var type = this.dataset.type;
        $('.like-nav')[0].dataset.type = type;
        init_my_like(type);
    });

    //初始化我的收藏列表数据方法
    function init_my_like(type) {
        $('.like-nav')[0].dataset.page = 1;
        $('.like-nav')[0].dataset.maxpage = 999;
        $('.like-content').children('ul').children('li').remove();
        $('.loading').hide();
        $('.end-line').hide();
        getMyLike(type);
    }

    //获取我的收藏列表数据方法
    function getMyLike(type) {
        var uid = localStorage.getItem('uid');
        var page = $('.like-nav')[0].dataset.page;
        var maxpage = $('.like-nav')[0].dataset.maxpage;
        if (page > maxpage) {
            return false;
        }
        //组合数据发送请求
        var data = {
            type: type,
            uid: uid,
            page: page
        };
        $.ajax({
            url: '/wap/like/getMyLike.html',
            type: 'get',
            data: data,
            beforeSend: function () {
                $('.loading').show();
                $('.like-content').children('ul').find(".clear").remove();
            },
            success: function (res) {
                var e = JSON.parse(res);
                $('.like-nav')[0].dataset.page = parseInt(e.data.current_page) + 1;
                $('.like-nav')[0].dataset.maxpage = e.data.max_page;
                //循环列表数据
                var length = Object.keys(e.data.data).length;
                for (var i = 0; i < length; i++) {
                    if (type == 1) {
                        var text = ' <li class="sj">\n' +
                            '                        <img src="' + e.data.data[i].img_url + '" alt="">\n' +
                            '                    </li>';
                    } else if (type == 2) {
                        var text = '<li class="zx">\n' +
                            '                        <img src="' + e.data.data[i].article_info.litpic + '" alt="">\n' +
                            '                        <div class="like-info">\n' +
                            '                            <div class="like-title">' + e.data.data[i].article_info.title + '</div>\n' +
                            '                            <div class="like-more">\n' +
                            '                                <div class="like-time">' + e.data.data[i].article_info.pubdate + '</div>\n' +
                            '                                <div class="like-column">' + e.data.data[i].article_info.column + '</div>\n' +
                            '                            </div>\n' +
                            '                        </div>\n' +
                            '                    </li>';
                    } else if (type == 3) {
                        var text = '<li class="bz">\n' +
                            '                        <img src="' + e.data.data[i].img_url + '" alt="">\n' +
                            '                        <div class="like-title">' + e.data.data[i].title + '</div>\n' +
                            '                    </li>';
                    }
                    $('.like-content').children('ul').append(text);
                }
                //判断是否需要显示我是底线
                if (e.data.current_page == e.data.max_page) {
                    $('.end-line').show();
                }
                $('.like-content').children('ul').append('<div style="width:100%;height:1px;clear:both;" class="clear"></div>')
            },
            complete: function () {
                $('.loading').hide();
            }
        })
    }

    //我的消息页面导航点击事件
    $(document).on('click', '.msg-nav ul li', function () {
        var nav = $(this).siblings();
        $j.each(nav, function () {
            $(this).removeClass('this');
        });
        $(this).addClass('this');
        var type = $(this)[0].dataset.type;
        init_my_msg(type);
    });

    //初始化我的消息列表数据方法
    function init_my_msg(type) {
        $('.msg-nav')[0].dataset.page = 1;
        $('.msg-nav')[0].dataset.maxpage = 99999;
        $('.msg-content').children('ul').children('li').remove();
        getMyMsg(type);
    }

    //获取我的消息列表方法
    function getMyMsg(type) {
        var uid = localStorage.getItem('uid');
        var page = $('.msg-nav')[0].dataset.page;
        var count = $('.msg-nav')[0].dataset.max;
        if (page > count) {
            return false;
        }
        //组合数据，发送请求获取数据
        var data = {
            uid: uid,
            page: page,
            type: type
        };
        $.ajax({
            url: '/wap/msg/getMyMsg.html',
            type: 'get',
            data: data,
            beforeSend: function () {
                $('.loading').show();
                $('.msg-content').children('ul').find(".clear").remove();
            },
            success: function (res) {
                var e = JSON.parse(res);
                $('.msg-nav')[0].dataset.page = parseInt(e.data.current_page) + 1;
                $('.msg-nav')[0].dataset.maxpage = e.data.max_page;
                $('.msg-nav')[0].dataset.type = type;
                //循环列表数据
                var length = Object.keys(e.data.data).length;
                for (var i = 0; i < length; i++) {
                    var status = '';
                    if (e.data.data[i].status == 1) {
                        var status = status + '<div class="msg-list-status-unread"></div>';
                    }
                    var text = '<li class="msg-li" data-id="' + e.data.data[i].id + '">\n' +
                        '                        <div class="msg-list">\n' +
                        '                            <div class="msg-list-status">\n' + status +
                        '                            </div>\n' +
                        '                            <div class="msg-list-left">\n' +
                        '                                <img src="' + e.data.data[i].source_info.face + '" alt="">\n' +
                        '                            </div>\n' +
                        '                            <div class="msg-list-right">\n' +
                        '                                <div class="msg-list-right-head">\n' +
                        '                                    <div class="msg-list-right-head-nickname">' + e.data.data[i].source_info.nickname + '</div>\n' +
                        '                                    <div class="msg-list-right-head-time">' + e.data.data[i].create_time + '</div>\n' +
                        '                                </div>\n' +
                        '                                <div class="msg-list-right-title">\n' +
                        '                                    ' + e.data.data[i].title + '\n' +
                        '                                </div>\n' +
                        '                            </div>\n' +
                        '                        </div>\n' +
                        '                    </li>';
                    $('.msg-content').children('ul').append(text);
                }
                //判断是否需要显示我是底线
                if (e.data.current_page == e.data.max_page) {
                    $('.end-line').show();
                }
                $('.msg-content').children('ul').append('<div style="width:100%;height:1px;clear:both;" class="clear"></div>');
            },
            complete: function () {
                $('.loading').hide();
            }
        })
    }

    // 把image 转换为 canvas对象
    function convertImageToCanvas(image) {
        // 创建canvas DOM元素，并设置其宽高和图片一样
        var canvas = document.createElement("canvas");
        canvas.width = image.width;
        canvas.height = image.height;
        // 坐标(0,0) 表示从此处开始绘制，相当于偏移。
        canvas.getContext("2d").drawImage(image, 0, 0);
        return canvas;
    }

    //我的消息列表点击事件
    $(document).on('click', '.msg-li', function () {
        var id = $(this)[0].dataset.id;
        $.router.load("msg_detail.html?id=" + id);
    });

    $(document).on('click','.detail-back',function () {
        $.router.back();
    });

    //获取地址栏参数方法
    function getParams(url) {
        var theRequest = new Object();
        if (!url)
            url = location.href;
        if (url.indexOf("?") !== -1) {
            var str = url.substr(url.indexOf("?") + 1) + "&";
            var strs = str.split("&");
            for (var i = 0; i < strs.length - 1; i++) {
                var key = strs[i].substring(0, strs[i].indexOf("="));
                var val = strs[i].substring(strs[i].indexOf("=") + 1);
                theRequest[key] = val;
            }
        }
        return theRequest;
    }

    setTimeout(function () {
        //获取默认类型获取数据
        $j.each($('.sj-class li'), function () {
            if ($(this).hasClass('this')) {
                var type = $(this)[0].dataset.type;
                init_get_phone_list(type);
            }
        });
    }, 100);
    //手机列表页导航栏点击事件
    $(document).on('click', '.sj-class li', function () {
        $('.sj-class li').removeClass('this');
        $(this).addClass('this');
        var type = this.dataset.id;
        init_get_phone_list(type);
    });

    //初始化获取手机列表数据方法
    function init_get_phone_list(type) {
        $('.sj-content ul li').remove();
        $('.sj-content ul .clear').remove();
        $('.sj-content')[0].dataset.page = 1;
        $('.sj-content')[0].dataset.maxpage = 999;
        getPhoneList(type);
    }

    function sjScroll() {
        //监听手机列表滚动事件
        $('.sj-content').on('scroll', function () {
            //判断元素是否存在，避免控制台报错
            if ($('.clear').length > 0) {
                var top = $('.clear').position().top;
                var height = $(window).height();
                if (top <= height && $j('.loading').is(':hidden')) {
                    var t = $('.sj-content')[0].dataset.type;
                    getPhoneList(t);
                }
            }
        });
    }

    //获取手机列表数据函数
    function getPhoneList(type) {
        var page = parseInt($('.sj-content')[0].dataset.page);
        var maxPage = parseInt($('.sj-content')[0].dataset.maxpage);
        if (page > maxPage) {
            return false;
        }
        sjScroll();
        $.ajax({
            url: Api.Common.GetArticleList + '/' +54,
            type: 'get',
            data: {
                type: type,
                page: page
            },
            beforeSend: function () {
                $('.loading').show();
                $('.sj-content ul .clear').remove();
                $('.sj-content .end-line').hide();
            },
            success: function (res) {
                var e = JSON.parse(res);
                if (!e.success) {
                    layer.open({content: e.msg, skin: 'msg', time: 2})
                    return false;
                }
                $('.sj-content')[0].dataset.page = parseInt(e.data.current_page) + 1;
                $('.sj-content')[0].dataset.maxpage = e.data.max_page;
                $('.sj-content')[0].dataset.type = type;
                //循环渲染数据
                $j.each(e.data.data, function () {
                    var text = '<li data-id="'+ this.id +'"><img src="' + this.litpic + '"></li>';
                    $('.sj-content ul').append(text);
                });
                $('.sj-content ul').append('<div style="width:100%;height:1px;clear:both;" class="clear"></div>');
                if (e.data.current_page == e.data.max_page) $('.sj-content .end-line').show();
            },
            complete: function () {
                $('.loading').hide();
            }
        })
    }
    //手机壁纸列表点击事件
    $(document).on('click','.sj-content ul li',function () {
        var id = this.dataset.id;
        $.router.load('/m/html/sj_detail.html?id='+id,true);
    });
    function zxScroll() {
        //监听手机列表滚动事件
        $('.zx-content').on('scroll', function () {
            //判断元素是否存在，避免控制台报错
            if ($('.clear').length > 0) {
                var top = $('.clear').position().top;
                var height = $(window).height();
                if (top <= height && $j('.loading').is(':hidden')) {
                    var t = $('.zx-content')[0].dataset.type;
                    get_zx_list(t);
                }
            }
        });
    }
    //资讯列表页面导航栏点击事件
    $(document).on('click', '.zx-class li', function () {
        $('.zx-class li').removeClass('this');
        $(this).addClass('this');
        var id = this.dataset.id;
        init_get_zx_list(id);
    });

    //初始化获取手机列表数据方法
    function init_get_zx_list(type) {
        $('.zx-content ul li').remove();
        $('.zx-content ul .clear').remove();
        $('.zx-content')[0].dataset.page = 1;
        $('.zx-content')[0].dataset.maxpage = 999;
        get_zx_list(type);

    }

    //获取资讯列表数据方法
    function get_zx_list(type) {
        var page = parseInt($('.zx-content')[0].dataset.page);
        var max_page = parseInt($('.zx-content')[0].dataset.maxpage);
        if (page > max_page) {
            return false;
        }
        zxScroll();
        $.ajax({
            url: Api.Common.GetArticleList + '/' +24,
            type: 'get',
            data: {
                page: page,
                type: type
            },
            beforeSend: function () {
                $('.loading').show();
                $('.zx-content ul .clear').remove();
                $('.end-line').hide();
            },
            success: function (res) {
                var e = JSON.parse(res);
                $('.zx-content')[0].dataset.page = parseInt(e.data.current_page) + 1;
                $('.zx-content')[0].dataset.maxpage = parseInt(e.data.max_page);
                $('.zx-content')[0].dataset.type = type;
                if (!e.success) {
                    layer.open({content: e.msg, skin: 'msg', time: 2});
                    return false;
                }
                $j.each(e.data.data, function () {
                    var text = '<li data-id="'+ this.id +'"><img src="'+ this.litpic +'" alt=""><div class="zx-more"><div class="zx-title">'+ this.title +'</div><div class="zx-info">'+ this.pubdate +'</div><div class="zx-column"></div></div></li>';
                    $('.zx-content ul').append(text);
                });
                $('.zx-content ul').append('<div style="width:100%;height:1px;clear:both;" class="clear"></div>');
                if (e.data.current_page == e.data.max_page) $('.zx-content .end-line').show();
            },
            complete: function () {
                $('.loading').hide();
            }

        })
    }

    //获取手机壁纸详细信息
    function get_phone_detail(id,p) {
        if(id == undefined){
            //获取参数
            var params = getParams();
            var id = params.id;
        }
        $.ajax({
            url:Api.Phone.GetPhoneDetail + '/' +id,
            type:'get',
            data:{
                p:p,
                uid:localStorage.getItem('uid')
            }, beforeSend:function () {
                loading = loading = layer.open({type: 2, shade: false});
            }, success:function (res) {
                var e = JSON.parse(res);
                if(!e.success){
                    layer.open({content:e.msg,skin:'msg',time:2});
                    return false;
                }
                //渲染数据
                $('.sj-detail-title').text(e.data.title);
                $('.images').attr('src',e.data.img);
                $('.sj-detail-description').text(e.data.description);
                $('.sj-detail-column').text('分类：' + e.data.column);
                $('.sj-detail-time').text('时间：' + e.data.pubdate);
                //清空原有数据
                $('.sj-detail-tag ul li').remove();
                $('.sj-detail-tag ul .clear').remove();
                $j.each(e.data.tag,function () {
                    var text = '<li data-id="'+ this.id +'">'+ this.tag_name +'</li>';
                    $('.sj-detail-tag ul').append(text);
                });
                $('.sj-detail-tag ul').append('<div class="clear"></div>');
                $('.sj-detail-source').text('来源：'+e.data.source);
                $('.sj-detail-author').text('作者：'+e.data.author);
                //清空原有数据
                $('.sj-detail-hot ul li').remove();
                $('.sj-detail-hot ul .clear').remove();
                $j.each(e.data.hot_tag,function () {
                    var text = '<li data-id="'+ this.id +'" style="background:'+ getRandomColor() +'">'+ this.tag_name +'</li>';
                    $('.sj-detail-hot ul').append(text);
                });
                $('.sj-detail-hot ul').append('<div class="clear"></div>');
                //清空原有数据
                $('.sj-detail-related ul li').remove();
                $('.sj-detail-related ul .clear').remove();
                $j.each(e.data.random_article,function () {
                    var text = '<li data-id="'+ this.id +'"><img src="'+ this.litpic +'" alt=""></li>';
                    $('.sj-detail-related ul').append(text);
                });
                $('.sj-detail-related ul').append('<div class="clear"></div>');
                $('.prev-doc')[0].dataset.id = e.data.prev.id;
                $('.next-doc')[0].dataset.id = e.data.next.id;
                $('.prev-page')[0].dataset.id = e.data.prev_p;
                $('.next-page')[0].dataset.id = e.data.next_p;
                //初始化收藏图标
                $('.detail-like img').attr('src','/public/jpg/mobile/article-like.png');
                if(e.data.like_status == true){
                    $('.detail-like img').attr('src','/public/jpg/mobile/article-like-yellow.png');
                }
                //返回页面顶部
                $('.content').scrollTop(0);
                var url = window.location.href;
                var   newUrl=  changeURLArg(url, "id", id);
                newUrl = changeURLArg(newUrl,'p',p);
                window.history.pushState({}, "", newUrl);
            }, complete:function () {
                layer.close(loading);
            }
        });
    }
    //修改用户信息方法
    function edit_user_info(data, callback) {
        //获取用户id
        var uid = localStorage.getItem('uid');
        //组合数据
        data.uid = uid;
        if (uid != null) {
            //获取用户信息
            $.ajax({
                url: '/wap/user/editUserInfo.html',
                type: 'get',
                data: data,
                beforeSend: function () {
                    loading = layer.open({type: 2, shade: false})
                },
                success: function (res) {
                    var e = JSON.parse(res);
                    callback(e);
                },
                complete: function () {
                    layer.close(loading);
                }
            })
        }
    }

    //设置栏目页面更多图片点击事件
    $(document).on('click', '.close-set-column', function () {
        $.router.back();
    });
    //意见反馈发表按钮点击事件
    $(document).on('click', '.callback-push', function () {
        //获取反馈标题
        var title = $('input[name="title"]').val();
        var content = $('textarea[name="content"]').val();
        var uid = localStorage.getItem('uid');
        if (title == '') {
            layer.open({content: '反馈的标题不能为空', skin: 'msg', time: 2});
            return false;
        }
        if (content == '') {
            layer.open({content: '反馈的内容不能为空', skin: 'msg', time: 2});
            return false;
        }
        var data = {
            title: title,
            content: content,
            uid: uid
        };
        //发送请求
        $.ajax({
            url: '/wap/callback/push.html',
            type: 'post',
            data: data,
            beforeSend: function () {
                loading = layer.open({type: 2, shade: false})
            },
            success: function (res) {
                var e = JSON.parse(res);
                layer.open({content: e.msg, skin: 'msg', time: 2});
                setTimeout(function () {
                    if (e.success) {
                        history.back();
                    }
                });
            },
            complete: function () {
                layer.close(loading);
            }
        });
    });

    //获取用户信息方法
    function get_user_info(callback) {
        //获取用户id
        var uid = localStorage.getItem('uid');
        if (uid != null) {
            //获取用户信息
            $.ajax({
                url: '/wap/user/getuserinfo.html',
                type: 'get',
                data: {
                    uid: uid
                },
                beforeSend: function () {
                    loading = layer.open({type: 2, shade: false})
                },
                success: function (res) {
                    var e = JSON.parse(res);
                    callback(e)
                },
                complete: function () {
                    layer.close(loading);
                }
            })
        }
        ;
    };
    active();

    //判断用户是否登录
    function checkLogin(url) {
        //获取用id
        var uid = localStorage.getItem('uid');
        if (uid == null) {
            $.router.load('/m/html/login.html');
            return false;
        }
        return uid;
    }

    function active() {
        var bar = $('.bar').children('a');
        bar.each(function () {
            if ($(this).hasClass('active')) {
                var href = $(this).attr('href');
                loadContent(href);
            }
        })
    }

    function loadContent(href) {
        loading = layer.open({type: 2, shade: false});
        $('.content').html('');
        $('.content').load(href + ' .content', function (response, status, xhr) {
            if (status == 'success') {
                //遍历返回的数据
                $j.each($j(response), function (key, value) {
                    var node = value.nodeName.toLowerCase();
                    //设置页面标题
                    if (node == 'title') {
                        $j('title').html(value.innerHTML)
                    }
                    //运行子页面js
                    if (node == 'script') {
                        window.eval(value.outerText);
                    }
                    //加载css文件
                    if (node == 'link') {
                        $j("head").append(value)
                    }
                });
                layer.close(loading);
            }
            return false;
        });
    }

    $(document).on('click', '.bar a', function () {
        var href = $(this).attr('href');
        var bar = $j('.bar').children('a');
        bar.each(function () {
            $j(this).removeClass('active');
        });
        $j(this).addClass('active');
        loadContent(href);
        return false;
    });

    //注册页面发送短信验证码方法
    $(document).on('click','.register-content .send-code',function () {
        var phone = $('input[name="phone"]').val();
        if(phone == ''){
            layer.open({content:'请填写手机号码',skin:'msg',time:2});
            return false;
        }
        //验证手机号码格式
        if(!(/^1[346578]\d{9}$/.test(phone))){
            layer.open({content:"手机号码不正确",skin:'msg',time:2});
            return false;
        }
        var that = this;
        $.ajax({
            url:Api.Login.SendRegisterSms,
            type:'post',
            data:{
                phone:phone
            },
            beforeSend:function () {
                loading = layer.open({type:2,shade:false});
            },success:function (res) {
                var e = JSON.parse(res);
                layer.open({content:e.msg,skin:'msg',time:2});
                if(e.success) {
                    $(that).attr('disabled', 'disabled');
                    var time = 120;
                    var i = 0;
                    var sending = setInterval(function () {
                        i++;
                        if (i >= time) {
                            $('.send-code').attr('disabled', '');
                            $('.send-code').text('获取远程验证码');
                            clearInterval(sending);
                        }
                        $('.send-code').text(time - i + '秒后重发');
                    }, 1000);
                }
            },complete:function () {
                layer.close(loading);
            }
        })
    });
    //注册页面注册按钮点击事件
    $(document).on('click','.register-btn',function(){
        var phone = $('input[name="phone"]').val();
        var code = $('input[name="code"]').val();
        var password = $('input[name="pwd"]').val();
        if(phone == ''){
            layer.open({content:'请输入手机号',skin:'msg',time:2});
            return false;
        }
        //验证手机号码格式
        if(!(/^1[346578]\d{9}$/.test(phone))){
            layer.open({content:"手机号码不正确",skin:'msg',time:2});
            return false;
        }
        if(code == ''){
            layer.open({content:'请输入手机接收到的验证码',skin:'msg',time:2});
            return false;
        }
        if(password == ''){
            layer.open({content:'请输入要设置的账户密码',skin:'msg',time:2});
            return false;
        }
        //发送请求
        $.ajax({
            url:Api.Login.Register,
            type:'post',
            data:{
                phone:phone,
                code:code,
                pwd:hex_sha1(password)
            }, beforeSend:function () {
                loading = layer.open({type:2,shade:false})
            }, success:function (res) {
                var e = JSON.parse(res);
                layer.open({content:e.msg,skin:'msg',time:2});
                if(e.success){
                    $.router.load('/m/html/prefect.html?token='+e.data.uid);
                }
            }, compplete:function () {
                layer.close(loading);
            }
        })
    });
    //完善账号信息页面提交按钮点击事件
    $(document).on('click','.perfect-info-btn',function () {
         var nickname = $('input[name="nickname"]').val();
         var realname = $('input[name="realname"]').val();
         if(nickname == ''){
             layer.open({content:'请输入登录账号',skin:'msg',time:2});
             return false;
         }
         if(realname == ''){
             layer.open({content:'请输入真实姓名',skin:'msg',time:2});
             return false;
         }
         var token = $('input[name="token"]').val();
         //发送请求
        $.ajax({
            url:Api.Login.PerfectInfo,
            type:'post',
            data:{
                nickname:nickname,
                realname:realname,
                token:token
            },
            beforeSend:function () {
                loading = layer.open({type:2,shade:false});
            },success:function (res) {
                var e = JSON.parse(res);
                layer.open({content:e.msg,skin:'msg',time:2});
                if(e.success){
                    history.go(-2);
                }
            },complete:function () {
                layer.close(loading);
            }
        })
    });
    //文档详情页上一篇点击事件
    $(document).on('click','.prev-doc',function () {
        var id = this.dataset.id;
        if(id == 0){
            layer.open({content:'没有数据啦...',skin:'msg',time:2});
            return false;
        }
        get_phone_detail(id,0);
    });
    //文档详情页下一篇点击事件
    $(document).on('click','.next-doc',function () {
        var id = this.dataset.id;
        if(id == 0){
            layer.open({content:'没有数据啦...',skin:'msg',time:2});
            return false;
        }
        get_phone_detail(id,0);
    });
    //文档详情页上一页点击事件
    $(document).on('click','.prev-page',function () {
        var p = this.dataset.id;
        if(p == 0){
            layer.open({content:'没有上一页啦...',skin:'msg',time:2});
            return false;
        }
        get_phone_detail(undefined,p);
    });
    //文档详情页下一页点击事件
    $(document).on('click','.next-page',function () {
        var p = this.dataset.id;
        if(p == 0){
            layer.open({content:'没有下一页啦...',skin:'msg',time:2});
            return false;
        }
        get_phone_detail(undefined,p);
    });
    //返回顶部点击事件
    $(document).on('click','.detail-top',function () {
        $('.content').scrollTop(0);
    });
    //文档收藏点击事件
    $(document).on('click','.detail-like',function () {
        if(!checkLogin()){
            return false;
        }
        var params = getParams();
        var data = {
            uid:localStorage.getItem('uid'),
            id:params.id,
            p:params.p
        };
        //组合数据
        $.ajax({
            url:Api.Common.MemberLikeOperate,
            type:'post',
            data:data,
            beforeSend:function () {
                loading = layer.open({type:2,shade:false})
            },success:function (res) {
                var e = JSON.parse(res);
                layer.open({content:e.msg,skin:'msg',time:2});
                if(e.success){
                    if(e.code == 1){
                        $('.detail-like img').attr('src','/public/jpg/mobile/article-like-yellow.png');
                    }else{
                        $('.detail-like img').attr('src','/public/jpg/mobile/article-like.png');
                    }
                }
            },complete:function () {
                layer.close(loading);
            }
        })
    });
    $(document).on('click','.detail-comment',function () {
        var params = getParams();
        var id = params.id;
        $.router.load('/m/html/article_comment.html?id='+id);
    });
    //文档详情页下载图标点击事件
    $(document).on('click','.detail-down',function () {
        var down_url = $('.images').attr('src');
        window.open(down_url);
    });
    //禁止ctrl+n和 禁止ctrl+r和 禁止shift+f10 禁止鼠标右键or左右键 和禁止f5
    var oLastBtn = 0, bIsMenu = false;

    function nocontextmenu() {
        event.cancelBubble = true;
        event.returnValue = false;
        return false;
    }

    function norightclick(e) {
        if (window.Event) {
            if (e.which != 1) {
                return false;
            }
        } else if (event.button != 1) {
            event.cancelBubble = true;
            event.returnValue = false;
            return false;
        }
    }

    document.oncontextmenu = nocontextmenu;
    document.onmousedown = norightclick;

    function onKeyDown() {
        if ((event.altKey) || ((event.keyCode == 8) && (event.srcElement.type != "text" && event.srcElement.type != "textarea" && event.srcElement.type != "password")) || ((event.ctrlKey) && ((event.keyCode == 78) || (event.keyCode == 82))) || (event.keyCode == 116)) {
            event.keyCode = 0;
            event.returnValue = false;
        }
    }
    //修改url地址栏参数
    function changeURLArg(url, arg, arg_val) {
        /// <summary>
        /// url参数替换值
        /// </summary>
        /// <param name="url">目标url </param>
        /// <param name="arg">需要替换的参数名称</param>
        ///<param name="arg_val">替换后的参数的值</param>
        /// <returns>参数替换后的url </returns>
        var pattern = arg + '=([^&]*)';
        var replaceText = arg + '=' + arg_val;
        if (url.match(pattern)) {
            var tmp = '/(' + arg + '=)([^&]*)/gi';
            tmp = url.replace(eval(tmp), replaceText);
            return tmp;
        } else {
            if (url.match('[\?]')) {
                return url + '&' + replaceText;
            } else {
                return url + '?' + replaceText;
            }
        }
        return url + '\n' + arg + '\n' + arg_val;
    }

    //获取网页链接参数
    function getQueryString(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]);
        return null;
    }

    //获取网页参数
    var path = getQueryString('path');
    if (path != null){
        var path_name = decodeURIComponent(path);
        //使用路由跳转页面
        $.router.load(path_name);
    }

    //公共函数，将json对象转数组
    function getArr(o) {
        var a = new Array();
        var length = Object.keys(o).length;
        for (var i = 0; i < length; i++) {
            a.push(o[i]);
        }
        delete a.undefined;
        return a;
    }
    
    //公共函数，获取随机颜色代码
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

    //全局变量，动态的文章ID
    var ShareURL = "";
    var shareTitle = $('title').text(),
        shareDesc = $('meta[name="description"]').attr('content'),
        sharePic = $('link[rel="icon"]').attr('href');
    $(".bdsharebuttonbox a").mouseover(function () {
        ShareURL = $(this).attr("data-url");
    });

    /*
	* 动态设置百度分享URL的函数,具体参数
	* cmd为分享目标id,此id指的是插件中分析按钮的ID
	*，我们自己的文章ID要通过全局变量获取
	* config为当前设置，返回值为更新后的设置。
	*/


    $(document).on('click', '.bdsharebuttonbox a', function () {
        var cmd = this.dataset.cmd;

        function SetShareUrl(cmd, config) {
            if (ShareURL) {
                config.bdUrl = ShareURL;
            }
            return config;
        }

        window._bd_share_config = {
            "common": {
                onBeforeClick: SetShareUrl(cmd),
                "bdSnsKey": {},
                "bdText": ""
                ,
                "bdMini": "2",
                "bdMiniList": false,
                "bdPic": "http://www.datouwang.com/uploads/pic/jiaoben/2017/jiaoben826_s.jpg",
                "bdStyle": "0",
                "bdSize": "24"
            }, "share": {}
        }
    });
    //加载百度分享js文件
    with (document) 0[(getElementsByTagName('head')[0] || body).appendChild(createElement('script')).src = 'http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion=' + ~(-new Date() / 36e5)];
};
