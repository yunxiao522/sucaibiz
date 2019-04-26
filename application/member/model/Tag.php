<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/26
 * Time: 20:34
 */

namespace app\member\model;
use think\Db;
use think\Model;

class Tag extends Model
{
    private $tag_table_name = 'tag';
    private $tag_list_table_name = 'tag_list';
    public function __construct()
    {
        parent::__construct();
    }
    //写入tag表
    private function addTagInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        return Db::name($this->tag_table_name)->insertGetId($arr);
    }
    //写入Tag_list表
    private function addTagListInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->tag_list_table_name)->insert($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }
    //根据条件查询tag条数方法
    public function getTagCount($where = []){
        if(empty($where)){
            return 0;
        }
        return Db::name($this->tag_table_name)->where($where)->count('id');
    }
    //增加tag表某一字段计数方法
    public function incTagField($where = [] ,$field = 'total' ,$num = 1){
        return Db::name($this->tag_table_name)->where($where)->inc($field ,$num);
    }
    //查询tag表信息方法
    public function getTagInfo($where = [] ,$field = ' * '){
        if(empty($where)){
            return [];
        }
        return Db::name($this->tag_table_name)->field($field)->where($where)->find();
    }
    //高级添加tag信息方法
    public function addFullTagInfo($tag_arr = [] ,$article_id = '' ,$column_id = ''){
        if(empty($tag_arr) || empty($article_id) || empty($column_id)){
            return false;
        }
        //循环tag数组
        foreach($tag_arr as $key => $value){
            $where = ['tag_name'=>$value ,'column_id'=>$column_id];
            $num = $this->getTagCount($where);
            if($num == 0){
                //组合数据添加tag表
                $arr = [
                    'tag_name'=>$value,
                    'column_id'=>$column_id,
                    'count'=>0,
                    'total'=>1,
                    'create_time'=>time(),
                    'weekcc'=>0,
                    'daycc'=>0,
                    'monthcc'=>0
                ];
                $tag_id = $this->addTagInfo($arr);
            }else if($num == 1){
                //更新tag表标签次数
                $this->incTagField($where);
                //查询对应tag标签id
                $tag_id = $this->getTagInfo($where ,' id ');
            }
            //添加tag_list表信息
            $b = [
                'article_id'=>$article_id,
                'tag_id'=>$tag_id
            ];
            $this->addTagListInfo($b);
        }
        return true;
    }
}