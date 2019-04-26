<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/16
 * Time: 13:02
 * Description：消息中心
 */

namespace app\member\controller;


use app\member\model\Msg;
use SucaiZ\Page;
use think\Request;
use think\View;
use app\member\model\Level;

class Message extends Common
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
    //获取消息方法
    public function getMsg(){
        $uid = $this->member_info['id'];
        //组合查询条件
        $where = [
            'user_type'=>1,
            'user_id'=>$uid,
            'status'=>1
        ];
        $msg = new Msg();
        $list = $msg->getMsgList($where ,' id,title,create_time  ' ,5);
        foreach ($list as $key => $value){
            $list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
            $list[$key]['title'] = '[系统消息] ' .$value['title'];
        }
        if(empty($list)){
            $a = [
                'errorcode'=>2,
                'msg'=>'没有新消息'
            ];
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'获取消息成功',
                'data'=>$list
            ];
        }
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }
    //查看消息列表
    public function msg(){
        //获取参数
        $class = input('class');
        if(!isset($class) || empty($class) || !is_string($class)){
            $class = 'all';
        }
        if($class == 'all'){
            //组合查询条件
            $where  = [
                'user_id'=>$this->member_info['id'],
                'user_type'=>1
            ];
        }else if($class == 'recommend'){
            $where = [
                'user_id'=>$this->member_info['id'],
                'user_type'=>1,
                'class'=>1
            ];
        }else if($class == 'attention'){
            $where = [
                'user_id'=>$this->member_info['id'],
                'user_type'=>1,
                'class'=>2
            ];
        }else if($class == 'notice'){
            $where = [
                'user_id'=>$this->member_info['id'],
                'user_type'=>1,
                'class'=>3
            ];
        }else if($class == 'system'){
            $where = [
                'user_id'=>$this->member_info['id'],
                'user_type'=>1,
                'class'=>4
            ];
        }else if($class == 'team'){
            $where = [
                'user_id'=>$this->member_info['id'],
                'user_type'=>1,
                'class'=>5
            ];
        }
        $msg = new Msg();
        $user = new \app\member\model\User();
        //获取分页相关数据
        $limit = input('limit');
        if (!isset($limit) || !is_numeric($limit)) {
            $limit = 10;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        $list = $msg->getMsgList($where ,' * ' ,$limits);
        //循环列表数据
        foreach($list as $key => $value){
            $list[$key]['source_info'] = $user->getUser(['id'=>$value['source']] ,' nickname,face,id ');
            $list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
            //循环更新消息状态
            $msg->updateMsgInfo(['id'=>$value['id']] ,['status'=>2]);
        }
        $count = $msg->getCount($where);
        //实例化分页类
        $paging = new Page($count ,$limit);
        //分配分页数据到页面
        View::share('paging' ,$paging->render());
        View::share('list' ,$list);
        View::share('class' ,$class);
        View::share('type' ,'msg');
        return View('templates/message_msg');
    }
    //查看私信
    public function personal(){
        //获取分页相关数据
        $limit = input('limit');
        if (!isset($limit) || !is_numeric($limit)) {
            $limit = 10;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        View::share('type' ,'personal');
        return View('templates/message_personal');
    }
    //查看评论和回复
    public function comment(){
        //获取分页相关数据
        $limit = input('limit');
        if (!isset($limit) || !is_numeric($limit)) {
            $limit = 10;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        View::share('type' ,'comment');
        return View('templates/message_comment');
    }
    //查看消息详细信息
    public function show(){
        //验证数据
        $id = input('id');
        if(!isset($id) || empty($id) || !is_numeric($id)){
            echo '非法访问';die;
        }
        //组合条件验证消息id是否是本人消息
        $where = [
            'id'=>$id,
            'user_type'=>1,
            'user_id'=>$this->member_info['id']
        ];
        $msg = new Msg();
        $count = $msg->getCount($where);
        if($count == 0){
            echo '不能看属于别人的消息哦';die;
        }else{
            //根据id获取消息详细信息
            $msg_info = $msg->getMsgInfo($where);
            View::share('msg_info' ,$msg_info);
            View::share('user_info' ,$this->member_info);
            View::share('type' ,'msg');
            return View('templates/message_show');
        }
    }
    //发送私信方法
    public function sendMessage(){
        //验证前台提交的数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        if(Request::instance()->isPost()){
            //验证前台提交的数据
            $content = input('content');
            if(empty($content)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的私信内容不能为空哦'
                ];
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($content ,'UTF-8') > 200){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的私信内容不能超过200个字符哦'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $msg = new Msg();
            //组合数据添加到数据库内
            $arr = [
                'user_type'=>1,
                'user_id'=>$uid,
                'title'=>'私信',
                'content'=>$content,
                'status'=>2,
                'create_time'=>time(),
                'alter_time'=>0,
                'class'=>6,
                'source'=>$this->member_info['id']
            ];
            $res = $msg->addMsgInfo($arr);
            if($res){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'发送成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'发送失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            //组合查询条件，获取收件人昵称
            $where = [
                'id'=>$uid
            ];
            $user = new \app\member\model\User();
            $user_info = $user->getUser($where ,' id,nickname ');
            if(empty($user_info)){
                echo '用户不存在';die;
            }else{
                View::share('user_info' ,$user_info);
                return View('templates/message_sendmessage');
            }

        }
    }
}