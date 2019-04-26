<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/26
 * Time: 17:10
 */

namespace app\index\controller;


use app\common\controller\BaseController;
use think\Cookie;
use think\Session;

class Common extends BaseController
{
    public $cookie_name = 'member_info';
    public $member_info;
    public $uid = 0;

    public function __construct()
    {
        parent::__construct();
        $this->getUserInfo();

    }

    //获取用户信息
    public function getUserInfo(){
        //判断客户端cookie是否存在，并且和数据库中的一致
        $cookie_status = Cookie::has($this->cookie_name);
        if ($cookie_status) {
            $cookie_info = json_decode(Cookie::get($this->cookie_name),true);
            if (isset($cookie_info['uid']) || isset($cookie_info['token'])) {
                $user = new \app\member\model\User();
                $where = [
                    'id' => $cookie_info['uid'],
                    'token' => $cookie_info['token']
                ];
                $member_info = $user->getUser($where);
                Session::set('user', $member_info);
                $this->uid = $cookie_info['uid'];
            }
        }
        $this->member_info = Session::get('user');
    }
}