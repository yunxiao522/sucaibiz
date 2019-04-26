<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/24
 * Time: 21:06
 */

namespace app\admin\model;


use think\Model;
use think\Db;

class Comment extends Model
{
    public $table_name = 'comment';
    public $comment_key_table_name = 'comment_key';
    public $user_table_name;
    public $article_table_name;

    public function __construct()
    {
        parent::__construct();
        $user = new Member();
        $this->user_table_name = $user->user_table_name;
        $article = new Article();
        $this->article_table_name = $article->table_name;
    }

    //获取评论列表
    public function getCommentList($where = [], $field = ' * ', $limit = 100, $order = 'id desc')
    {
        $res = Db::name($this->table_name)
            ->alias('c')
            ->field($field)
            ->join("$this->user_table_name u ", " u.id = c.uid ", 'left')
            ->join("$this->article_table_name a ", " a.id = c.aid ", 'left')
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->select();
        return $res;
    }

    //获取评论表总数
    public function getCommentCount($where = [])
    {
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }
    //修改评论信息
    public function alterCommentInfo($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->update($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //删除评论方法
    public function delCommentInfo($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取评论关键词列表
    public function getCommentKeyList($where = [], $field = ' * ', $limit = 1000, $order = 'id desc')
    {
        $res = Db::name($this->comment_key_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取评论关键词总条数
    public function getCommentKeyCount($where = [])
    {
        $res = Db::name($this->comment_key_table_name)->where($where)->count('id');
        return $res;
    }
    //删除评论关键词
    public function delCommentKey($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->comment_key_table_name)->where($where)->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
    //获取评论关键词详细信息
    public function getCommentKeyInfo($where = [], $field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->comment_key_table_name)->where($where)->field($field)->find();
        return $res;
    }
    //修改评论关键词信息
    public function alterCommentKeyInfo($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        $res = Db::name($this->comment_key_table_name)->where($where)->update($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //新增评论关键词信息
    public function addCommentKeyInfo($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->comment_key_table_name)->insert($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }
}