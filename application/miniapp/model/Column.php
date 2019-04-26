<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/14
 * Time: 12:42
 * Description:
 */


namespace app\miniapp\model;

use SucaiZ\Cache\Mysql;
use think\Db;
use think\Model;

class Column extends Model
{
    //栏目数据库表名
    private $column_table = 'column';
    private $miniapp_column_table = 'miniapp_column';

    public function __construct()
    {
        parent::__construct();
    }

    //获取栏目列表
    public function getColumnList($where = [], $field = ' * ', $limit = 100, $order = ' id asc ')
    {
        return Db::name($this->miniapp_column_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }

    //获取栏目信息
    public function getColumnInfo($where = [], $field = ' * ')
    {
        if (empty($where)) {
            return [];
        }
        if (isset($where['id'])) {
            return Mysql::find($this->column_table, 'id', $where['id']);
        } else {
            return Db::name($this->column_table)->field($field)->where($where)->find();
        }

    }
}