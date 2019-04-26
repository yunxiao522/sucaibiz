<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/26
 * Time: 17:35
 * Description：系统配置控制器
 */
namespace app\admin\model;
use think\Model;
use think\Db;
class Sysconfig extends Common{
    private $table_name = 'sysconfig';
    private $group_table_name = 'sysconfig_group';
    private $water_table_name = 'sysconfig_water';
    public $table = 'sysconfig';
    public function __construct()
    {
        parent::__construct();
    }

    //获取系统配置
    public function getSysconfig($field = ' * '){
        $res = Db::name($this->table_name)->field($field)->find();
        return $res;
    }

    //新增系统配置
    public function addSysconfig($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->insert($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取系统配置列表
    public function getSysconfigList($where = [], $field = ' * ', $limit = 1000, $order = 'id desc'){
        $res = Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取系统配置条数
    public function getSysconfigCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //删除系统配置信息
    public function delSysconfig($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->where($where)->delete();
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //获取系统配置详细信息
    public function getSysconfigInfo($where = [], $field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->table_name)->where($where)->field($field)->find();
        return $res;
    }

    //修改系统配置信息
    public function alterSysconfigInfo($where =[] ,$data = []){
        return $this->updateData($where ,$data ,'sysconfig' ,$this->table_name);
    }

    //获取系统分组
    public function getSysconfigGroup($where = [], $field = ' * ', $limit = 1000, $order = 'id asc')
    {
        $res = Db::name($this->group_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取系统分组条数
    public function getSysconfigGroupCpunt($where = []){
        if(empty($where)){
            return 0;
        }
        $res = Db::name($this->group_table_name)->where($where)->count('id');
        return $res;
    }

    //新增系统分组信息
    public function addSysconfigGroup($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->group_table_name)->insert($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //修改系统分组信息
    public function alterSysconfigGroup($where =[] ,$data = []){
        return $this->updateData($where ,$data ,'sysconfig_group' ,$this->group_table_name);
    }

    //获取分组详细信息
    public function getSysconfigGroupInfo($where = [], $field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->group_table_name)->where($where)->field($field)->find();
        return $res;
    }

    //删除分组信息
    public function delSysconfigGroup($where = []){
        if(empty($where)){
            return false;
        }
        return $this->deleteData($where , 'name', 'sysconfig_group',$this->group_table_name);
    }

    //获取水印信息
    public function getWaterInfo(){
        $where = ['id'=>1];
        $res = Db::name($this->water_table_name)->where($where)->find();
        return $res;
    }

    //修改水印信息
    public function alterWaterInfo($where = [] ,$data = []){
        if(empty($where) || empty($data)){
            return false;
        }
        return $this->updateData($where ,$data ,'water' ,$this->water_table_name);

    }
}