<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/24
 * Time: 16:39
 */

namespace app\member\controller;


use SucaiZ\config;
use SucaiZ\Site;
use think\Db;
use think\Request;

class Comment extends Common
{
    private $redis;
    private $tier_key = 'article_tier_num';
    private $tier_key_ttl = 604800;
    private $user_comment_opinion_status = [1=>'点赞',2=>'反对',3=>'举报'];
    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
    }

    public function push(){
        //获取验证前台数据
        $data['aid'] = input('aid');
        if(empty($data['aid']) || !is_numeric($data['aid'])){
            return $this->ajaxError('参数错误');
        }
        $data['parent_id'] = input('pid');
        if(!is_numeric($data['parent_id'])){
            return $this->ajaxError('参数错误');
        }
        if(empty($data['parent_id'])){
            $data['parent_id'] = 0;
        }
        $data['content'] = input('content');
        if(empty($data['content'])){
            return $this->ajaxError('评论内容不能为空');
        }
        $data['ppid'] = input('ppid');
        if(empty($data['ppid']) || !is_numeric($data['ppid'])){
            $data['ppid'] =  0;
        }
        $device = input('device') == ''?$this->getDevice():'网页';
        //获取评论过滤的关键字
        $comment_key = $this->redis->get(config::get('cfg_member_comment_key'));
        $comment_key_arr = json_decode($comment_key,true);
        foreach($comment_key_arr as $value){
            if(strpos($data['content'],$value)){
                dump($value);
                return $this->ajaxError('发表失败');
            }
        }
        $data['uid'] = $this->member_info['id'];
        $data['face'] = $this->member_info['face'];
        $data['praiser'] =  0;
        $data['oppose'] = 0;
        $data['inform'] = 0;
        $data['status'] = 1;
        $data['create_time'] = time();
        $data['comment_ip'] = Request::instance()->ip();
        $site = new Site();
        $data['city'] = $site->getIpInfo($data['comment_ip']);
        $data['tier'] = $this->getCommentTier($data['aid'],$data['ppid']);
        $data['device'] = $device;
        //组合数据写入数据库
        $res = Model('comment')->add($data);
        if($res){
            $this->incrCommentTier($data['aid'],$data['ppid']);
            Model('article')->fieldinc(['id'=>$data['aid']],'comment_num');
            return $this->ajaxOk('发表成功');
        }else{
            return $this->ajaxError('发表失败');
        }
    }

    /**
     * @param $aid 文档id
     * @param $pid 评论的父级id
     * @return bool|string
     * Description 获取评论的楼层
     */
    private function getCommentTier($aid,$pid){
        $tier_key =  str_replace(['tier','num'],[$aid,$pid],$this->tier_key);
        $tier = $this->redis->get($tier_key);
        if(empty($tier)){
            //组合查询条件获取评论楼层数
            $tier = Model('comment')->getField([
                'aid'=>$aid,
                'parent_id'=>$pid
            ],'tier',' tier desc ');
        }
        $tier ++;
        return $tier;
    }

    /**
     * @param $aid
     * @param $pid
     * Description 增加评论楼层数
     */
    private function incrCommentTier($aid,$pid){
        $tier_key =  str_replace(['tier','num'],[$aid,$pid],$this->tier_key);

        $this->redis->incr($tier_key);
    }

    /**
     * @return false|string
     * Description 评论举报方法
     */
    public function inform(){
        //获取验证前台数据
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return $this->ajaxError('参数错误');
        }
        //检查评论是否存在
        $comment_info = Model('comment')->getOne(['id'=>$id],'id,aid,inform');
        if(empty($comment_info)){
            return $this->ajaxError('参数错误');
        }
        //判断用户是否已经投票过
        $opinion_id = $this->checkCommentOption($comment_info['id']);
        if(!empty($opinion_id)){
            return $this->ajaxError('您已经举报过啦...');
        }
        //开启数据库事务
        Db::startTrans();
        $res = Model('UserOpinion')->add([
            'aid'=>$comment_info['aid'],
            'cid'=>$comment_info['id'],
            'uid'=>$this->member_info['id'],
            'create_time'=>time(),
            'status'=>3
        ]);
        Model('comment')->fieldinc(['id'=>$comment_info['id']],'inform');
        if($res){
            Db::commit();
            return $this->ajaxOk('举报成功');
        }else{
            Db::rollback();
            return $this->ajaxError('举报失败');
        }
    }

    /**
     * @return false|string
     * Description 评论支持操作
     */
    public function praiser(){
        //获取前台验证数据
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return $this->ajaxError('参数错误');
        }
        //检查评论是否存在
        $comment_info = Model('comment')->getOne(['id'=>$id],'id,aid,praiser');
        if(empty($comment_info)){
            return $this->ajaxError('参数错误');
        }
        //判断用户是否已经有过反对操作
        $opinion_id = $this->checkCommentOption($comment_info['id'],2);
        if(!empty($opinion_id)){
            return $this->ajaxError('您已经投过票啦...');
        }
        //判断是否有过支持操作
        $opinion_id = $this->checkCommentOption($comment_info['id'],1);
        //开启数据库事务
        Db::startTrans();
        if(!empty($opinion_id)){
            Model('comment')->fielddec(['id'=>$comment_info['id']],'praiser');
            $comment_info['praiser'] --;
            $res = Model('UserOpinion')->del([
                'id'=>$opinion_id
            ]);
        }else{
            $comment_info['praiser'] ++;
            Model('comment')->fieldinc(['id'=>$comment_info['id']],'praiser');
            $res = Model('UserOpinion')->add([
                'cid'=>$comment_info['id'],
                'aid'=>$comment_info['aid'],
                'uid'=>$this->member_info['id'],
                'create_time'=>time(),
                'status'=>1
            ]);
        }
        if($res){
            Db::commit();
            return $this->ajaxOkdata(['num'=>$comment_info['praiser']],'投票成功');
        }else{
            Db::rollback();
            return $this->ajaxError('投票失败');
        }
    }

    /**
     * @return false|string
     * Description 评论反对操作
     */
    public function oppose(){
        //获取验证前台数据
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return $this->ajaxError('参数错误');
        }
        //获取评论数据
        $comment_info = Model('comment')->getOne(['id'=>$id],'id,aid,oppose');
        if(empty($comment_info)){
            return $this->ajaxError('参数错误');
        }
        //判断用户是否有过支持操作
        $opinion_id = $this->checkCommentOption($comment_info['id'],1);
        if(!empty($opinion_id)){
            return $this->ajaxError('您已经投过票啦...');
        }
        //判断是否有过反对操作
        $opinion_id = $this->checkCommentOption($comment_info['id'],2);
        //开启数据库事务
        Db::startTrans();
        if(!empty($opinion_id)){
            Model('comment')->fielddec(['id'=>$comment_info['id']],'oppose');
            $comment_info['oppose'] --;
            $res = Model('UserOpinion')->del([
                'id'=>$opinion_id
            ]);
        }else{
            Model('comment')->fieldinc(['id'=>$comment_info['id']],'oppose');
            $comment_info['oppose'] ++;
            $res = Model('UserOpinion')->add([
                'cid'=>$comment_info['id'],
                'aid'=>$comment_info['aid'],
                'uid'=>$this->member_info['id'],
                'create_time'=>time(),
                'status'=>2
            ]);
        }
        if($res){
            Db::commit();
            return $this->ajaxOkdata(['num'=>$comment_info['oppose']],'投票成功');
        }else{
            Db::rollback();
            return $this->ajaxError('投票失败');
        }
    }

    /**
     * @param $comment_id 评论id
     * @param null $status 操作
     * @return bool 状态
     * Description 检查会员是否对某个评论操作过
     */
    private function checkCommentOption($comment_id,$status = null){
        //组合查询条件，查询用户是否有过评论操作
        $where = [
            'uid'=>$this->member_info['id'],
            'cid'=>$comment_id
        ];
        if(!empty($status) && isset($this->user_comment_opinion_status[$status])){
            $where['status'] = $status;
        }
        return Model('UserOpinion')->getField($where,'id');
    }

    private function getDevice(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if(strpos($agent, 'windows nt')){
            return '网页';
        }
        if(strpos($agent, 'iphone')){
            return 'iphone';
        }
        if(strpos($agent, 'ipad')){
            return 'ipad';
        }
        if(strpos($agent, 'android')){
            return 'android';
        }
        return '未知设备';
    }
}