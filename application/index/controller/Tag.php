<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/16
 * Time: 12:08
 * Description：Tag标签控制器
 */
namespace app\index\controller;
use think\Controller;
use think\Request;

class tag extends Controller{
    public function __construct()
    {
        parent::__construct();
    }

    //更新tag点击数方法
    public function incr(){
        if(Request::instance()->isPost()){
            $id = input('id');
            $where = ['id' => $id];
            $tag = model('Tag');
            $tag->incrTag($where);
        }
    }

    //显示tag列表方法
    public function show(){
        $id = input('id');
        if(!isset($id) || !is_numeric($id)){
            Header('location:/404.html');
        }
        return View('../../admin/view/public/tag_show');
    }

    //根据文档id更新tag点击数
    public function incrByArticleId(){
        if(Request::instance()->isPost()){
            //验证数据
            $aid = input('id');
            if(!isset($aid) || empty($aid) || !is_numeric($aid)){
                echo '非法访问';die;
            }
            //组合查询条件
            $where = ['article_id'=>$aid];
            $tag = new \app\index\model\Tag();
            $tag_list = $tag->getTagArticleList(['article_id'=>$aid] ,' * ');
            //循环tag列表,增加点击量
            foreach($tag_list as $key => $value){
                //组合更新条件
                $where1 = ['id' => $value['tag_id']];
                $tag->incrTag($where1);
                $tag->incrTag($where1,'weekcc');
                $tag->incrTag($where1,'daycc');
                $tag->incrTag($where1,'monthcc');
                unset($where1);
            }
        }
    }
}