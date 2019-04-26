<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/24
 * Time: 13:14
 * Descrition：评论控制器
 */

namespace app\index\controller;

use app\admin\model\Article;
use app\admin\model\User;
use SucaiZ\config;
use think\Model;
use think\Request;
use think\Db;

class Comment extends Common
{
    private $redis;
    private $get_comment_num;
    private $order;

    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
        $this->get_comment_num = config::get('cfg_get_comment_num');
        $this->order = 'create_time desc';
    }

    //获取文档评论
    public function getArticleComment()
    {
        $this->checkArticleHash();
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            echo '非法访问';
            die;
        }
        $aid = input('aid');

    }

    //获取评论过滤关键词列表
    public function getCommentKey()
    {
        $comment_key_json = $this->redis->get('Comment_key');
        if (empty($comment_key_json)) {
            $comment = new \app\member\model\Comment();
            $Comment_key_list = $comment->getCommentKeyList([], " content ", 100000, 'id Desc');
            $comment_key_arr = [];
            foreach ($Comment_key_list as $key => $value) {
                $comment_key_arr[] = $value['content'];
            }
            $comment_key_arr = array_unique($comment_key_arr);
            $this->redis->set('Comment_key', json_encode($comment_key_arr, JSON_UNESCAPED_UNICODE));
            return $comment_key_arr;
        } else {
            return json_decode($comment_key_json, true);
        }
    }

    //添加评论方法
    public function addComment()
    {
        //验证前台数据
        $token = input('token');
        $uid = input('uid');
        $content = input('content');
        $parent_id = input('parent_id');
        if (!isset($token)) {
            echo '非法访问';
            die;
        }
        if (!isset($uid) || !is_numeric($uid)) {
            echo '非法访问';
            die;
        }
        if (!isset($content)) {
            echo '非法访问';
            die;
        }
        if (!isset($parent_id) || !is_numeric($parent_id)) {
            echo '非法访问';
            die;
        }
        if ($content == '') {
            $a['errorcode'] = 1;
            $a['msg'] = '输入的评论内容不能为空';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        $tier_num = $this->getCommentTier($token, $parent_id);
        $article = new Article();
        $info = $article->getArticleInfo(['token' => $token], ' * ');
        $aid = $info['id'];
        $data = [
            'aid' => $aid,
            'uid' => $uid,
            'content' => $content,
            'tier' => $tier_num,
            'parent_id' => $parent_id,
            'praiser' => 0,
            'oppose' => 0,
            'inform' => 0,
            'status' => 1,
            'create_time' => time(),
            'comment_ip' =>$_SERVER['REMOTE_ADDR'],
            'city'=>$this->taobaoIP($_SERVER['REMOTE_ADDR'])
        ];

        $comment = new \app\member\model\Comment();
        //开启数据库事务
        $comment_id = $comment->addCommentInfo($data);
        //更改文档表评论条数
        $article->alterCommentNum(['token' => $token]);
        if ($comment_id) {
            $a['errorcode'] = 0;
            $a['msg'] = '发布成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '抱歉，文档评论发表失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //获取评论楼层数
    public function getCommentTier($token, $comment_id = 0)
    {
        $redis_key = $token . '#' . $comment_id;
        $tier_status = $this->redis->exists($redis_key);
        if ($tier_status) {
            $this->redis->incr($redis_key);
            $tier_num = $this->redis->get($redis_key);
            return $tier_num;
        } else {
            $where = [
                'aid' => $token,
                'parent_id' => $comment_id
            ];
            $comment = new \app\member\model\Comment();
            $num = $comment->getCommentCount($where);
            $tier_num = $num + 1;
            $this->redis->set($redis_key, $tier_num, 604800);
            return $tier_num;
        }
    }

    //处理评论附属
    public function postComment()
    {
        //验证数据访问
        $aid = input('aid');
        if (!isset($aid) || !is_numeric($aid)) {
            echo '非法访问';
            die;
        }
        $comment_id = input('comment_id');
        if (!isset($comment_id) || !is_numeric($comment_id)) {
            echo '非法访问';
            die;
        }
        $type = input('type');
        if (!isset($type)) {
            echo '非法访问';
            die;
        }
        $comment = new \app\member\model\Comment();
        $type_id = input('type_id');
        if (isset($type_id) && is_numeric($type_id)) {
            if ($type_id == 1) {
                if ($type == 'CommentReplyVote') {
                    $b = [
                        'status' => 1
                    ];
                } else if ($type == 'CommentCancleVote') {
                    $b = [
                        'status' => 2
                    ];
                }
            } else if ($type_id == 2) {
                $b = [
                    'status' => 0,
                    'alter_time' => time()
                ];
            }
            $where = [
                'aid' => $aid,
                'cid' => $comment_id,
                'uid' => $this->member_info['id']
            ];
            $opinion_count = $comment->getCommentOpinionCount($where);
            if ($opinion_count == 0) {
                $b['aid'] = $aid;
                $b['cid'] = $comment_id;
                $b['uid'] = $this->member_info['id'];
                $res = $comment->addCommentOpinion($b);
            } else if ($opinion_count == 1) {
                $b['alter_time'] = time();
                $res = $comment->updateCommentOpinion($where, $b);
            }
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = 'success';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = 'fail';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        }

        if ($type == 'CommentOppose') {

        }

    }

    //获取评论附属信息
    public function getCommentAffiliterInfo($cid = '', $aid = '')
    {
        $c = [];
        $where = [
            'aid' => $aid,
            'cid' => $cid,
        ];
        $where['status'] = 1;
        $comment = new \app\member\model\Comment();
        $c['prasier']['num'] = $comment->getCommentOpinionCount($where);
        $where['status'] = 2;
        $c['oppose']['num'] = $comment->getCommentOpinionCount($where);
        if (isset($this->member_info['id'])) {
            $where['uid'] = $this->member_info['id'];
        } else {
            $where['uid'] = 0;
        }
        $b = $comment->getCommentOpinionCount($where);
        if ($b == 1) {
            $c['oppose']['this'] = true;
        } else {
            $c['oppose']['this'] = false;
        }
        $where['status'] = 1;
        $d = $comment->getCommentOpinionCount($where);
        if ($d == 1) {
            $c['prasier']['this'] = true;
        } else {
            $c['prasier']['this'] = false;
        }
        return $c;
    }

    //验证文档正确性
    public function checkArticleHash()
    {
        $hash = input('hash');
        $aid = input('aid');
        if (isset($hash) && isset($aid)) {
            if ($hash != md5($aid)) {
                echo '非法访问';
                die;
            }
        } else {
            echo '非法访问';
            die;
        }
    }

    //获取评论列表
    public function getComment()
    {
        //判断请求类型
        $type = input('type');
        if(!isset($type)){
            echo '非法访问';die;
        }
        if($type == 'comment'){
            //验证数据
            $token = input('token');
            if (!isset($token)) {
                echo '非法访问';
                die;
            }
            $page = input('page');
            if (!isset($page)) {
                echo '非法访问';
                die;
            }
            $limit = input('limit');
            if (!isset($limit)) {
                echo '非法访问';
                die;
            }
            $order = input('order');
            if (!isset($order)) {
                echo '非法访问';
                die;
            }
            //获取数据
            $id = $this->getArticleId($token);
            if (!$id) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '参数错误'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            //组合数据获取评论列表
            $limits = ($page - 1) * $limit . ',' . $limit;
            if ($order == 'true') {
                $order = ' c.id desc ';
            } else {
                $order = ' c.id asc ';
            }
            $where = ['aid' => $id, 'c.status' => 1];
            $comment = new \app\admin\model\Comment();
            $list = $comment->getCommentList($where, ' * ', $limits, $order);
            //循环处理数据
            $data = '';
            foreach ($list as $key => $value) {
                $data .= '<div class="list">
                <div class="left">
                    <div><a href="/member/index.html?id='.$value['uid'].'"><img src="'.$value['face'].'" alt="" ></a></div>
                    <div class="lv">Lv.'.$value['level'].'</div>
                </div>
                <div class="right">
                    <div class="comment_info">
                        <div class="nickname">'.$value['nickname'].'</div>
                        <div class="time">'.date('Y-m-d H:i:s', $value['create_time']).'</div>
                        <div class="tier">'.$value['tier'].'楼</div>
                    </div>
                    <div class="comment_content">'.$value['content'].'</div>
                    <div class="operate">
                        <div class="unnfold">展开(0)</div>
                        <div class="idea">
                            <div class="report">举报</div>
                            <div class="support">支持('.$value['praiser'].')</div>
                            <div class="oppose">反对('.$value['oppose'].')</div>
                            <div class="reply">回复</div>
                        </div>
                    </div>
                </div>
            </div>';
            }
            return $data;
        }else if($type == 'hotcomment'){

        }else if($type == 'parentcomment'){

        }


    }

    //获取文档id
    private function getArticleId($token)
    {
        $article = new Article();
        $where = ['token' => $token, 'is_delete' => 1];
        $info = $article->getArticleInfo($where, ' id ');
        if (empty($info)) {
            return false;
        } else {
            return $info['id'];
        }
    }

    //获取客户端ip所在城市
    private function taobaoIP($clientIP){
        $taobaoIP = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$clientIP;
        $IPinfo = json_decode(file_get_contents($taobaoIP));
        $province = $IPinfo->data->region;
        $city = $IPinfo->data->city;
        $data = $province.$city;
        return $data;
    }

    //获取评论列表数据
    public function getList(){
        //验证获取前台数据
        $aid = input('aid');
        if(empty($aid) || !is_numeric($aid)){
            return $this->ajaxError('参数错误');
        }
        //判断文档是否存在
        $article_id = Model('article')->getField(['id'=>$aid],'id');
        if(empty($article_id)){
            return $this->ajaxError('参数错误');
        }
        $parent_id = input('pid');
        if(empty($parent_id) || !is_numeric($parent_id)){
            $parent_id = 0;
        }
        if($parent_id != 0){
            $this->get_comment_num = 10000;
        }
        //获取排序信息
        $order = input('order');
        if(empty($order) || $order == 1){
            $this->order = 'create_time desc';
        }else{
            $this->order = 'create_time asc';
        }
        //判断用户是否登录,等着则获取用户对文档内所有的评论反馈状态.减少后续对数据库的查询
        $user_back_feed = [];
        if($this->uid != 0){
            $back_feed = Model('UserOpinion')->getAll([
                'aid'=>$aid,
                'uid'=>$this->uid
            ],'cid,status',10000);
            $user_back_feed = array_column($back_feed,'status','cid');
        }
        $list = $this->getCommentList($aid,$parent_id,$user_back_feed);
        //重置查询排序方法
        $this->order = 'create_time asc';
        //循环列表数据
        foreach($list['data'] as $key => $value){
            $list['data'][$key]['son_list'] = [
                'count'=>0
            ];
            if($parent_id == 0){
                $this->get_comment_num = 5;
                $list['data'][$key]['son_list'] = $this->getCommentList($aid,$value['id'],$user_back_feed);
            }
        }
        return $this->ajaxOkdata($list,'get data successed');
    }

    /**
     * @param $aid 文档id
     * @param $pid 评论的二级父级id
     * @param $user_back_feed 用户的评论操作列表数据
     * @return mixed
     * Description 获取评论列表数据
     */
    private function getCommentList($aid,$pid,$user_back_feed)
    {
        //组合查询条件，查询评论列表数据
        $where = [
            'aid' => $aid,
            'ppid' => $pid,
            'inform' => [
                '<',
                5
            ],
            'status' => 1
        ];
        Model('comment')->limit = $this->get_comment_num;
        $list = Model('comment')->getList($where, 'id,face,uid,content,tier,parent_id,praiser,oppose,create_time,city', $this->order);
        //循环列表数据
        foreach ($list['data'] as $key => $value) {
            //初始化评论反馈数据
            $list['data'][$key]['praiser_status'] = false;
            $list['data'][$key]['oppose_status'] = false;
            if (isset($user_back_feed[$value['id']])) {
                if ($user_back_feed[$value['id']] == 1) {
                    $list['data'][$key]['praiser_status'] = true;
                } else {
                    $list['data'][$key]['oppose_status'] = true;
                }
            }
            $list['data'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            //查询用户信息
            $list['data'][$key]['user_info'] = Model('user')->getOne(['id'=>$value['uid']],'nickname,level');
        }
        return $list;
    }
}