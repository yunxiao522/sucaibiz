<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/19
 * Time: 19:46
 * Description: 评论管理
 */


namespace app\miniapp\controller;
use app\miniapp\model\User;
use think\Collection;

class Comment extends Collection
{
    //存储redis实例
    private $redis;
    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
    }

    //获取评论列表
    public function getCommentList(){
        //验证数据
        $aid = input('aid');
        if(!isset($aid) || empty($aid) || !is_numeric($aid)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';die;
        }
        $uid = input('uid');
        if(!isset($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }

        //组合查询条件
        $where = " aid = $aid and status = 1 ";
        if($start != 0){
            $where .= " and id < $start ";
        }
        //获取评论列表
        $comment = new \app\miniapp\model\Comment();
        $list = $comment->getCommentList($where ,' * ' ,15 ,' create_time desc ');
        //根据评论列表内容获取用户信息和操作信息
        $user = new User();
        foreach($list as $key => $value){
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
            $list[$key] = $value;
            $list[$key]['user_info'] = $user->getUserInfo(['id'=>$value['uid']] ,' id,nickname,face,level ');
            $list[$key]['user_info']['face'] = "https://www.sucai.biz" .$list[$key]['user_info']['face'];

        }
        //循环处理数据
        $a = [
            'errorcode'=>0,
            'msg'=>'获取数据成功',
            'data'=>$this->getCommentSon($list)
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //发表评论
    public function addComment(){
        //验证数据
        $uid = input('uid');

        $aid = input('aid');

        $comment = input('comment');

        $parent_id = input('parent_id');


    }

    //根据评论层数查询分类评论
    public function getCommentSon($list = [] ,$tier = 2){
        //首先取出第一层评论
        $arr = [];
        foreach($list as $key => $value){
            $time = $value['create_time'];
            $value['create_time'] = date('Y-m-d H:i:s' ,$time);
            $list[$key]['create_time'] = date('Y-m-d H:i:s' ,$time);
            if($value['parent_id'] == 0){
                $arr[] = $value;
                unset($list[$key]);
            }
        }
        //根据第一层评论，获取二层以上的评论
        foreach($arr as $key => $value){
            $son = getSonComment($list ,$value['id'] ,$value['tier'] ,$value['id'] );
            if(!empty($son) && isset($son[$value['id']])){
                $son = $son[$value['id']];
            }else{
                $son = [];
            }
            $arr[$key]['son'] =$son;
            $arr[$key]['level']= $value['tier'] .'楼';
        }
        return $arr;
    }
    //递归查询评论的子评论
    private function getSonComment($list ,$id  ,$tier){
        static $arr = [];
        foreach($list as $value){
            if($value['parent_id'] == $id){
                getSonComment($list ,$value['id'] ,$tier);
                $value['level']=$tier ."楼#" .$value['tier'];
                $arr[] = $value;
            }
        }
        return $arr;
    }

    //点赞操作
    public function support(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        $cid = input('cid');
        if(!isset($cid) || empty($cid) || !is_numeric($cid)){
            echo '非法访问';die;
        }

        //组合redis状态key
        $key = $uid .'_' .$cid;
        //验证是否在操作限制时间内
        $status = $this->redis->get($key);
        if(!empty($status)){
            $a = [
                'errorcode'=>2,
                'msg'=>'投票操作太频繁啦...'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        //检查是否有过反对操作
        //组合查询条件
        $where = [
            'uid'=>$uid,
            'comment_id'=>$cid,
            'type'=>2
        ];
        $comment = new \app\miniapp\model\Comment();
        $res = $comment->getCommentOperateInfo($where);
        if(!empty($res)){
            $a = [
                'errorcode'=>2,
                'msg'=>'不要贪心哦，只能投票一次哦'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        //查询是否已经点过赞,点过删除该条
        //重新组合查询条件
        $where = [
            'uid'=>$uid,
            'comment_id'=>$cid,
            'type'=>1
        ];
        $res = $comment->getCommentOperateInfo($where);
        if(!empty($res)){
            $res = $comment->delCommentOperateInfo($where);
            if($res){
                //设置操作状态
                $this->redis->set($key ,1 ,5);
                //修改评论表投票数量
                $comment->alterCommentVote('praiser', 2,['id'=>$cid]);
                $a = [
                    'errorcode'=>0,
                    'msg'=>'投票成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'投票失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }
        //查询结果为空则表示没有点赞操作，则插入一条新数据
        //组合插入数据
        $arr = [
            'uid'=>$uid,
            'comment_id'=>$cid,
            'create_time'=>time(),
            'type'=>1
        ];
        $res = $comment->addCommentOperate($arr);
        if($res){
            //设置操作状态
            $this->redis->set($key ,1 ,5);
            //修改评论表投票数量
            $comment->alterCommentVote('praiser', 1,['id'=>$cid]);
            $a = [
                'errorcode'=>0,
                'msg'=>'投票成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'投票失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //反对操作
    public function oppose(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        $cid = input('cid');
        if(!isset($cid) || empty($cid) || !is_numeric($cid)){
            echo '非法访问';die;
        }

        //组合redis状态key
        $key = $uid .'_' .$cid;
        //验证是否在操作限制时间内
        $status = $this->redis->get($key);
        if(!empty($status)){
            $a = [
                'errorcode'=>2,
                'msg'=>'投票操作太频繁啦...'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        //检查是否有过赞同操作
        //组合查询条件
        $where = [
            'uid'=>$uid,
            'comment_id'=>$cid,
            'type'=>1
        ];
        $comment = new \app\miniapp\model\Comment();
        $res = $comment->getCommentOperateInfo($where);
        if(!empty($res)){
            $a = [
                'errorcode'=>2,
                'msg'=>'不要贪心哦，只能投票一次哦'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        //查询是否已经点过反对,点过删除该条
        //重新组合查询条件
        $where = [
            'uid'=>$uid,
            'comment_id'=>$cid,
            'type'=>2
        ];
        $res = $comment->getCommentOperateInfo($where);
        if(!empty($res)){
            $res = $comment->delCommentOperateInfo($where);
            if($res){
                //设置操作状态
                $this->redis->set($key ,1 ,5);
                //修改评论表投票数量
                $comment->alterCommentVote('oppose', 2,['id'=>$cid]);
                $a = [
                    'errorcode'=>0,
                    'msg'=>'投票成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'投票失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }
        //查询结果为空则表示没有点赞操作，则插入一条新数据
        //组合插入数据
        $arr = [
            'uid'=>$uid,
            'comment_id'=>$cid,
            'create_time'=>time(),
            'type'=>2
        ];
        $res = $comment->addCommentOperate($arr);
        if($res){
            //设置操作状态
            $this->redis->set($key ,1 ,5);
            //修改评论表投票数量
            $comment->alterCommentVote('oppose', 1,['id'=>$cid]);
            $a = [
                'errorcode'=>0,
                'msg'=>'投票成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'投票失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //获取我的评论数据
    public function myComment(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        $type = input('type');
        if(!isset($type) || !is_numeric($type) || empty($type)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';die;
        }

        //组合查询条件获取数据
        if($type == 1){
            $where = " uid = $uid and parent_id = 0 and status = 1";
        }else if($type == 2){
            $where = " uid = $uid and parent_id != 0 and status = 1";
        }else if($type == 3){
            $a = [
                'errorcode'=>1,
                'msg'=>'暂未开放哦'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        if($start != 0){
            $where .= " and id < $start ";
        }

        //获取评论列表
        $comment = new \app\miniapp\model\Comment();
        $comment_list = $comment->getCommentList($where , ' * ' ,15 ,' id desc ');
        //获取评论对应的文章信息
        $article = new \app\miniapp\model\Article();
        //获取评论用户信息
        $user = new User();
        foreach($comment_list as $key => $value){
            $comment_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
            $comment_list[$key]['article_info'] = $article->getArticleInfo(['id'=>$value['aid']] ,' id,title ');
            $user_info = $user->getUserInfo(['id'=>$value['uid']]);
            $user_info['face'] = "https://www.sucai.biz" .$user_info['face'];
            $comment_list[$key]['user_info'] = $user_info;
        }
        if(!empty($comment_list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$comment_list
            ];
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'已经到底啦'
            ];
        }

        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }
}