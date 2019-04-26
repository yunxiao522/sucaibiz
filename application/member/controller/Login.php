<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/3/3
 * Time: 11:09
 */

namespace app\member\controller;

use app\admin\controller\Queue;
use app\common\controller\BaseController;
use app\member\model\Author;
use Baidu\Baidu\Baidu;
use Github\Auth\Github;
use Sina\Sae\SaeTClientV2;
use Sina\Sae\SaeTOAuthV2;
use SucaiZ\config;
use Tencent\Qq\QQLogin;
use think\Request;
use think\View;
use verfily\verfily;
use think\Session;
use think\Cookie;

class Login extends BaseController
{
    private $Redis = null;
    public $cookie_name;

    public function __construct()
    {
        parent::__construct();
        $this->Redis = getRedis();
        $this->cookie_name = config::get('cfg_member_cookie_key');
    }
    public function _initialize()
    {
        parent::_initialize();
        //判断是否存在$_GET['uid'];
        $uid = input('uid');
        if(!isset($uid) || $uid == 0) {
            //判断客户端cookie是否存在，并且和数据库中的一致
            $cookie_status = Cookie::has($this->cookie_name);
            if ($cookie_status) {
                $cookie_info = Cookie::get($this->cookie_name);
                if (isset($cookie_info['uid']) && isset($cookie_info['token'])) {
                    $user = model('User');
                    $where = [
                        'id' => $cookie_info['uid'],
                        'token' => $cookie_info['token']
                    ];
                    $count = $user->getUserCount($where);
                    if ($count == 1) {
                        header('location:/member/index.html');
                    }
                }
            }
            //判断会员session是否存在，存在则跳转至会员首页
            $session_status = Session::has('user');
            if ($session_status) {
                header('location:/member/index.html');
            }
        }
    }

    //会员登录方法
    public function login()
    {
        $url = input('url');
        if (Request::instance()->isPost()) {
            $username = input('username');
            $password = input('password');
            if (empty($username)) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的用户名不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (empty($password)) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的用户密码不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $user_redis_num_key = $_SERVER['REMOTE_ADDR'] . '_member';
            $redis = getRedis();
            if (!$redis->exists($user_redis_num_key)) {
                $redis->set($user_redis_num_key, 0, 1800);
            }
            $user_redis_num = $redis->get($user_redis_num_key);
            if ($user_redis_num <= 5) {
                //根据用户名取出用户账号信息
                $user = model('User');
                $user_info = $user->getUser(['username' => $username]);
                if ($user_info) {
                    //验证密码是否正确
                    if ($user_info['password'] == getUserPwd($password)) {
                        //验证用户账号状态
                        if ($user_info['status'] == 1 && !empty($user_info['phone'])) {
                            $a['errorcode'] = 1;
                            $a['msg'] = "账号被禁用，请联系管理员..";
                            return json_encode($a, JSON_UNESCAPED_UNICODE);
                        }else if(empty($user_info['phone'])){
                            $a['errorcode'] = 2;
                            $a['msg'] = '请先完善账号信息';
                            $a['url'] = '/register.html?token=' .$user_info['token'];
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }
                        $this->Redis->delete($user_redis_num_key);
                        $this->RecordRemember($user_info ,'web');
                        $a['errorcode'] = 0;
                        $a['msg'] = "登录成功...";
                        if(empty($url)){
                            $a['url'] = '/member/index.html';
                        }else{
                            $a['url'] = $url;
                        }
                        return json_encode($a, JSON_UNESCAPED_UNICODE);
                    } else {
                        $this->Redis->incr($user_redis_num_key);
                        $a['errorcode'] = 1;
                        $a['msg'] = "输入的用户密码不正确！";
                        return json_encode($a, JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    $this->Redis->incr($user_redis_num_key);
                    $a['errorcode'] = 1;
                    $a['msg'] = "输入的用户名不存在！";
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $souce = $redis->ttl($user_redis_num_key);
                $a['errorcode'] = 1;
                $a['msg'] = '等录次数超过5次,请' . $souce . '秒后再试';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            View::share('url' ,$url);
            return View('templates/login');
        }
    }

    //会员注册方法
    public function register()
    {
        if (Request::instance()->isPost()) {
            $token = input('token');
            if (isset($token)) {
                //根据token判断账号是否存在，不存在则跳转到注册页面
                $where = ['token' => $token];
                $user = model('User');
                $count = $user->getUserCount($where);
                if ($count) {
                    //判断账号信息是否已经完善，判断账号是否存在了修改时间，存在则表示已完善账号信息
                    $member_info = $user->getUser($where ,' id,phone ');
                    if (empty($member_info['phone'])) {
                        //验证前台提交的数据
                        $nickname = input('nickname');
                        if(!isset($nickname)){
                            echo '非法访问';
                            die;
                        }
                        if($nickname == ''){
                            $a['errorcode'] = 1;
                            $a['msg'] = '输入的昵称不能为空';
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }
                        if(mb_strlen($nickname ,'UTF-8') > 20){
                            $a['errorcode'] = 1;
                            $a['msg'] = '输入的昵称不能超过20个字符';
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }
                        $realname = input('realname');
                        if(!isset($realname)){
                            echo '非法访问';
                            die;
                        }
                        if($realname == ''){
                            $a['errorcode'] = 1;
                            $a['msg'] = '输入的真实姓名不能为空';
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }
                        if(mb_strlen($realname ,'UTF-8') > 20){
                            $a['errorcode'] = 1;
                            $a['msg'] = '输入的真实姓名不能超过20个字符';
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }
                        $phone = input('phone');
                        if(!isset($phone)){
                            echo '非法访问';
                            die;
                        }
                        $phone_rule = "/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/";
                        if (!preg_match($phone_rule, $phone) || mb_strlen($phone ,'UTF-8') != 11) {
                            $a['errorcode'] = 1;
                            $a['msg'] = '输入的手机号格式不正确';
                            return json_encode($a, JSON_UNESCAPED_UNICODE);
                        }
                        $phone_code = input('phone-code');
                        if(!isset($phone_code)){
                            echo '非法访问';
                            die;
                        }
                        $member_sms_key = $token . '_code';
                        $code = Session::get($member_sms_key);
                        if($phone_code != $code){
                            $a['errorcode'] = 1;
                            $a['msg'] = '输入的手机验证码不正确';
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }
                        $id = input('id');
                        if(!isset($id) || !is_numeric($id)){
                            echo '非法访问';
                            die;
                        }
                        //更新数据库信息
                        $where = ['id'=>$id,'token'=>$token];
                        $b = [
                            'nickname'=>$nickname,
                            'realname'=>$realname,
                            'phone'=>$phone,
                            'alter_time'=>time()
                        ];
                        $user = model('User');
                        $res = $user->updateUser($where ,$b);
                        if($res){
                            $a['errorcode'] = 0;
                            $a['msg'] = '更新账号信息成功';
                            $a['url'] = '/register3.html';
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }else{
                            $a['errorcode'] = 1;
                            $a['msg'] = '更新账号信息失败';
                            return json_encode($a ,JSON_UNESCAPED_UNICODE);
                        }
                    } else {
                        echo '非法访问';
                        die;
                    }
                } else {
                    echo '非法访问';
                    die;
                }
            } else {
                $username = $_POST['username'];
                $password = $_POST['password'];
                $email = $_POST['email'];
                $vpassword = $_POST['vpassword'];
                //检查账号是否唯一
                $User = new \app\member\model\User();
                $count = $User->getUserCount(['username'=>$username]);
                if($count != 0){
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'输入的用户名已经存在'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
                //检查特殊字符
                if(preg_match("/[ ' .,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$username)){
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'输入的账号不能包含特殊字符和空格哦'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
                //验证数据完整性
                if (!isset($username)) {
                    $a['errorcode'] = 1;
                    $a['msg'] = '请检查数据的完整性';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
                if ($username == '' || mb_strlen($username, 'UTF-8') > 20) {
                    $a['errorcode'] = 1;
                    $a['msg'] = '输入的用户名不能为空，并且不能超过20个字符';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
                if (!isset($password)) {
                    $a['errorcode'] = 1;
                    $a['msg'] = '请检查数据的完整性';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
                if (!isset($vpassword)) {
                    $a['errorcode'] = 1;
                    $a['msg'] = '请检查数据的完整性';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
                if ($password != $vpassword) {
                    $a['errorcode'] = 1;
                    $a['msg'] = '两次输入的密码不一致';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
                $email_rule = "/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
                if ($email == '') {
                    $a['errorcode'] = 1;
                    $a['msg'] = '输入的邮箱地址不能为空';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
                if (!preg_match($email_rule, $email)) {                       //用正则表达式函数进行判断
                    $a['errorcode'] = 1;
                    $a['msg'] = '输入的邮箱格式不正确';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
                //组合数据添加到数据库
                $face_number = rand(1, 24);
                $face_url = '/upload/face/' . $face_number . '.jpg';
                $user_token = createUserToken();
                $b = [
                    'username' => $username,
                    'password' => getUserPwd($password),
                    'token' => $user_token,
                    'face' => $face_url,
                    'type' => 1,
                    'email' => $email,
                    'create_time' => time(),
                    'status' => 1,
                ];

                $res = $User->insertUser($b);
                if ($res) {
                    //发送激活账号邮件
                    $email_title = '会员注册';
                    $email_content = "亲爱的" . $username . "：<br/>感谢您在我站注册了新帐号。<br/>请点击链接激活您的帐号。<br/> <a href='http://www.sucai.biz/active.html?token=$user_token' target= '_blank'>http://www.sucai.biz/active.html?token=$user_token</a><br/>如果以上链接无法点击，请将它复制到你的浏览器地址栏中进入访问，该链接24小时内有效。";
                    $a = [
                        'function' => 'sendEmail1',
                        'address' => $email,
                        'addressname' => $username,
                        'content' => $email_content,
                        'title' => $email_title

                    ];
                    //写入会员邮件表
                    $b = [
                        'address' => $email,
                        'uid' => $res,
                        'title' => $email_title,
                        'content' => $email_content,
                        'create_time' => time(),
                        'status' => 0
                    ];
                    $email_m = model('Email');
                    $email_m->addEmail($b);
                    if (sendEmail1($a)) {
                        $a['errorcode'] = 0;
                        $a['msg'] = '注册成功';
                        $a['url'] = '/register.html?token=' .$token;
                        return json_encode($a, JSON_UNESCAPED_UNICODE);
                    } else {
                        $a['errorcode'] = 1;
                        $a['msg'] = '注册失败';
                        return json_encode($a, JSON_UNESCAPED_UNICODE);
                    }
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = '注册失败';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            }
        } else {
            if (input('?get.token')) {

                $token = input('token');
                //根据token判断账号是否存在，不存在则跳转到注册页面
                $where = ['token' => $token];
                $user = model('User');
                $count = $user->getUserCount($where);
                if ($count) {
                    //判断账号信息是否已经完善，判断账号是否存在了修改时间，存在则表示已完善账号信息
                    $member_info = $user->getUser($where, ' id,phone ');
                    if (empty($member_info['phone'])) {
                        $this->assign('id', $member_info['id']);
                        $this->assign('token', $token);
                        return View('templates/register_2');
                    } else {
                        header('location:/login.html');
                        die;
                    }
                } else {
                    header("location:/register.html");
                    die;
                }
            } else {
                return View('templates/register');
            }
        }
    }
    //完成注册账号
    public function register3(){
        return View('templates/register_3');
    }
    //会员激活方法
    public function active()
    {
        $token = input('token');
        if (!isset($token)) {
            echo '非法访问';
            die;
        }
        $b = [
            'token' => $token
        ];
        $c = [
            'status' => 2,
            'alter_time' => time()
        ];
        $user = model('User');
        //先查询用户是否已经激活
        $member_info = $user->getUser($b, ' status,phone ');
        if ($member_info['status'] == 1) {
            $res = $user->updateUser($b, $c);
            if ($res) {
                header("location:/register.html?token=$token");
            } else if(empty($member_info['phone'])){
                echo '账号激活失败，请联系管理员';
                die;
            }
        } else if(empty($member_info['phone'])){
            //账号已激活，但未完成账号信息
            header("location:/register.html?token=$token");
        } else{
            header('location:/register3.html');

        }
    }

    //获取验证码图片方法
    public function getverifyimg()
    {
        $verfily = new verfily();
        $verfily->codelen = 5;
        $verfily->width = 92;
        $verfily->height = 32;
        $verfily->doimg();
        $verfily->outPut();
    }

    //验证输入的验证码是否正确
    public function checkverfily()
    {
        if (Request::instance()->isPost()) {
            $verfily = $_POST['verfily'];
            if (isset($verfily)) {
                $verf = new verfily();
                if ($verf->checkVerfily($verfily)) {
                    $a['errorcode'] = 0;
                    $a['msg'] = '输入的验证码正确';
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = '输入的验证码不正确';
                }
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                echo '非法访问';
                die;
            }
        } else {
            echo '非法访问';
            die;
        }
    }

    //检查用户账号可用性
    public function checkusername()
    {
        if (Request::instance()->isPost()) {
            $username = input('username');
            if (isset($username)) {
                $where = ['username' => $username];
                $user = model('User');
                $res = $user->getUserCount($where);
                if ($res == 0) {
                    $a['errorcode'] = 0;
                    $a['msg'] = '用户账号可用';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = '用户账号重复,请更换账号';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo '非法访问';
                die;
            }
        } else {
            echo '非法访问';
            die;
        }
    }

    //检查用户邮箱是否是唯一
    public function checkemail()
    {
        if (Request::instance()->isPost()) {
            $email = input('email');
            if (isset($email)) {
                $where = ['email' => $email];
                $user = model('User');
                $res = $user->getUserCount($where);
                if ($res == 0) {
                    $a['errorcode'] = 0;
                    $a['msg'] = '用户账号可用';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = '用户账号重复,请更换账号';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo '非法访问';
                die;
            }
        } else {
            echo '非法访问';
            die;
        }
    }

    //检查用户手机号是否是唯一
    public function checkphone()
    {
        if (Request::instance()->isPost()) {
            $phone = input('phone');
            if (isset($phone)) {
                $where = ['phone' => $phone];
                $user = model('User');
                $res = $user->getUserCount($where);
                if ($res == 0) {
                    $a['errorcode'] = 0;
                    $a['msg'] = '用户账号可用';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = '用户账号重复,请更换账号';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo '非法访问';
                die;
            }
        } else {
            echo '非法访问';
            die;
        }
    }
    //检查用户昵称是否是唯一
    public function checknickname()
    {
        if (Request::instance()->isPost()) {
            $nickname = input('nickname');
            if (isset($nickname)) {
                $where = ['nickname' => $nickname];
                $user = model('User');
                $res = $user->getUserCount($where);
                if ($res == 0) {
                    $a['errorcode'] = 0;
                    $a['msg'] = '用户账号可用';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = '用户账号重复,请更换账号';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            } else {
                echo '非法访问';
                die;
            }
        } else {
            echo '非法访问';
            die;
        }
    }

    //发送会员短信验证码
    public function memberSms()
    {
        $token = input('token');
        $phone = input('phone');
        $uid = input('uid');
        if (!isset($token)) {
            echo '非法访问';
            die;
        }
        //查询换绑后的手机号是否绑定其他账号
        $where = [
            'phone'=>$phone
        ];
        $count = Model('user')->getCount($where,'id');
        if($count != 0){
            return $this->ajaxError('该手机号已经绑定其他账号');
        }
        $member_sms_key = $token . '_code';
        //获取手机短信code
        $code = getUserSmsCode();
        Session::set($member_sms_key, $code);
        //组合数据写入数据库
        $sms_id = Model('sms')->add([
            'uid'=>$uid,
            'phone'=>$phone,
            'title'=>'手机短信激活码',
            'content'=>"您的验证码为$code ，请于10分钟内正确输入，如非本人操作，请忽略此短信。",
            'sms_code'=>'SMS_133000964',
            'create_time'=>time(),
            'status'=>3
        ]);
        if(!$sms_id){
            return $this->ajaxError('验证码发送失败');
        }
        $res = sendSms($phone,'素材站','SMS_133000964',['code' => $code]);
        if(!$res){
            //更新数据表发送状态
            Model('sms')->edit(['id'=>$sms_id],['status'=>2]);
            return $this->ajaxError('验证码发送失败');
        }
        //更新数据表发送状态
        Model('sms')->edit(['id'=>$sms_id],['status'=>1]);
        return $this->ajaxOk('发送成功');
    }

    //验证输入的验证码是否正确
    public function checkPhoneVerfiy(){
        $token = input('token');
        $verfiycode = input('verfiycode');
        if(!isset($token) || !isset($verfiycode)){
            echo '非法访问';
            die;
        }
        $member_sms_key = $token . '_code';
        $code = Session::get($member_sms_key);
        if(!isset($code)){
            $a['errorcode'] = 1;
            $a['msg'] = '超时，请重新获取手机验证码';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        if($code == $verfiycode){
            $a['errorcode'] = 0;
            $a['msg'] = '手机验证码正确';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a['errorcode'] = 1;
            $a['msg'] = '手机验证码不正确';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //新浪微博登录方法
    public function WeiBoLogin(){
        $callback_url = 'https://www.sucai.biz/callback.html?type=weibo';
        $obj = new SaeTOAuthV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey'));
        $weibo_login_url = $obj->getAuthorizeURL($callback_url);
        header("Location:".$weibo_login_url);
    }

    //腾讯qq登录方法
    public function QqLogin(){
        $callback_url = 'https://www.sucai.biz/callback.html?type=qq';
        $obj = new QQLogin(config::get('oauth-qq-appid') ,config::get('oauth-qq-appkey') ,$callback_url);
        $login_url = $obj->qq_login();
        header("Location:$login_url");
    }

    //百度账号登录方法
    public function BaiDuLogin(){
        $callback_url = 'https://www.sucai.biz/callback.html?type=baidu';
        $obj = new Baidu(config::get('oauth-baidu-appid') ,config::get('oauth-baidu-appkey') ,$callback_url);
        $baidu_login_url = $obj->getLoginUrl();
        header("Location:".$baidu_login_url);
    }

    //Github账号登录方法
    public function GithubLogin(){
        $callback_url = 'https://www.sucai.biz/callback.html?type=github';
        $obj = new Github(config::get('oauth-github-appid') , config::get('oauth-github-appkey') ,$callback_url);
        $github_login_url = $obj->getAuthorizeUrl();
        header("Location:".$github_login_url);
    }
    //第三方登录回调地址
    public function CallBack(){

        $code = input('code');
        $type = input('type');
        $uuid = input('uid');
        if(!isset($uuid) || !is_numeric($uuid) || empty($uuid)){
            $uuid = 0;
        }
        if(!isset($code) || !isset($type)){
            echo '非法访问';
            die;
        }
        if($type == 'weibo'){
            $a = new SaeTOAuthV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey'));
            $res = $a->getAccessToken('code' ,['code'=>$code ,'redirect_uri'=>'https://www.sucai.biz/callback.html?type=weibo']);
            $access_token = $res['access_token'];
            $uid = $res['uid'];
            Session::set('token' ,$uid);
            $b = $uid;
            //获取用户信息
            $sae = new SaeTClientV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey'),$access_token);
            $info = $sae->user_timeline_by_id($uid);
            $info = $info['statuses'][0]['user'];
            //组合用户信息存入session
            $user_info = [
                'face'=>$info['avatar_hd'],
                'token'=>md5($uid),
                'nickname'=>$info['name']
            ];
            Session::set('binging_user_info' ,$user_info);
            $where = ['weibo_token'=>md5($uid)];
        }else if($type == 'qq'){
            $callback_url = 'https://www.sucai.biz/callback.html?type=qq';
            $qc = new QQLogin(config::get('oauth-qq-appid') ,config::get('oauth-qq-appkey') ,$callback_url);
            $qc->qq_callback();
            $oid=$qc->get_openid(); //openid
            Session::set('token' ,$oid);
            $b = $oid;
            $where = ['qq_token'=>md5($oid)];
        }else if($type == 'baidu'){
            $callback_url = 'https://www.sucai.biz/callback.html?type=baidu';
            $obj = new Baidu(config::get('oauth-baidu-appid') ,config::get('oauth-baidu-appkey') ,$callback_url);
            $res = $obj->getLoggedInUser();
            $uid = $res['uid'];
            //组合用户信息存入session
            $info = $obj->getSession();
            $user_info = [
                'face'=>'http://tb.himg.baidu.com/sys/portrait/item/' .$info['portrait'],
                'token'=>md5($uid),
                'nickname'=>$info['uname']
            ];
            Session::set('binging_user_info' ,$user_info);
            $b = $res['uid'];
            $where = ['baidu_token'=>md5($uid)];
        }else if($type == 'github'){
            $callback_url = 'https://www.sucai.biz/callback.html?type=github';
            $obj = new Github(config::get('oauth-github-appid') , config::get('oauth-github-appkey') ,$callback_url);
            $access_token = $obj->getAccessToken($code);
            $res = $obj->getUserInfo($access_token);
            //组合数据存入session
            $info = [
                'nickname'=>$res['login'],
                'face'=>$res['avatar_url'],
                'token'=>md5($res['id'])
            ];
            Session::set('binging_user_info' ,$info);
            $uid = $res['id'];
            Session::set('token' ,$uid);
            $b = $res['id'];
            $where = ['github_token' =>md5($uid)];
        }
        $Author = new Author();
        $author_Count = $Author->getAuthorCount($where);
        if($author_Count == 0){
            header("location:/binding.html?type=$type&code=$code&token=$b&flow=1&uid=$uuid");
        }else if($author_Count == 1){
            $author_info = $Author->getAuthor($where ,' uid ');
            $uid = $author_info['uid'];
            $user = new \app\member\model\User();
            $Member_Info = $user->getUser(['id'=>$uid]);
            $this->RecordRemember($Member_Info ,$type);
            header('location:/member/index.html');
        }
    }
    //绑定账号方法
    public function binding(){
        $code = input('code');
        $type = input('type');
        $token = input('token');
        $flow = input('flow');
        $uid = input('uid');
        if(!isset($code) || !isset($type) || !isset($token) || !isset($flow)){
            echo '非法访问';
            die;
        }
        $binging_user_info = Session::get('binging_user_info');
        if(Request::instance()->isPost()){
            $Member = model('User');
            if($flow == 2){
                $username = input('name');
                $password = input('password');
                if(!isset($username) || !isset($password)){
                    echo '非法访问';
                    die;
                }
                //判断账号类型
                $phone_rule = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
                if (preg_match($phone_rule, $username)) {
                    $where = ['phone' => $username];
                } else if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $where = ['email' => $username];
                } else {
                    $where = ['username' => $username];
                }

                $Member_info = $Member->getUser($where);
                if(!empty($Member_info)){
                    if(getUserPwd($password) == $Member_info['password']){
                        $this->RecordRemember($Member_info ,$type);
                        //组合查询条件
                        $author_where = [
                            'uid'=>$Member_info['id'],
                        ];
                        //获取用户扩展信息条数
                        $author = new Author();
                        $counnt = $author->getAuthorCount($author_where);
                        $author_where["$type"."_token"] = ['EXP' ,'is null'];
                        //获取对应扩展信息的条数
                        $count1 = $author->getAuthorCount($author_where);
                        if($counnt == 1 && $count1 == 0){
                            $a = [
                                'errorcode'=>1,
                                'msg'=>'已经绑定过账户，请到个人中心->账户绑定中解绑账户'
                            ];
                        }else{
                            //组合数据写入用户扩展表
                            $arr = [
                                "$type".'_token'=>$binging_user_info['token'],
                                "$type".'_user_name'=>$binging_user_info['nickname'],
                                "$type".'_user_face'=>$binging_user_info['face']
                            ];
                            //根据账户信息选择添加还是更新账户扩展信息
                            if($counnt == 0){
                                $arr['uid']=$Member_info['id'];
                                $res = $author->addAuthor($arr);
                                if($res){
                                    $a = [
                                        'errorcode'=>0,
                                        'msg'=>'绑定账户成功'
                                    ];
                                }else{
                                    $a = [
                                        'errorcode'=>1,
                                        'msg'=>'绑定账号失败'
                                    ];
                                }
                            }else{
                                $res = $author->alterAuthor(['uid'=>$Member_info['id']] ,$arr);
                                if($res){
                                    $a = [
                                        'errorcode'=>0,
                                        'msg'=>'绑定账户成功'
                                    ];
                                }else{
                                    $a = [
                                        'errorcode'=>1,
                                        'msg'=>'绑定子账号失败'
                                    ];
                                }
                            }
                        }
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }else{
                        $a = [
                            'errorcode'=>1,
                            'msg'=>'密码不正确'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'账号不存在'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
            }else if($flow == 3){
                //判断用户登录的session是否存在
                if(Session::has('user')){
                    $user_info = Session::get('user');
                    $where = ['id'=>$user_info['id']];
                    if($type == 'weibo'){
                        $data = ['sina_uid'=>$token];
                    }else if($type == 'qq'){
                        $data = ['qq_token'=>$token];
                    }else if($type == 'baidu'){
                        $data = ['baidu_uid'=>$token];
                    }else if($type == 'github'){
                        $data = ['github_uid'=>$token];
                    }
                    //更新账号信息
                    $res = $Member->updateUser($where ,$data);
                    if($res){
                        $a = [
                            'errorcode'=>0,
                            'msg'=>'账号绑定成功'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }else{
                        $a = [
                            'errorcode'=>1,
                            'msg'=>'账号绑定失败'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    echo '请先登录执行此项炒作';die;
                }
            }
        }else{
            if($type == 'qq'){
                $face_url = '/public/png/qq-face.png';
                //获取qq用户信息
                $callback_url = 'http://www.sucai.biz/callback.html?type=qq';
                $qc = new QQLogin(config::get('oauth-qq-appid') ,config::get('oauth-qq-appkey') ,$callback_url);
                $qq_info = $qc->getUserInfo($token);
                if(!isset($qq_info['ret']) && $qq_info['ret'] != 0){
                    echo '获取用户信息失败,请联系管理员。email:sucaiz@qq.com';die;
                }
                $user_info = $qq_info;
                $user_info['face'] = $user_info['figureurl_qq_1'];
                $user_info['token'] = md5(Session::get('token'));
                //将qq账户信息存入session
                Session::set('binging_user_info' ,$user_info);
            }else if($type == 'baidu'){
                $user_info = Session::get('binging_user_info');
                $face_url = $user_info['face'];
            }else if($type == 'github'){
                $user_info = Session::get('binging_user_info');
                $face_url = $user_info['face'];
            }else if($type == 'weibo'){
                $user_info = Session::get('binging_user_info');
                $face_url = $user_info['face'];
            }
            //根据uid直接绑定授权信息
            if($uid != 0){
                //组合查询条件
                $where = ['uid'=>$uid];
                $author = new Author();
                $author_count = $author->getAuthorCount($where);
                if($author_count == 0){
                    $arr = [
                        'uid'=>$uid,
                        $type .'_token' => Session::get('binging_user_info')['token'],
                        $type .'_user_name' => Session::get('binging_user_info')['nickname'],
                        $type .'_user_face' => Session::get('binging_user_info')['face']
                    ];
                    $res = $author->addAuthor($arr);
                }else{
                    $arr = [
                        $type .'_token' => Session::get('binging_user_info')['token'],
                        $type .'_user_name' => Session::get('binging_user_info')['nickname'],
                        $type .'_user_face' => Session::get('binging_user_info')['face']
                    ];
                    $res = $author->alterAuthor($where ,$arr);
                }
                if($res){
                    dump('绑定账户授权信息成功');die;
                }else{
                    dump('绑定账户授权信息失败');die;
                }
            }

            $this->assign('code' ,$code);
            $this->assign('type' ,$type);
            $this->assign('token' ,$token);
            if($flow == 1){
                View::share('face' ,$face_url);
                View::share('user_info' , $user_info);
                return View('templates/binding');
            }else if($flow == 2){
                return View('templates/binding2');
            }else if($flow == 3){
                return View('templates/binding3');
            }

        }
    }

    //记录会员内容
    public function RecordRemember($user_info ,$method = ''){
        //将用户信息存入session中
        Session::set('user', $user_info);
        //判断前台是否提交cookies状态值
        $cookie_status = input('cookie_status');
        if(empty($cookie_status)){
            $cookie_status = true;
        }
        //存在并且为true,则向浏览器存储cookie
        if(isset($cookie_status) && $cookie_status == true){
            $cookie_info = [
                'uid'=>$user_info['id'],
                'username'=>$user_info['nickname'],
                'face'=>$user_info['face'],
                'token'=>$user_info['token']
            ];
            $cookie = json_encode($cookie_info ,JSON_UNESCAPED_UNICODE);
            Cookie::set('member_info' ,$cookie ,['expire'=>2678400  ,'path'=>'/']);
        }
        //添加用户登录日志
        $arr = [
            'uid' => $user_info['id'],
            'login_time' => time(),
            'login_ip' => $_SERVER['REMOTE_ADDR'],
            'browser' => getBrowserInfo(),
            'type'=>1,
            'method'=>$method
        ];
        $log = model('Log');
        $log->addLoginLog($arr);
    }

    //评论登录
    public function login1(){

    }
}