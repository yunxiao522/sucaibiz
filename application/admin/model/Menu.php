<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/28
 * Time: 21:06
 * Description:
 */


namespace app\admin\model;
use think\Db;
use think\Model;

class Menu extends Model
{
    private $type_table = 'menu_type';
    private $menu_table = 'menu';
    public function __construct()
    {
        parent::__construct();
    }

    //添加菜单分类
    public function addClass($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->type_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取菜单分类列表数据
    public function getClassList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        return Db::name($this->type_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //添加菜单数据
    public function addMenu($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->menu_table)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取菜单列表
    public function getMenuList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        return Db::name($this->menu_table)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取菜单总条数
    public function getMenuCount($where = []){
        return Db::name($this->menu_table)->where($where)->count('id');
    }
    //删除菜单方法
    public function delMenuInfo($where = []){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->menu_table)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //获取菜单详细信息
    public function getMenuInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->menu_table)->field($field)->where($where)->find();
    }
    //修改菜单信息方法
    public function alterMenuInfo($where = [] ,$arr = []){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->menu_table)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}