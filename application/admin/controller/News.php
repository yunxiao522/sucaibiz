<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/14
 * Time: 17:29
 * Description: 消息管理
 */

namespace app\admin\controller;
use app\admin\model\Msg;
use think\Request;

class News extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getNewsInfo(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            return self::ajaxError('非法访问');
        }
        //组合查询条件
        $where = [
            'user_id'=>$uid,
            'user_type'=>1,
            'status'=>1
        ];
        $msg = new Msg();
        $count = $msg->getCount($where);
        if(empty($count)){
            $a = [
                'errorcode'=>2,
                'msg'=>'没有新信息'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'获取信息条数成功',
                'count'=>$count
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
}