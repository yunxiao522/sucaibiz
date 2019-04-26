<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/9
 * Time: 21:39
 * Description: 系统提示控制器
 */


namespace app\admin\controller;


use think\Controller;
use think\Request;

class Hint extends Controller
{
    private $preserve_redis_key = 'preserve';
    public function __construct()
    {
        parent::__construct();
    }

    public function Preserve(){
        $preserve_status_key = $this->preserve_redis_key .'_status';
        $preserve_info_key = $this->preserve_redis_key .'_info';
        $redis = getRedis();
        if(!$redis->exists($preserve_status_key) || !$redis->exists($preserve_info_key)){
            header('location:/admin/index.html');
        }
        return View('Preserve');
    }
}