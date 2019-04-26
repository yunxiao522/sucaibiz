<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/8 0008
 * Time: 15:21
 */

namespace app\index\controller;


class Like extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function collect(){
        if($this->uid == 0){
            return $this->ajaxError('请先登录','',2);
        }
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return $this->ajaxError('参数错误');
        }
        $p = input('p');
        if(empty($p)){
            $p = 0;
        }
        //组合数据查询该文档是否存在
        $where = [
            'id'=>$id
        ];
        $article_info = Model('article')->getField($where,'id');
        if(empty($article_info)){
            return $this->ajaxError('文档不存在');
        }
        //组合查询条件，判断是否已经收藏过该
        $where  = [
            'article_id'=>$id,
            'alone'=>$p,
            'type'=>1,
            'uid'=>$this->uid
        ];
        $collect_id = Model('mylike')->getField($where,'id');
        if(!empty($collect_id)){
            $res = Model('mylike')->del(['id'=>$collect_id]);
            if($res){
                return $this->ajaxOk('取消收藏成功','',1);
            }else{
                return $this->ajaxError('取消收藏失败');
            }
        }
        //查询文档所属栏目id
        $column_id = Model('article')->getField(['id'=>$id],'column_id');
        //组合数据插入数据库
        $data = [
            'uid'=>$this->uid,
            'article_id'=>$id,
            'type'=>1,
            'alone'=>$p,
            'create_time'=>time(),
            'class_id'=>1,
            'channel'=>$column_id
        ];
        $res = Model('mylike')->add($data);
        if($res){
            return $this->ajaxOk('添加收藏成功');
        }else{
            return $this->ajaxError('添加收藏失败');
        }
    }

    /**
     * @return false|string
     * Description 获取收藏状态
     */
    public function getLikeStatus(){
        if($this->uid == 0){
            return $this->ajaxOkdata(['status'=>false],'为收藏数据');
        }
        //获取验证前台数据
        $id = input('id');
        $p = input('p');
        if(empty($p)){
            $p = 0;
        }
        $type = input('type');
        if(empty($type)){
            $type = 1;
        }
        if(empty($id) || !is_numeric($id)){
            return $this->ajaxError('参数错误');
        }
        //组合查询条件，查询文档收藏状态
        $where = [
            'uid'=>$this->uid,
            'alone'=>$p,
            'type'=>$type,
            'article_id'=>$id
        ];
        $colect_id = Model('mylike')->getField($where,'id');
        if(empty($colect_id)){
            return $this->ajaxOkdata(['status'=>false]);
        }else{
            return $this->ajaxOkdata(['status'=>true]);
        }
    }
}