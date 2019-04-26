<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/16
 * Time: 11:52
 * Description：tag标签表数据库模型
 */
namespace app\admin\model;
use think\Db;
class Tag extends Common{

    //tag表 表名
    private $table_name = 'tag';
    private $taglist_table_name = 'tag_list';
    public $table = 'tag';
    public $tag = 'tag';

    public function __construct()
    {
        parent::__construct();

    }

    //获取tag标签列表
    public function getTagList($where = [] , $field = ' * ' , $limit = 1000 , $order = 'id desc'){
        $res = Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取tag标签总条数
    public function getTagCount($where = []){
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //新增tag表数据
    public function insertTag($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->table_name)->insertGetId($data);
        if($res !== false){
            return $res;
        }else{
            return false;
        }
    }

    //获取单条tag内容
    public function getTagInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        $res = Db::name($this->table_name)->where($where)->field($field)->find();
        return $res;
    }

    //新增tag_list表数据
    public function insertTagList($data = []){
        if(empty($data)){
            return false;
        }
        $res = Db::name($this->taglist_table_name)->insert($data);
        if($res !== false){
            return true;
        }else{
            return false;
        }
    }

    //更新tag表引用数量
    public function updateTotal($where = []){
        if(empty($where)){
            return false;
        }
        return Db::name($this->table_name)->where($where)->setInc('total' ,1);
    }
    //减少tag表引用的数据
    public function subTtotal($where = []){
        if(empty($where)){
            return false;
        }
        return Db::name($this->table_name)->where($where)->setDec('total' ,1);
    }

    //根据文档id查询tag信息

    /**
     * @param array $where
     * @param string $filed
     * @param int $limit
     * @param string $order
     * @return array|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTagListInfo($where = [] ,$filed = ' * ' ,$limit = 1000 ,$order = ' l.id desc '){
        //验证数据
        if(empty($where)){
            return [];
        }

        //连表查询获取数据
        $res = Db::name($this->taglist_table_name)
            ->alias('l')
            ->join(' tag t ' ,' l.tag_id = t.id ' ,' left ')
            ->field($filed)
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->select();
        return $res;
    }

    //修改tag标签方法
    public function alterTag($where = [] ,$tag_list = []){
        //验证数据
        if(empty($where) || empty($tag_list)){
            return false;
        }

        //
    }

    //删除tag_List表方法

    /**
     * @param array $where
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delTagList($where = []){
        //验证数据
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->taglist_table_name)->where($where)->delete();
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
}