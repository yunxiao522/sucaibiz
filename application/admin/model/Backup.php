<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/9
 * Time: 16:43
 */

namespace app\admin\model;
use think\Db;

class Backup extends Common
{
    private $table_name = 'backup';
    public function __construct()
    {
        parent::__construct();
    }

    //获取备份列表数据
    public function getBackUpList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $res = Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取备份列表总条数
    public function getBackUpCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //删除备份方法
    public function delBackUp($where = []){
        return $this->deleteData($where ,'file_name' ,'backup' ,$this->table_name);
    }

    //获取备份详细信息
    public function getBackUpInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->table_name)->where($where)->field($field)->find();
        return $res;
    }
}