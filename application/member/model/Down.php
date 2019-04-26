<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/9/11
 * Time: 19:59
 * Description：我的下载数据库模型
 */

namespace app\member\model;


use think\Db;
use think\Model;

class Down extends Model
{
    private $table_name = 'my_down';
    public function __construct()
    {
        parent::__construct();
    }
    //获取我的下载总条数

    /**
     * @param array $where 查询规则
     * @param string $field 分类字段
     * @return int 返回符合条件的条数
     */
    public function getDownCount($where = [] ,$field = 'uid'){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->table_name)->where($where)->count($field);
    }
    //获取我的下载列表

    /**
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param int $limit 查询条数
     * @param string $order 排序规则
     * @return array 返回我的下载列表
     */
    public function getDownList($where = [] ,$field = ' * ' ,$limit = 10 ,$order = ' create_time desc '){
        return Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
}