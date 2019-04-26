<?php
namespace app\admin\controller;
use think\Request;
use think\Session;
use think\Url;

class Login {
    private $Redis = null;
    public function __construct()
    {

        $this->Redis = getRedis();
    }

    //后台登录方法
    public function login(){
        //判断session中是否已经存在管理员信息
        $admin_info = Session::get('admin');
        if(isset($admin_info)){
            header('location:/admin/index.html');
        }
        if(Request::instance()->isPost()){
            //验证用户名和密码
            if(input('username')==''){
                echo '输入的用户名不能为空';
                die;
            }
            if(input('password')==''){
                echo '输入的用户密码不能为空';
                die;
            }
            $admin_login_sum_key = 'admin_' .$_SERVER['REMOTE_ADDR'];
            if(!$this->Redis->exists($admin_login_sum_key)){
                $this->Redis->set($admin_login_sum_key,1,1800);
            }
            //判断用户登录次数，超过次数给予用户提示
            if($this->Redis->get($admin_login_sum_key)<5){
                //根据用户名取出用户信息
                $user = model('user');
                $user_info = $user->getUserInfoOne(['user_name'=>input('username')]);
                if($user_info){
                    //验证密码是否正确
                    if($user_info['user_password'] == getAdminPassword(input('password'))){
                        //验证用户账号状态
                        if($user_info['state'] == 2){
                            $a['errorcode'] = 1;
                            $a['msg'] = "账号被禁用，请联系管理员..";
                            return json_encode($a,JSON_UNESCAPED_UNICODE);
                        }
                        $this->Redis->delete($admin_login_sum_key);
                        //将用户信息存入session中
                        Session::set('admin' ,$user_info);
                        //添加用户登录日志
                        $arr = [
                            'uid'=>$user_info['id'],
                            'login_time'=>time(),
                            'login_ip'=>$_SERVER['REMOTE_ADDR'],
                            'browser'=>getBrowserInfo(),
                            'type'=>2
                        ];
                        $log = model('Log');
                        $log->table_name = 'login_log_table_name';
                        $log->createTableInfo($arr);
                        $a['errorcode'] = 0;
                        $a['msg'] = "登录成功...";
                        $a['skip_url'] = '/admin/index.html';
                        return json_encode($a,JSON_UNESCAPED_UNICODE);
                    }else{
                        $this->Redis->incr($admin_login_sum_key);
                        $a['errorcode'] = 1;
                        $a['msg'] = "输入的用户密码不正确！";
                        return json_encode($a,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    $this->Redis->incr($admin_login_sum_key);
                    $a['errorcode'] = 1;
                    $a['msg'] = "输入的用户名不存在！";
                    return json_encode($a,JSON_UNESCAPED_UNICODE);
                }
            }else{
                $a["errorcode"] = 1;
                $a["msg"] = "您输入的次数过多，请" .$this->Redis->ttl($admin_login_sum_key) ."秒后再试！";
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }
        }else{
            return View();
        }
    }

    //后台账号退出方法
    public function loginout(){
        Session::clear();
        $a['errorcode'] = 0;
        $a['msg'] = "退出成功";
        $a['url'] = Url::build();
        header('location:/admin/login.html');
    }


}