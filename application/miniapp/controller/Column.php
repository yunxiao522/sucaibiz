<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/14
 * Time: 12:38
 * Description:
 */


namespace app\miniapp\controller;
use app\miniapp\model\Tag;
use think\Collection;

class Column extends Collection
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取子栏目
    public function getSon(){
        //验证数据
        $cid = input('cid');
        if(!isset($cid) || empty($cid) || !is_numeric($cid)){
            echo '非法访问';die;
        }
        if(!in_array($cid ,[54,24])){
            $a = [
                'errorcode'=>1,
                'msg'=>'栏目不存在'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        $where = ['parent_id'=>$cid];
        $status = input('status');
        if(isset($status) && is_numeric($status)){
            $where['t_status'] = 1;
        }
        $column = new \app\miniapp\model\Column();
        $list = $column->getColumnList($where ,' column_id as id,type_name,t_status ');
        $list_arr = [];
        foreach($list as $key => $value){
            $value['num'] = $key;
            $list_arr[$value['id']] = $value;
        }
        array_unshift($list_arr ,['type_name'=>'最新' ,'num'=>999 ,'id'=>$cid ,'t_status'=>1]);
        if(!empty($list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取成功',
                'data'=>$list_arr
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'获取失败',
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //获取栏目信息
    public function getColumnInfo(){
        //验证数据
        $column_id = input('column');
        if(!isset($column_id) || empty($column_id) || !is_numeric($column_id)){
            echo '非法访问';die;
        }
        $column = new \app\miniapp\model\Column();
        $column_info = $column->getColumnInfo(['id'=>$column_id]);
        if(empty($column_info)){
            $a = [
                'errorcode'=>1,
                'msg'=>'获取数据失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$column_info
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //获取栏目文档列表
    public function getColumnTagList(){
        //验证数据
        $column_id = input('column');
        if(!isset($column_id) || empty($column_id) || !is_numeric($column_id)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';
        }
        //获取对应栏目的tag标签
        $tag = new Tag();
        $tag_list = $tag->getTagList(['column_id'=>$column_id] ,' * ' ,15);
        $article = [];
        //循环读取tag列表，取出每个标签的正数第一篇文章
        foreach($tag_list as $key => $value){
            $article_info = $tag->getTagListInfo(['tag_id'=>$value['id']] ,' a.title,a.litpic ' ,1);
            if(isset($article_info) && !empty($article_info[0])){
                $a=$article_info[0];
                $a['name']=$value['tag_name'];
                $a['id']=$value['id'];
                $article[] = $a;
            }
            unset($a);
        }
        if(empty($article)){
            $a = [
                'errorcode'=>1,
                'msg'=>'已经到底了'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$article
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
}