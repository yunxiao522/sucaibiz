<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/9/6
 * Time: 20:11
 * Description：Tag数据库模型
 */

namespace app\common\model;


use SucaiZ\Cache\Mysql;
use think\Db;
use think\Model;

class Tag extends Base
{
    private $tag_table_name = 'tag';
    public $table = 'tag';
    private $taglist_table_name = 'tag_list';
    public function __construct()
    {
        parent::__construct();
    }
    //获取tag标签信息

    /**
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTagInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        if(isset($where['id'])){
            return Mysql::find($this->tag_table_name ,'id' ,$where['id']);
        }else{
            return Db::name($this->tag_table_name)->field($field)->where($where)->find();
        }
    }
    //获取tag标签列表
    /**
     * @param array $where 查询条件
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTagList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->taglist_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
}