<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/19
 * Time: 16:53
 * Description: 评论表模型
 */


namespace app\miniapp\model;
use think\Model;
use think\Db;

class Comment extends Model
{
    //评论表名称
    private $comment_table = 'comment';
    //评论操作表表名
    private $comment_operate_table = 'comment_operate';
    public function __construct()
    {
        parent::__construct();
    }

    //获取评论列表
    public function getCommentList($where = [] ,$field = ' * ' ,$limit=100 ,$order = ' create_time asc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->comment_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //修改评论表投票数
    public function alterCommentVote($field = 'praiser' ,$type = 1 ,$where = []){
        if(empty($where)){
            return false;
        }
        if($type == 1){
            return Db::name($this->comment_table)->where($where)->setInc($field);
        }else if($type == 2){
            return Db::name($this->comment_table)->where($where)->setDec($field);
        }
    }

    //获取评论操作信息
    public function getCommentOperateInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->comment_operate_table)->field($field)->where($where)->find();
    }

    //删除评论操作信息
    public function delCommentOperateInfo($where){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->comment_operate_table)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //新增一条评论操作
    public function addCommentOperate($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->comment_operate_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取评论总条数
    public function getCommentCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->comment_table)->where($where)->count('id');
    }
}