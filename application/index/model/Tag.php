<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/16
 * Time: 12:12
 * Description：Tag标签表控制器
 */
namespace app\index\model;
use think\Db;
use think\Model;

class Tag extends Common{
    private $table_name = 'tag';
    private $taglist_table_name = 'tag_list';
    public $table = 'tag';
    public function __construct()
    {
        parent::__construct();
    }

    public function incrTag($where = [] ,$incr = 'count'){
        $res = Db::name($this->table_name)->where($where)->setInc($incr);
    }

    //获取tag标签列表

    /**
     * @param array $where
     * @param string $filed
     * @param int $limit
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTagList($where = [] ,$filed = ' * ' ,$limit = 1000 ,$order='id desc'){
        $res = Db::name($this->table_name)->field($filed)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //判断标签id是否存在

    /**
     * @param string $id
     * @return bool
     */
    public function checkTagId($id = ''){
        //判断是否输入文档id,没有输入返回不存在
        if(empty($id)){
            return false;
        }

        //到数据库中查询条数
        $res = Db::name($this->table_name)->where(['id'=>$id])->count('id');
        if($res == 0){
            return false;
        }else{
            return true;
        }
    }

    //获取栏目详细信息

    /**
     * @param array $where
     * @param string $field
     * @return array|bool|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTagInfo($where = [] , $field = ' * '){
        if(empty($where)){
            return false;
        }
        $res = Db::name($this->table_name)->field($field)->where($where)->find();
        if($res){
            return $res;
        }else{
            return false;
        }
    }

    //获取tag文档对应表数据

    /**
     * @param array $where
     * @param string $filed
     * @param int $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getTagArticleList($where = [] ,$filed = ' * ' ,$limit = 1000){
        $res = Db::name($this->taglist_table_name)->field($filed)->where($where)->select();
        return $res;
    }

    //获取tag文档中间表总数

    /**
     * @param array $where
     * @return int|string
     */
    public function getTagArticleCount($where = []){
        $res = Db::name($this->taglist_table_name)->where($where)->count('id');
        return $res;
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
        $key = $this->table_name .$this->getW($where) .$filed .$limit .$order .'tag_list';
        $res = $this->getRedis($key);
        if(empty($res)){
            //连表查询获取数据
            $res = Db::name($this->taglist_table_name)
                ->alias('l')
                ->join(' tag t ' ,' l.tag_id = t.id ' ,' left ')
                ->field($filed)
                ->where($where)
                ->limit($limit)
                ->order($order)
                ->select();
            $this->setRedis($key ,$res);
        }

        return $res;


    }
}