<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/15
 * Time: 10:08
 * Description:
 */


namespace app\miniapp\model;


use think\Model;
use think\Db;

class Article extends Model
{
    public $table_name = 'article';
    public $affiliate_table_name = [1 => 'article_body', 2 => 'article_images', 3 => 'article_album', 4 => 'article_resource'];

    public function __construct()
    {
        parent::__construct();
    }

    //获取文档全部信息
    public function getArticleInfoAll($where = [], $channel = '')
    {
        //判断验证数据
        if (empty($where) || empty($channel)) {
            return [];
        }
        $affiliate_table_name = $this->affiliate_table_name[$channel];
        //查询数据
        $res = Db::name($this->table_name)
            ->alias(' a ')
            ->join(" $affiliate_table_name f ", ' f.article_id = a.id ', ' left ')
            ->field(' * ')
            ->where($where)
            ->find();

        return $res;
    }

    //查询文档列表
    public function getArticleList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id asc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }

    //获取文档基础信息
    public function getArticleInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->table_name)->field($field)->where($where)->find();
    }
}