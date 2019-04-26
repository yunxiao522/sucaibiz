$(function () {
    //发送短信按钮事件
    var countdown = 5;
    function settime() {
        if (countdown == 0) {
            window.location.href = '/login.html';
            return;
        } else {
            $('.hinit').html(countdown);
            countdown--;
        }
        setTimeout(function (obj) {
            settime(obj)
        }, 1000);
    }
    settime();
});