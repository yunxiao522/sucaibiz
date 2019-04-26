<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/12
 * Time: 19:01
 * Description: 个人信息管理
 */


namespace app\miniapp\controller;
use think\Controller;

class My extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取个人信息评论总条数
    public function getCommentCount(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        $comment = new \app\miniapp\model\Comment();
        $count = $comment->getCommentCount(['uid'=>$uid]);
        $a = [
            'errorcode'=>0,
            'msg'=>'获取数据成功',
            'data'=>$count
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }
    //获取评论列表
    public function getCommentList(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';die;
        }
        //获取评论列表
        $comment = new \app\miniapp\model\Comment();
        $article = new \app\miniapp\model\Article();
        //构建查询条件
        $where = " uid = $uid ";
        if(!empty($start)){
            $where .= " and id > $start ";
        }
        $comment_list = $comment->getCommentList($where ,' * ' ,15);
        //循环列表
        foreach($comment_list as $key => $value){
            //初始化操作数据
            $value['support_status'] = false;
            $value['oppose_status'] = false;
            //判断用户是否登录，登录则获取评论投票信息
            if($uid != 0){
                //组合查询条件
                $where1 = [
                    'comment_id'=>$value['id'],
                    'uid'=>$uid
                ];
                $res = $comment->getCommentOperateInfo($where1);
                if(!empty($res)){
                    if($res['type'] == 1){
                        $value['support_status'] = true;
                    }else if($res['type'] == 2){
                        $value['oppose_status'] = true;
                    }
                }
            }
            $comment_list[$key] = $value;
            $article_info = $article->getArticleInfo(['id'=>$value['aid']] ,' id,title,channel ');
            $comment_list[$key]['time']=date('H:i:s' ,$value['create_time']);
            $comment_list[$key]['m']=date('m' ,$value['create_time']);
            $comment_list[$key]['d']=date('d ' ,$value['create_time']);
            $comment_list[$key]['info']=$article_info;

        }
        if(!empty($comment_list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$comment_list
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'无数据'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

}