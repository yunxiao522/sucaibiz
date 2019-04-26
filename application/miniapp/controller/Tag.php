<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/11
 * Time: 13:13
 * Description:
 */


namespace app\miniapp\controller;
use think\Controller;

class Tag extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //根据栏目获取小程序tag列表
    public function getTagList(){
        //验证数据
        $column = input('column');
        if(!isset($column) || empty($column) || !is_numeric($column)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';die;
        }
        //组合查询条件
        if($start != 0){
            $where = " column_id = $column and id < $start ";
        }else{
            $where = " column_id = $column ";
        }

        $where .= " and status = 1 ";

        $miniapp = new \app\miniapp\model\Tag();
        $tag_list = $miniapp->getMiniAppTagList($where ,' tag_id,name,litpic ' ,15 );
        //获取小程序全部列表
        $tag_all_list = $miniapp->getMiniAppTagList(['status'=>1] ,' tag_id ' ,1000 ,' id asc ');
        //循环处理列表数据
        foreach($tag_list as $key => $value){
            foreach($tag_all_list as $k => $v){
                if($value['tag_id'] == $v['tag_id']){
                    $tag_list[$key]['index'] = $k;
                }
            }
        }
        if(!empty($tag_list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$tag_list
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'已经到底啦...'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //获取小程序全部tag列表
    public function getAllTagList(){
        $tag = new \app\miniapp\model\Tag();
        $tag_list = $tag->getMiniAppTagList(['status'=>1] ,' id,name,tag_id ' ,1000 ,' id asc ');
        if(!empty($tag_list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$tag_list
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'获取的数据为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //获取小程序tag文档列表
    public function getTagArticleList(){
        //验证数据
        $tag_id = input('tag_id');
        if(!isset($tag_id) || empty($tag_id)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';die;
        }
        //组合查询条件
        $where = " tag_id = $tag_id ";
        if($start != 0){
            $where .= " article_id < $start";
        }
        //获取文档id列表
        $tag = new \app\miniapp\model\Tag();
        $article = new \app\miniapp\model\Article();
        $article_id_list = $tag->getTagLList($where ,' article_id ' ,15);
        array_column($article_id_list ,'article_id');
        $arr = [];
        foreach($article_id_list as $key => $value){
            $article_info = $article->getArticleInfo(['id'=>$value['article_id'] ,'is_delete'=>1] ,' litpic,id,column_id ');
            $arr[] = $article_info;
        }
        if(!empty($arr)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$arr
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'已经到底啦...'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
}