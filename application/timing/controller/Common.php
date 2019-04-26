<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/5
 * Time: 11:22
 */

namespace app\timing\controller;
use think\Controller;
use think\Request;
class Common extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->verfilyIp();
    }
    //验证访问者的ip地址
    private function verfilyIp(){
        $ip = Request::instance()->ip();
        if(false){
            echo '您的ip为' .$ip .',被禁止访问</br>';
            echo '非法访问';die;
        }
    }

    public function test(){
        dump(1);
    }

}