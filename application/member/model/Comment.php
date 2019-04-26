<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/26
 * Time: 11:39
 */

namespace app\member\model;


use app\common\model\Base;
use think\Db;

class Comment extends Base
{
    private $comment_table_name = 'comment';
    private $comment_key_table_name = 'comment_key';
    private $comment_opinion_table_name = 'comment_opinion';
    public $table = 'comment';

    public function __construct()
    {
        parent::__construct();

    }

    public function setAffiliate($table = ''){
        if (!empty($table)) {
            $this->table_name = $this->comment_table_arr[$table];
        }
    }

    //获取评论关键词列表
    public function getCommentKeyList($where = [], $field = ' * ', $limit = 1000, $order = 'id desc')
    {
        $res = Db::name($this->comment_key_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //新增评论方法
    public function addCommentInfo($data = [])
    {
        if (empty($data)) {
            return false;
        }
        $res = Db::name($this->comment_table_name)->insertGetId($data);
        if ($res !== false) {
            return $res;
        } else {
            return false;
        }
    }

    //获取评论数量
    public function getCommentCount($where = [])
    {
        $res = Db::name($this->comment_table_name)->where($where)->count('id');
        return $res;
    }

    //增加附属表信息
    public function addCommentOpinion($data = [])
    {
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->comment_opinion_table_name)->insert($data);
        if ($res !== false) {
            return $res;
        } else {
            return false;
        }
    }

    //查询附属表信息
    public function getCommentOpinionCount($where = []){
        if(empty($where)){
            return 0;
        }
        $res = Db::name($this->comment_opinion_table_name)->where($where)->count('id');
        return $res;
    }

    //更新附属表信息
    public function updateCommentOpinion($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        $res = Db::name($this->comment_opinion_table_name)->where($where)->update($data);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}