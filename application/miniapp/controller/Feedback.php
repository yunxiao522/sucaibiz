<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/19
 * Time: 12:20
 * Description:
 */


namespace app\miniapp\controller;
use think\Collection;

class Feedback extends Collection
{
    public function __construct()
    {
        parent::__construct();
    }

    //添加反馈
    public function add(){
        //验证数据
        $title = input('title');
        if(!isset($title)){
            echo '非法访问';die;
        }
        if(empty($title)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的反馈标题不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        if(mb_strlen($title ,'UTF-8') >80){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的标题不能超过80个字符'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        $content = input('content');
        if(!isset($content)){
            echo '非法访问';die;
        }
        if(empty($content)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的反馈内容不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        //组合数据添加到数据库
        $arr = [
            'uid'=>$uid,
            'title'=>$title,
            'content'=>$content,
            'create_time'=>time(),
            'status'=>1
        ];
        $feedback = new \app\miniapp\model\Feedback();
        if($feedback->addFeedBack($arr)){
            $a = [
                'errorcode'=>0,
                'msg'=>'反馈成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'反馈失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
}