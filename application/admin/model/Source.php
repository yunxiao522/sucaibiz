<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/15
 * Time: 9:35
 * Description：文章来源数据库模型
 */
namespace app\admin\model;
use think\Model;
use think\Db;
class Source extends Model{
    private $table_name = 'article_source';
    public function __construct()
    {
        parent::__construct();
    }

    //获取文章来源列表方法
    public function getArticleSourceList(){
        $res = Db::name($this->table_name)->select();
        return $res;
    }

    //设置文档来源方法
    public function setArticleSource($arr = []){
        if(empty($arr)){
            return false;
        }
        Db::name($this->table_name)->where('1 = 1')->delete();
        $res = Db::name($this->table_name)->insertAll($arr);
        return $res;
    }
}