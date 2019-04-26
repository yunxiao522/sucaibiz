<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/15
 * Time: 9:29
 * Description：文章作者数据库模型
 */
namespace app\admin\model;
use think\Db;
use think\Model;
class Author extends Model{
    private $table_name = 'Article_author';
    public function __construct()
    {
        parent::__construct();
    }

    //获取文章作者列表
    public function getArticleAuthorList(){
        $res = Db::name($this->table_name)->select();
        return $res;
    }

    //设置文章作者方法
    public function setArticleAuthor($arr = []){
        if(empty($arr)){
            return false;
        }
        Db::name($this->table_name)->where('1 = 1')->delete();
        $res = Db::name($this->table_name)->insertAll($arr);
        return $res;
    }
}