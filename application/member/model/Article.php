<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/5
 * Time: 18:44
 */

namespace app\member\model;
use app\common\model\Base;
use think\Model;
use think\Db;
use SucaiZ\Cache\Mysql;

class Article extends Base
{
    private $table_name = 'article';
    private $extend_table_name = [1=>'article_body' ,2=>'article_images' ,4=>'article_resource'];
    public $table = 'article';
    public function __construct()
    {
        parent::__construct();
    }
    //获取文档 详细信息
    public function getArticleInfo($where = [] ,$field = '  * '){
        if (empty($where)) {
            return [];
        }
        if (isset($where['id'])) {
            return Mysql::find($this->table_name, 'id', $where['id']);
        } else {
            $res = Db::name($this->table_name)->where($where)->field(' * ')->find();
            return $res;
        }
    }
    //获取文档列表数据
    public function getArticleList($where = [] ,$field = ' * ' ,$limit = 10 ,$order = ' id desc '){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取文档总条数
    public function getCount($where = [] ,$field = 'id'){
        return Db::name($this->table_name)->where($where)->count($field);
    }
    //添加文档基本信息
    public function addArticleInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        return Db::name($this->table_name)->insertGetId($arr);
    }
    //添加扩展表信息
    public function addArticleExtendInfo($arr = [] ,$channel = ''){
        if(empty($arr) || empty($channel)){
            return false;
        }
        $res = Db::name($this->extend_table_name[$channel])->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}