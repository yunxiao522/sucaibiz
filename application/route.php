<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
//后台路由
Route::rule('admin/login', 'admin/login/login');
//Route::rule('admin/index', 'admin/index/index');
Route::rule('admin/loginout', 'admin/login/loginout');
Route::rule('admin/account', 'admin/index/account');
Route::rule('admin/editpassword', 'admin/index/editpassword');
Route::rule('admin/upface', 'admin/index/uploadface');
Route::rule('admin/add_top_column', 'admin/column/addtop');
Route::rule('admin/getnamerule', 'admin/sysconfig/getnamerule');
Route::rule('admin/getcolumnlistjson', 'admin/column/getColumnListTojson');
Route::rule('admin/alter_Column', 'admin/column/alterColumn');
Route::rule('admin/add_child_column', 'admin/column/addchild');
Route::rule('admin/del_column', 'admin/column/delcolumn');
Route::rule('admin/alter_Column_sort_rank', 'admin/column/altercolumnsortrank');
Route::rule('admin/getcolumntree', 'admin/column/getcolumnlisttree');
Route::rule('admin/get_article_list', 'admin/article/getarticlelist');
Route::rule('admin/get_recycled_list', 'admin/article/getrecycledjson');
Route::rule('admin/del_article_one', 'admin/article/delarticleone');
Route::rule('admin/del_article_all', 'admin/article/delarticleall');
Route::rule('admin/restore_article_one', 'admin/article/restoreone');
Route::rule('admin/get_verify_num', 'admin/sysconfig/getverifnum');
Route::rule('admin/real_del_article_one', 'admin/article/realdelarticleone');
Route::rule('admin/restore_article_all', 'admin/article/restoreall');
Route::rule('admin/real_del_article_all', 'admin/article/realdelarticleall');
Route::rule('admin/get_my_article_list', 'admin/article/getmyarticlejson');
Route::rule('admin/get_column_count', 'admin/article/getcolumninfojson');
Route::rule('admin/get_column_info', 'admin/column/getcolumninfo');
Route::rule('admin/getattributetreejson', 'admin/article/getattributetreejson');
Route::rule('admin/add_article_attribute', 'admin/article/addattribute');
Route::rule('admin/upload_litpic', 'admin/article/uploadlitpic');
Route::rule('admin/get_upload_list_json', 'admin/upload/getuploadlistjson');
Route::rule('admin/get_log_admin_login_josn', 'admin/sysconfig/getadminloginlogjson');
Route::rule('admin/show_article_author', 'admin/article/showarticleauthor');
Route::rule('admin/show_article_source', 'admin/article/showarticlesource');
Route::rule('admin/set_article_author', 'admin/article/setarticleauthor');
Route::rule('admin/set_article_source', 'admin/article/setarticlesource');
Route::rule('admin/examine_article_title', 'admin/article/examinearticletitle');
Route::rule('admin/get_article_column_type', 'admin/article/getcolumnchannel');
Route::rule('admin/get_tag_list_json', 'admin/tag/gettaglistjson');
Route::rule('admin/member_show', 'admin/member/show');
Route::rule('admin/member_level', 'admin/member/level');
Route::rule('admin/add_member_level', 'admin/member/addMemebrLevel');
Route::rule('admin/get_member_level_list', 'admin/member/getMemberLevelList');
Route::rule('admin/del_member_level', 'admin/member/delMemberLevel');
Route::rule('admin/alter_member_level', 'admin/member/alterMemberLevel');
Route::rule('admin/get_member_level', 'admin/member/getMemberLevel');
Route::rule('admin/add_member', 'admin/member/addMember');
Route::rule('admin/get_member_list', 'admin/member/getMemberList');
Route::rule('admin/show_member_login_log', 'admin/member/showMemberLoginLog');
Route::rule('admin/get_member_login_log', 'admin/member/getMemberLoginLog');
Route::rule('admin/mod_member_log', 'admin/member/modMemberLevel');
Route::rule('admin/alter_member', 'admin/member/alterMember');
Route::rule('admin/update_member_face', 'admin/member/alterMemberFace');
Route::rule('admin/alter_member_level_img', 'admin/member/alterMemberLevelImg');
Route::rule('admin/update_member_level_img', 'admin/member/updateMemberLevelImg');
Route::rule('admin/del_member', 'admin/member/delMember');
Route::rule('admin/show_member_info', 'admin/member/showMemberInfo');
Route::rule('admin/show_integral_list', 'admin/member/showIntegralList');
Route::rule('admin/get_integral_list', 'admin/member/getIntegralList');
Route::rule('admin/add_integral_info', 'admin/member/addIntegral');
Route::rule('admin/del_integral_info', 'admin/member/delIntegralInfo');
Route::rule('admin/alter_integral_info', 'admin/member/alterIntegralInfo');
Route::rule('admin/show_email_list', 'admin/member/showEmail');
Route::rule('admin/get_email_list', 'admin/member/getMemberEmailList');
Route::rule('admin/del_email_info', 'admin/member/delMemberEmailInfo');
Route::rule('admin/show_email_info', 'admin/member/showEmailInfo');
Route::rule('admin/show_sms_list', 'admin/member/showMemberSmsList');
Route::rule('admin/get_sms_list', 'admin/member/getMemberSmsList');
Route::rule('admin/comment_mange', 'admin/comment/manage');
Route::rule('admin/show_sms_info', 'admin/Member/getMemberSmsInfo');
Route::rule('admin/show_Comment_key', 'admin/comment/showCommentKey');
Route::rule('admin/get_Comment_key', 'admin/comment/getCommentKey');
Route::rule('admin/del_Comment_key', 'admin/comment/delCommentKey');
Route::rule('admin/alter_Comment_key', 'admin/comment/alterCommentKey');
Route::rule('admin/add_Comment_key', 'admin/comment/addCommentKey');
Route::rule('admin/alter_Comment_Status', 'admin/comment/alterCommentStatus');
Route::rule('admin/del_Comment_Info', 'admin/comment/delCommentInfo');
Route::rule('admin/del_More_Comment_Info', 'admin/comment/dellMoreCommentInfo');
Route::rule('admin/refresh_Comment_key_cache', 'admin/comment/refresCommentKeyCache');
Route::rule('admin/sysconfig_show', 'admin/sysconfig/showSysconfigBasic');
Route::rule('admin/show_sysconfig_list', 'admin/sysconfig/showSysconfigList');
Route::rule('admin/show_sysconfig_group', 'admin/sysconfig/showSysconfigGroup');
Route::rule('admin/get_sysconfig_group', 'admin/sysconfig/getSysconfigGroup');
Route::rule('admin/add_sysconfig_group', 'admin/sysconfig/addSysconfigGroup');
Route::rule('admin/alter_sysconfig_group', 'admin/sysconfig/alterSysconfigGroup');
Route::rule('admin/del_Sysconfig_group', 'admin/sysconfig/delSysconfigGroup');
Route::rule('admin/add_Sysconfig', 'admin/sysconfig/addSysconfig');
Route::rule('admin/get_Sysconfig_list', 'admin/sysconfig/getSysconfigList');
Route::rule('admin/del_Sysconfig_info', 'admin/sysconfig/delSysconfigInfo');
Route::rule('admin/alter_Sysconfig_info', 'admin/sysconfig/alterSysconfig');

Route::rule('admin/get_plan_list', 'admin/plan/getPlanList');
Route::rule('admin/del_plan', 'admin/plan/delPlan');
Route::rule('admin/alter_plan', 'admin/plan/alterPlan');
Route::rule('admin/show_login_log', 'admin/log/showLoginLog');
Route::rule('admin/get_login_log', 'admin/log/getLogLogin');
Route::rule('admin/show_sysconfig_water', 'admin/sysconfig/water');
Route::rule('admin/alter_Sysconfig_value', 'admin/sysconfig/alterSysconfigValue');
Route::rule('admin/update_sysconfig_water', 'admin/sysconfig/updateWater');

Route::rule('admin/get_log_operate', 'admin/log/getOperate');
Route::rule('admin/get_backup_list', 'admin/Sysconfig/getBackUpList');
Route::rule('admin/del_backup', 'admin/Sysconfig/delBackUp');
Route::rule('admin/create_backup', 'admin/Sysconfig/createBackup');
Route::rule('admin/preserve', 'admin/Sysconfig/preserve');
Route::rule('preserve', 'admin/hint/preserve');
Route::rule('admin/addlixian', 'admin/article/addLiXian');
Route::rule('admin/upload_source_one', 'admin/article/uploadSourceImgOne');
Route::rule('admin/upload_source_more', 'admin/article/uploadSourceImgMore');

Route::rule('admin/get_queue_list', 'admin/sysconfig/getQueueList');
Route::rule('admin/make_column_html', 'admin/html/makeColmunHtml');
Route::rule('admin/make_article_html', 'admin/html/makeArticle');
Route::rule('admin/make_index_html', 'admin/html/makeIndex');
Route::rule('admin/html_article', 'admin/html/makeArticleOne');
Route::rule('admin/html_column', 'admin/html/makeColumnOne');
Route::rule('admin/user_list', 'admin/user/show');
Route::rule('admin/user_list_json', 'admin/user/getUserList');
Route::rule('admin/user_role', 'admin/user/role');
Route::rule('admin/user_role_List', 'admin/user/getRoleList');
Route::rule('admin/user_role_add', 'admin/user/addRole');
Route::rule('admin/user_role_del', 'admin/user/delRole');
Route::rule('admin/user_role_alter', 'admin/user/alterRole');
Route::rule('admin/user_model_show', 'admin/rbac/modelMenage');
Route::rule('admin/user_model_add', 'admin/rbac/addModel');
Route::rule('admin/user_model_list_json', 'admin/rbac/getModelListJson');
Route::rule('admin/user_model_alter', 'admin/rbac/alterMode');
Route::rule('admin/user_model_del', 'admin/rbac/delModel');
Route::rule('admin/user_access_show', 'admin/rbac/access');
Route::rule('admin/advert_show', 'admin/advert/show');
Route::rule('admin/advert_add', 'admin/advert/add');
Route::rule('admin/advert_get_list', 'admin/advert/getAdvertList');
Route::rule('admin/advert_alter', 'admin/advert/alter');
Route::rule('admin/advert_del', 'admin/advert/del');
Route::rule('admin/alter_article_att', 'admin/article/alterAtt');
Route::rule('admin/uploadcolumncoverimg', 'admin/column/uploadColumnCoverImg');
Route::rule('admin/sysconfig_add_menu_class' ,'admin/sysconfig/addMenuClass');
Route::rule('admin/sysconfig_add_menu' ,'admin/sysconfig/addMenu');
Route::rule('admin/sysconfig_show_menu_json' ,'admin/sysconfig/getMenuList');
Route::rule('down/image' ,'index/down/image');
Route::rule('admin/getclick','admin/index/getclick');
Route::rule('admin/getupload','admin/index/getupload');
Route::rule('admin/searchkeyword','admin/search/keyword');
Route::rule('admin/searchcolumn','admin/search/column');
//显示文档回收站页面
Route::get('admin/article/recycled',function(){
    return view('admin@article/recycled');
});
//显示计划列表页
Route::rule('admin/show_plan_list', function(){
    return view('admin@plan/Plan_show_list');
});
//显示添加计划页面
Route::get('admin/add_plan', function(){
    return view('admin@plan/Plan_add');
});
//添加计划
Route::post('admin/add_plan','admin/plan/addPlan');
//显示消息队列列表
Route::get('admin/show_sysconfig_queue', function (){
    return view('admin@sysconfig/Sysconfig_queue_show');
});
//显示系统日志
Route::get('admin/sysconfig/log',function(){
   return view('admin@sysconfig/log');
});
//显示备份回滚列表
Route::rule('admin/show_backup', function (){
    return View('admin@sysconfig/BackUp_show');
});
//显示栏目列表
Route::rule('admin/column/show',function(){
    return view('admin@column/show');
});
//显示文档列表
Route::get('admin/article/show',function(){
    return view('admin@article/show');
});
//显示热门下载
Route::rule('admin/hotDown',function(){
   return view('admin@upload/HotDown');
});
//移动端tag标签管理
Route::rule('admin/mobile/tagManage',function(){
    return view('admin@mobile/tagManage');
});
//移动端分类管理
Route::rule('admin/mobile/columnManage',function(){
   return view('admin@mobile/showColumn');
});
Route::get([
    'admin/getHotDown'=>'admin/down/getHotDown',
]);
//发布文档时根据tag关键词和栏目id获取tag列表
Route::get('admin/pushGetTag','admin/tag/getPushTagList');


Route::rule('admin/mobileCoverManage',function(){
    return view('admin@mobile/coverManage');
});

//前台路由
Route::rule('tag_incr' , 'index/tag/incr');
Route::rule('article_incr' , 'index/index/incr');
//Route::rule('tag' , 'index/tag/show');
Route::rule('getcomment' , 'index/Comment/getArticleComment');
Route::rule('down' , 'index/down/index');
Route::rule('article' , 'index/index/article');
Route::rule('list' , 'index/index/showList');
Route::rule('tags' , 'index/index/tag');
Route::rule('uploads/' , 'index/original/fileUrl');
Route::rule('search','index/search/index');
Route::rule('version','index/index/version');

//Route::rule('tag_list' , 'index/index/tag');
//Route::rule('index_update' , 'index/index/index');


//sina模块路由saRoute::rule('sina/callback' ,'sina/api/getAccessToken');

//会员路由
Route::rule('login' , 'member/login/login');
Route::rule('register' , 'member/login/register');
Route::rule('active' , 'member/login/active');
Route::rule('getverfily' , 'member/login/getverifyimg');
Route::rule('checkverfily' , 'member/login/checkverfily');
Route::rule('checkusername' , 'member/login/checkusername');
Route::rule('checkemail' , 'member/login/checkemail');
Route::rule('memberSms', 'member/login/memberSms');
Route::rule('checkPhoneVerfiy', 'member/login/checkPhoneVerfiy');
Route::rule('checknickname', 'member/login/checknickname');
Route::rule('checkphone', 'member/login/checkphone');
Route::rule('register3', 'member/login/register3');
Route::rule('callback', 'member/login/CallBack');
Route::rule('weibologin', 'member/login/WeiBoLogin');
Route::rule('qqlogin', 'member/login/QqLogin');
Route::rule('baidulogin', 'member/login/BaiDuLogin');
Route::rule('githublogin', 'member/login/GithubLogin');
Route::rule('binding', 'member/login/binding');
Route::rule('loginout', 'member/index/logout');
Route::rule('plus/list', '/index/index/index');






Route::rule('touch' ,'admin/queue/executeget');

//支付路由
Route::rule('pay/msg','pay/common/mag');
Route::rule('pay/callback','pay/common/callback');

//微博消息推送接收
Route::rule('sina/receive',function(){
   echo '';
});
//微信公众号接收推送
Route::rule('wechat/ReceivePush','wechat/api/check');
return [

];
