<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/12
 * Time: 11:47
 * Description：中间控制器
 */

namespace app\admin\controller;

use app\common\controller\BaseController;
use think\Session;
use SucaiZ\File;
use SucaiZ\Rbac;

class Common extends BaseController
{
    private $preserve_redis_key = 'preserve';
    public $admin_info;


    public function __construct()
    {
        parent::__construct();

    }

    public function _initialize()
    {
        $admin_info = Session::get('admin');
        //判断请求是否是ajax
        $preserve_status_key = $this->preserve_redis_key .'_status';
        $preserve_info_key = $this->preserve_redis_key .'_info';
        $redis = getRedis();
        if($redis->exists($preserve_status_key) && $redis->exists($preserve_info_key)){
            header('location:/preserve.html');
        }
        //判断管理员登录信息是否存在
        if (!isset($admin_info)) {
            header('location:/admin/login.html');die;
        }else{
            //初始化数据，方便上传文件使用
            File::setUserInfo($admin_info['type'] ,$admin_info['id']);
            $this->admin_info = $admin_info;
        }

        //判断用户权限
//        $Rbac = new Rbac();
//        if(!$Rbac->isAuth($admin_info['id'] ,$admin_info['type'] ,['type'=>2])){
//            echo '无权访问该页面';die;
//        }
    }
}