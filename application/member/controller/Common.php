<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/3
 * Time: 14:35
 * Description：中间控制器
 */

namespace app\member\controller;

use app\common\controller\BaseController;
use SucaiZ\File;
use think\Controller;
use think\Cookie;
use think\Session;

class Common extends BaseController
{
    public $cookie_name = 'member_info';
    public $member_info;

    public function __construct()
    {
        parent::__construct();
        //判断客户端cookie是否存在，并且和数据库中的一致
        $cookie_status = Cookie::has($this->cookie_name);
        if ($cookie_status) {
            $info = Cookie::get($this->cookie_name);
            $cookie_info = json_decode($info,true);
            if (isset($cookie_info['uid']) || isset($cookie_info['token'])) {
                $user = new \app\member\model\User();
                $where = [
                    'id' => $cookie_info['uid'],
                    'token' => $cookie_info['token']
                ];
                $member_info = $user->getUser($where);
                Session::set('user', $member_info);
            }
        }
        $this->member_info = Session::get('user');
        if(empty($this->member_info)){
            die;
            header('location:http://www.sucai.biz/login.html');
        }
        File::setUserInfo(2 ,$this->member_info['id']);

    }

    //防翻墙方法
    public function _initialize()
    {
        parent::_initialize();
        //判断客户端cookie是否存在，并且和数据库中的一致
        $cookie_status = Cookie::has($this->cookie_name);
        if (!$cookie_status) {
            $cookie_info = Cookie::get($this->cookie_name);
            if (!isset($cookie_info['uid']) || !isset($cookie_info['token'])) {
                $user = new \app\member\model\User();
                $where = [
                    'id' => $cookie_info['uid'],
                    'token' => $cookie_info['token']
                ];
                $count = $user->getUserCount($where);
                //判断会员session是否存在
                $session_status = Session::has('user');
                if ($count != 1 && !$session_status) {
                    header('location:/login.html');
                }
            }
        }
    }
}