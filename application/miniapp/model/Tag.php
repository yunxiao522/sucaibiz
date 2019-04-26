<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/25
 * Time: 20:32
 * Description:
 */


namespace app\miniapp\model;
use think\Db;
use think\Model;

class Tag extends Model
{
    //tag列表
    private $tag_index = 'tag';
    //tag文档中间表
    private $tag_list = 'tag_list';
    //小程序tag表
    private $miniapp_tag_table_name = 'miniapp_tag';

    public function __construct()
    {
        parent::__construct();
    }
    //获取tag列表
    public function getTagList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->tag_index)->field($field)->where($where)->limit($limit)->order($order)->select();
    }
    //获取tag文档中间表文档列表
    public function getTagListInfo($where = [] ,$filed = ' * ' ,$limit = 1000 ,$order = ' l.id desc '){
        //验证数据
        if(empty($where)){
            return [];
        }

        //连表查询获取数据
        $res = Db::name($this->tag_list)
            ->alias('l')
            ->join(' article a ' ,' l.article_id = a.id ' ,' left ')
            ->field($filed)
            ->where($where)
            ->limit($limit)
            ->order($order)
            ->select();
        return $res;
    }
    //获取tag文档中间表列表
    public function getTagLList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->tag_list)->field($field)->where($where)->limit($limit)->order($order)->select();
    }

    //获取小程序tag列表
    public function getMiniAppTagList($where = [] ,$field = ' * ' ,$limit = 100 ,$order = ' id desc '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->miniapp_tag_table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
    }

}