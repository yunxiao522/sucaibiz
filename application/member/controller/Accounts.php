<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/7
 * Time: 15:38
 * Description：会员账号信息
 */

namespace app\member\controller;

use app\member\model\Author;
use app\member\model\Level;
use app\member\model\Log;
use Baidu\Baidu\Baidu;
use Github\Auth\Github;
use Sina\Sae\SaeTOAuthV2;
use SucaiZ\config;
use SucaiZ\File;
use SucaiZ\Page;
use Tencent\Qq\QQLogin;
use think\Request;
use think\Session;
use think\Validate;
use think\View;
use verfily\verfily;

class Accounts extends Common
{
    public function __construct()
    {
        parent::__construct();
        //获取用户等级信息
        $level = new Level();
        $level_info = $level -> getLevelInfo(['id'=>$this->member_info['type']]);
        $this->member_info['level_info'] = $level_info['level_name'];
        //分配会员数据到页面
        View::share('user_info' ,$this->member_info);
    }
    //修改密码
    public function password(){
        if(Request::instance()->isPost()){
            //验证数据
            $oldpass = input('oldpass');
            if(empty($oldpass)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的原始密码不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $newpass = input('newpass');
            if(empty($newpass)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的新密码不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $repass = input('repass');
            if(empty($repass)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的重复密码不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $uid = input('uid');
            if(!isset($uid) || empty($uid) || !is_numeric($uid)){
                echo '非法访问';die;
            }
            //验证两次输入的新密码是否一致
            if($newpass != $repass){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'两次输入的密码不一致'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //获取用户信息
            $user = new \app\member\model\User();
            $where = ['id'=>$uid];
            $user_info = $user->getUser($where ,'password');
            //验证输入的原始密码是否一致
            if(getUserPwd($oldpass) != $user_info['password']){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的原始密码不正确'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                //更新用户信息
                //组合更新信息
                $arr = [
                    'password'=>getUserPwd($newpass),
                    'alter_time'=>time()
                ];
                $res = $user->updateUserInfo($where ,$arr ,'user_info' ,['password'=>'密码','alter_time'=>'更新时间'] ,'修改密码');
                if($res){
                    $a = [
                        'errorcode'=>0,
                        'msg'=>'修改成功'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'修改失败'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
            }



            
        }else{
            View::share('type' ,'password');
            return View('templates/accounts_passsword');
        }
    }
    //账号信息
    public function setting(){
        //获取访问的分类
        $class = input('class');
        if(!isset($class) || empty($class) || !is_numeric($class)){
            $class = 'info';
        }
        if(Request::instance()->isPost()){
            //验证数据
            $nickname = input('nickname');
            if(!isset($nickname)){
                echo '非法访问';die;
            }
            if(empty($nickname)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的昵称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($nickname ,'UTF-8') > 20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的昵称不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $sex = input('sex');
            if(!isset($sex) || !is_numeric($sex)){
                echo '非法访问';die;
            }
            $signature = input('signature');
            if(!isset($signature)){
                echo '非法访问';die;
            }
            if(mb_strlen($signature ,'UTF-8') > 40){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的签名不能超过40个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(!isset($description)){
                echo '非法访问';die;
            }
            if(mb_strlen($description ,'UTF-8') >2000){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的说明不能超过2000个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $id = $this->member_info['id'];
            $arr = [
                'nickname'=>$nickname,
                'sex'=>$sex,
                'signature'=>$signature,
                'description'=>$description,
                'alter_time'=>time()
            ];
            //组合更新条件
            $where = ['id'=>$id];
            $user = new \app\member\model\User();
            $res = $user->updateUserInfo($where ,$arr ,'user_info' ,['nickname'=>'昵称','sex'=>'性别','signature'=>'签名','description'=>'说明'] ,'修改账号信息');
            if($res){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            View::share('type' ,'setting');
            View::share('class',$class);
            return View('templates/accounts_setting');
        }
    }
    //账号关联
    public function relevance(){
        if(Request::instance()->isPost()){
            //验证前台提交的数据
            $class = input('class');
            if(!isset($class) || empty($class) || !is_string($class)){
                echo '非法访问';die;
            }
            $type = input('type');
            if(!isset($type) || empty($type) || !is_string($type)){
                echo '非法访问';die;
            }
            //组合条件
            $where = ['uid'=>$this->member_info['id']];
            //判断操作种类
            if($class == 'nbinging'){
                //组合更新内容
                $arr = [
                    $type .'_token' => null,
                    $type .'_user_name' => null,
                    $type .'_user_face' => null
                ];
                $author = new Author();
                $res = $author->alterAuthor($where ,$arr);
                if($res){
                    $a = [
                        'errorcode'=>0,
                        'msg'=>'解绑成功'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'解绑失败'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
            }else{
                Session::set('uid' ,$this->member_info['id']);
                if($type == 'qq'){
                    $callback_url = 'https://www.sucai.biz/callback.html?type=qq&uid=' .$this->member_info['id'];
                    $obj = new QQLogin(config::get('oauth-qq-appid') ,config::get('oauth-qq-appkey') ,$callback_url);
                    $url = $obj->qq_login();
                    $title = '绑定qq账户';
                }else if($type == 'baidu'){
                    $callback_url = 'https://www.sucai.biz/callback.html?type=baidu&uid=' .$this->member_info['id'];
                    $obj = new Baidu(config::get('oauth-baidu-appid') ,config::get('oauth-baidu-appkey') ,$callback_url);
                    $url = $obj->getLoginUrl();
                    $title = '绑定百度账号';
                }else if($type == 'github'){
                    $callback_url = 'https://www.sucai.biz/callback.html?type=github&uid=' .$this->member_info['id'];
                    $obj = new Github(config::get('oauth-github-appid') , config::get('oauth-github-appkey') ,$callback_url);
                    $url = $obj->getAuthorizeUrl();
                    $title = '绑定github账户';
                }else if($type == 'weibo'){
                    $callback_url = 'https://www.sucai.biz/callback.html?type=weibo&uid=' .$this->member_info['id'];
                    $obj = new SaeTOAuthV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey'));
                    $url = $obj->getAuthorizeURL($callback_url);
                    $title = '绑定新浪微博账户';
                }
                $a = [
                    'errorcode'=>2,
                    'msg'=>'获取相关数据成功',
                    'url'=>$url,
                    'title'=>$title
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            //获取授权信息
            //组合查询条件
            $where = ['uid'=>$this->member_info['id']];
            $author = new Author();
            $author_info = $author->getAuthor($where);
            View::share('author_info' ,$author_info);
            View::share('type' ,'relevance');
            return View('templates/accounts_relevance');
        }
    }
    //偏好设置
    public function preference(){
        if(Request::instance()->isPost()){

        }else{
            View::share('type' ,'preference');
            return View('templates/accounts_preference');
        }
    }
    //登录记录
    public function loginrecord(){
        //获取分页相关数据
        $limit = input('limit');
        if (!isset($limit) || !is_numeric($limit)) {
            $limit = 20;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        //构建查询条件
        $where = ['uid'=>$this->member_info['id'],'type'=>1];
        $log = new Log();
        $count = $log->getCount($where);
        //获取日志列表
        $log_list = $log->getLoginLogList($where ,' * ' ,$limits);
        //循环处理数据
        foreach($log_list as $key => $value){
            $log_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['login_time']);
        }
        //实例化分页类
        $paging = new Page($count ,$limit);
        //分配分页数据到页面
        View::share('paging' ,$paging->render());
        View::share('list',$log_list);
        View::share('type' ,'loginrecord');
        return View('templates/accounts_loginrecord');
    }
    //修改账号邮箱方法
    public function alterEmail(){
        if(Request::instance()->isPost()){
            //验证前台数据

        }else{
            return View('templates/accounts_email');
        }
    }
    //修改账号手机方法
    public function alterPhone(){
        if(Request::instance()->isPost()){

        }else{
            return View('templates/accounts_phone');
        }
    }
    //获取验证码图像
    public function getCode(){
        //获取验证码图片方法
        $verfily = new verfily();
        $verfily->codelen = 4;
        $verfily->width = 92;
        $verfily->height = 35;
        $verfily->doimg();
        $verfily->outPut();
    }
    //发送修改手机号码验证短信方法
    public function sendSms(){
        if(Request::instance()->isPost()){
            //验证数据
            $code = input('code');
            if(!isset($code) || !is_numeric($code)) {
                echo '非法访问';die;
            }
            $phone = input('phone');
            if(!isset($phone) || !is_numeric($phone)){
                echo '非法访问';die;
            }
            //使用正则验证手机号码
            $phone_rule = "/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9]|16[0|1|2|3|5|6|7|8|9]|17[0|1|2|3|5|6|7|8|9])\d{8}$/";
            if (!preg_match($phone_rule, $phone) || mb_strlen($phone ,'UTF-8') != 11) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的手机号格式不正确';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $verfily = new verfily();
            if($verfily->checkVerfily($code)){
                //判断输入的手机号在数据库中是否存在
                $where = ['phone'=>$phone];
                $user = new \app\member\model\User();
                $count = $user->getUserCount($where);
                if($count == 0){
                    //组合发送短信内容
                    $sms_content = "";
                    //发送短信
                    $smscode = getUserSmsCode();
                    $templateparams = ['code' => $smscode];
                    $res = sendSms($phone ,'素材站' ,'SMS_133000964' ,$templateparams);
                    if($res){
                        $a = [
                            'errorcode'=>0,
                            'msg'=>'验证码发送成功,请注意查收'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }else{
                        $a = [
                            'errorcode'=>1,
                            'msg'=>'验证码发送失败'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'输入的手机号已经绑定过本站的账号'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }

            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的验证码不正确'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }
    }
    //发送修改邮箱邮件方法
    public function sendEmail(){
        if(Request::instance()->isPost()){
            //验证数据
            $code = input('code');
            if(!isset($code)){
                echo '非法访问';die;
            }
            $email = input('email');
            if(!isset($email) || !is_string($email)){
                echo '非法访问';die;
            }
            if(!Validate::is($email ,'email')){
               $a = [
                   'errorcode'=>1,
                   'msg'=>'请输入正确的邮箱地址'
               ];
               return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $verfily = new verfily();
            if($verfily->checkVerfily($code)){
                //判断输入的邮箱是否已经在数据库中存在
                $where = ['email'=>$email];
                $user = new \app\member\model\User();
                $count = $user->getUserCount($where);
                if($count == 0){
                    //生成邮箱对应的token
                    $token = md5($email .rand(00000 ,99999));
                    //存入redis
                    $redis = getRedis(1);
                    $redis->set($email ,$token);
                    //组合邮箱验证链接地址
                    $url = config::get('cfg_hostsite') .'/member/accounts/checkemail.html?token=' .$token .'&email=' .$email;

                    //组合邮箱内容
                    $content = '';
                    $res = sendEmail($email ,'' ,'会员邮箱验证' ,$content);
                    if($res){
                        $a = [
                            'errorcode'=>0,
                            'msg'=>'邮件发送成功'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }else{
                        $a = [
                            'errorcode'=>1,
                            'msg'=>'邮件发送失败'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'输入的邮箱地址已经绑定过账号了'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的验证码不正确'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }
    }
    //验证邮箱方法
    public function checkEmail(){
        //验证数据
        $code = input('token');
        if(!isset($code) || empty($code)){
            echo '非法访问';die;
        }
        $email = input('email');
        if(!isset($email) || empty($email)){
            echo '非法访问';die;
        }
        $redis = getRedis(1);
        //根据email地址获取地址
        $token = $redis->get($email);
        if(empty($token)){
            return View('templates/accounts_email_error');
        }else{
            return View('templates/accounts_email_success');
        }
    }
    //上传会员头像方法
    public function uploadFace(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        //设置用户信息
        File::setUserInfo(1, $uid);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            //更新用户表数据
            $user = new \app\member\model\User();
            //组合条件
            $where = ['id'=>$uid];
            //组合更新信息
            $arr = ['face'=>File::$url];
            $res = $user->updateUserInfo($where,$arr,'user_info' ,['face'=>'头像'] ,'修改头像');
            if($res){
                $a['url'] = File::$url;
                $a['id'] = File::$upload_id;
                $a['errorcode'] = 0;
                $a['msg'] = '上传成功';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }
}