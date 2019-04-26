<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/6
 * Time: 14:45
 */

namespace app\common\model;
use SucaiZ\Cache\Mysql;
use think\Db;
use think\Model;

class Article extends Base
{
    private $table_name = 'article';
    public $table = 'article';
    public function __construct()
    {
        parent::__construct();
    }
    //获取文档信息
    /**
     * @param array $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     */
    public function getArticleInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        if(isset($where['id'])){
            return Mysql::find($this->table_name ,'id' ,$where['id']);
        }else{
            return Db::name($this->table_name)->field($field)->where($where)->find();
        }
    }
    //随机获取n条文档数据
    public function getRandeArticleList($where = [] ,$field = ' * ' ,$limit = 10){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order(rand())->select();
    }
    //获取文档列表
    /**
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param int $limit 查询条数
     * @param string $order 排序依据
     * @return array 文档列表数据
     */
    public function getArticleList($where = [] ,$field = ' * ' ,$limit = 10 ,$order = ' id desc '){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
}