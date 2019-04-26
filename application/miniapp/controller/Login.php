<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/13
 * Time: 1:21
 * Description: 登录管理
 */


namespace app\miniapp\controller;

use app\miniapp\model\Log;
use app\miniapp\model\User;
use SucaiZ\config;
use think\Collection;
use verfily\verfily;

class Login extends Collection
{
    //存储redis实例
    private $redis;

    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
    }

    public function login()
    {
        //验证数据
        $username = input('username');
        if (!isset($username)) {
            echo '非法访问';
            die;
        }
        //判断类型
        $phone_rule = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
        if (preg_match($phone_rule, $username)) {
            $where = ['phone' => $username];
        } else if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $where = ['email' => $username];
        } else {
            $where = ['username' => $username];
        }
        if (empty($username)) {
            $a = [
                'errorcode' => 1,
                'msg' => '用户名不能为空哦'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        $password = input('password');
        if (!isset($password)) {
            echo '非法访问';
            die;
        }
        if (empty($password)) {
            $a = [
                'errorcode' => 1,
                'msg' => '密码不能为空哦'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        $user = new User();
        $user_info = $user->getUserInfo($where, ' * ');
        if (empty($user_info)) {
            $a = [
                'errorcode' => 1,
                'msg' => '用户不存在'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            if (empty($user_info['password'])) {
                $a = [
                    'errorcode' => 2,
                    'msg' => '用户信息不完整',
                    'uid' => $user_info['id']
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (getUserPwd(sha1($password)) == $user_info['password']) {
                //添加登录
                $this->insertLoginLog($user_info['id'], 'miniapp');
                $a = [
                    'errorcode' => 0,
                    'msg' => '登录成功',
                    'user_info' => [
                        'nickname' => $user_info['nickname'],
                        'id' => $user_info['id'],
                        'level' => $user_info['level'],
                        'face' => config::get('cfg_hostsite') . $user_info['face'],
                        'create_time' => date('Y-m-d', $user_info['create_time'])
                    ]
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a = [
                    'errorcode' => 1,
                    'msg' => '用户密码不正确'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        }
    }

    public function wxLogin()
    {
        //验证数据
        $code = input('code');
        if (!isset($code)) {
            echo '非法访问';
            die;
        }
        $code = input('code');
        $openid = $this->getOpenid($code);
        if (!$openid) {
            $a = [
                'errorcode' => 1,
                'msg' => '登录失败'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }

        $user = new User();
        $user_info = $user->getUserInfo(['mini_token' => $openid], ' * ');
        if (empty($user_info)) {
            $a = [
                'errorcode' => 2,
                'msg' => '请绑定账号',
                'openid' => $openid
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 0,
                'msg' => '登录成功',
                'user_info' => [
                    'nickname' => $user_info['nickname'],
                    'id' => $user_info['id'],
                    'level' => $user_info['level'],
                    'create_time' => date('Y-m-d', $user_info['create_time'])
                ]
            ];
            if(strstr($user_info['face'],"http")){
                $a['user_info']['face'] = $user_info['face'];
            }else{
                $a['user_info']['face'] = config::get('cfg_hostsite') . $user_info['face'];
            }
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }


    }

    //获取微信账号的openid
    public function getOpenid($code)
    {
        $appid = config::get('cfg_wechat_mini_appid');
        $appsecret = config::get('cfg_wechat_mini_appsecret');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$appsecret&js_code=$code&grant_type=authorization_code";
        $weixin = file_get_contents($url);
        $jsondecode = json_decode($weixin);
        $array = get_object_vars($jsondecode);//转换成数组
        if (isset($array['openid'])) {
            return $array['openid'];
        } else {
            return false;
        }

    }

    //获取验证码
    public function getVerfyImg()
    {
        $code = input('code');
        if (!isset($code)) {
            echo '非法访问';
            die;
        }
        $verfily = new verfily();
        $verfily->codelen = 4;
        $verfily->width = 92;
        $verfily->height = 32;
        $verfily->doimg();
        $verfilyCode = $verfily->getVerfyCode();
        $this->redis->set($code, $verfilyCode, 3600);
        $verfily->outPut();
    }

    //验证手机验证码
    public function pushSms()
    {
        //验证数据
        $phone = input('phone');
        if (!isset($phone)) {
            echo '非法访问';
            die;
        }
        //验证手机格式
        if (mb_strlen($phone, 'UTF-8') != 11) {
            $a = [
                'errorcode' => 1,
                'msg' => '手机号格式不正确'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        $phone_rule = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
        if (!preg_match($phone_rule, $phone)) {
            $a = [
                'errorcode' => 1,
                'msg' => '手机号格式不正确'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        //验证图片验证码
        $verify = input('verify');
        if (!isset($verify)) {
            echo '非法访问';
            die;
        }
        $code = input('code');
        if (!isset($code)) {
            echo '非法访问';
            die;
        }
        $verify_code = $this->redis->get($code);
        if (empty($verify_code)) {
            $a = [
                'errorcode' => 1,
                'msg' => '图片验证码不存在'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }

        if (strtolower($verify) != strtolower($verify_code)) {
            $a = [
                'errorcode' => 1,
                'msg' => '图片验证码不正确'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        //判断手机号是否已经注册过账号
        $user = new User();
        $user_num = $user->getUserCount(['phone' => $phone]);
        if ($user_num != 0) {
            $a = [
                'errorcode' => 2,
                'msg' => '已经注册过，请登录'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        //生成手机的验证码
        $phone_code = rand(10000, 99999);
        $templateparams = ['code' => $phone_code];
        $b = [
            'function' => 'sendSms1',
            'phone' => $phone,
            'singname' => '素材站',
            'templatescode' => 'SMS_133000964',
            'templateparams' => $templateparams
        ];
        task('verify', $b);
        $this->redis->set($code, $phone_code, 600);
        $a = [
            'errorcode' => 0,
            'msg' => '发送成功'
        ];
        return json_encode($a, JSON_UNESCAPED_UNICODE);
    }

    //注册账号方法
    public function register()
    {
        //判断类型
        $type = input('type');
        if (!isset($type)) {
            echo '非法访问';
            die;
        }
        if ($type == 'phone') {
            //验证数据
            $phone = input('phone');
            if (!isset($phone)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($phone, 'UTF-8') != 11) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '手机号格式不正确'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $phone_rule = '/^0?1[3|4|5|6|7|8][0-9]\d{8}$/';
            if (!preg_match($phone_rule, $phone)) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '手机号格式不正确'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $verify_code = input('verifly_code');
            if (!isset($verify_code)) {
                echo '非法访问';
                die;
            }
            $code = input('code');
            if (!isset($code)) {
                echo '非法访问';
                die;
            }
            $phone_code = $this->redis->get($code);
            if ($verify_code != $phone_code) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '手机验证码不正确'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            //组合数据添加到数据库
            $user = new User();
            $arr = [
                'token' => createUserToken(),
                'type' => 1,
                'create_time' => time(),
                'status' => 2,
                'level' => 1,
                'phone' => $phone,
                'experience' => 0,
                'gold' => '0',
                'integral' => 0,
                'comment_status' => 1,
                'face' => '/upload/face/' . rand(1, 24) . '.jpg',
            ];
            $uid = $user->addUserInfo($arr);
            if ($uid) {
                //获取用户头像
                $user_info = $user->getUserInfo(['id' => $uid]);
                $a = [
                    'errorcode' => 0,
                    'msg' => '注册成功',
                    'uid' => $uid,
                    'face' => config::get('cfg_hostsite') . $user_info['face']
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a = [
                    'errorcode' => 1,
                    'msg' => '注册失败'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }

        } else if ($type == 'perfect') {
            //验证数据
            $uid = input('uid');
            if (!isset($uid)) {
                echo '非法访问';
                die;
            }
            $nickname = input('nickname');
            if (!isset($nickname)) {
                echo '非法访问';
                die;
            }
            if (empty($nickname)) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '昵称不能为空'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($nickname, 'UTF-8') > 20) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '昵称不能超过20个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $password = input('password');
            if (!isset($password)) {
                echo '非法访问';
                die;
            }
            if (empty($password)) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '密码不能为空'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $verifypassword = input('verifypassword');
            if (!isset($verifypassword)) {
                echo '非法访问';
                die;
            }
            if ($password != $verifypassword) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '两次输入的密码不一致'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            //判断用户名是否存在
            $user = new User();
            $num = $user->getUserCount(['nickname' => $nickname]);
            if ($num != 0) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '昵称已存在'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }

            //组合数据保存到数据库
            $arr = [
                'nickname' => $nickname,
                'password' => getUserPwd(sha1($password)),
                'alter_time' => time()
            ];
            $where = ['id' => $uid];
            if ($user->updateUserInfo($where, $arr)) {
                //保存成功，获取账号信息
                $user_info = $user->getUserInfo($where);
                $a = [
                    'errorcode' => 0,
                    'msg' => '保存成功',
                    'user_info' => [
                        'nickname' => $user_info['nickname'],
                        'id' => $user_info['id'],
                        'level' => $user_info['level'],
                        'face' => config::get('cfg_hostsite') . $user_info['face']
                    ]
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a = [
                    'errorcode' => 1,
                    'msg' => '保存失败'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }

        } else if ($type = "wxlogin") {
            //验证数据
            $nickname = input('username');
            if (!isset($nickname)) {
                echo '非法访问';
                die;
            }
            if (empty($nickname)) {
                $a = [
                    'errorcode' => 0,
                    'msg' => '昵称不能为空哦'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $face = input('face');
            if (!isset($face)) {
                echo '非法访问';
                die;
            }
            if (empty($face)) {
                $a = [
                    'errorcode' => 0,
                    'msg' => '用户头像不能为空哦'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }

            $password = input('password');
            if (!isset($password)) {
                echo '非法访问';
                die;
            }

            $verftypassword = input('verftypassword');
            if (!isset($verftypassword)) {
                echo '非法访问';
                die;
            }

            if ($password != $verftypassword) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '两次输入的密码不一致'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }

            $openid = input('openid');
            if (!isset($openid) || empty($openid)) {
                echo '非法访问';
                die;
            }
            $user = new User();
            //检查昵称是否重复
            $count = $user->getUserCount(['nickname' => $nickname]);
            if ($count != 0) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '已经存在该昵称啦'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            //组合数据添加到数据库
            $arr = [
                'nickname' => $nickname,
                'password' => getUserPwd(sha1($password)),
                'face' => $face,
                'mini_token' => $openid,
                'token' => getArticleToken(),
                'type' => 1,
                'create_time' => time(),
                'status' => 2,
                'experience' => 0,
                'gold' => 0,
                'integral' => 0,
                'comment_status' => 1
            ];

            //检查是否已经注册过账号
            $count = $user->getUserCount(['mini_token' => $openid]);
            if ($count != 0) {
                $a = [
                    'errorcode' => 2,
                    'msg' => '已经注册过账号',
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if ($user->addUserInfo($arr)) {
                //获取会员账号信息
                $user_info = $user->getUserInfo(['mini_token' => $openid], ' * ');
                $a = [
                    'errorcode' => 0,
                    'msg' => '注册成功',
                    'user_info' => [
                        'nickname' => $user_info['nickname'],
                        'id' => $user_info['id'],
                        'level' => $user_info['level'],
                        'create_time' => date('Y-m-d', $user_info['create_time'])
                    ]
                ];
                if(strstr($user_info['face'],"http")){
                    $a['user_info']['face'] = $user_info['face'];
                }else{
                    $a['user_info']['face'] = config::get('cfg_hostsite') . $user_info['face'];
                }
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a = [
                    'errorcode' => 1,
                    'msg' => '注册失败'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else if ($type = "bind") {

        }
    }

    //添加登录日志
    private function insertLoginLog($uid, $method)
    {
        //组合数据添加到登录日志表
        $arr = [
            'uid' => $uid,
            'login_time' => time(),
            'login_ip' => $_SERVER['REMOTE_ADDR'],
            'browser' => '',
            'type' => 1,
            'method' => $method
        ];
        $log = new Log();
        $log->insertLoginLog($arr);
    }
}