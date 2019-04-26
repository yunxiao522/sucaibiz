//获取页面完整地址
var pathname = window.location.pathname;
var search = window.location.search;
window.location.href = '/m/index.html?path=' + encodeURIComponent(pathname + search);