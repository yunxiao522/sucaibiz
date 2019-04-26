<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/9 0009
 * Time: 14:55
 */

namespace app\index\controller;
error_reporting(0);

use app\common\controller\Url;
use SucaiZ\Page;
use think\Model;
use think\View;

class Search extends Common
{
    private $limit = 10;
    public function __construct()
    {
        parent::__construct();
    }


    public function index(){
        $keyword = input('keyword');
        if($keyword){
            Model('searchhistory')->addHistory($keyword,$this->uid);
        }
        $type = input('type');
        $type = empty($type)?0:$type;
        View::share('type',$type);
        View::share('keyword',$keyword);
        $type_list = Model('searchcolumn')->getAll(['status'=>1],'id,name',' id asc ');
        View::share('type_list',$type_list);
        if($keyword){
            //组合查询列表条件
            $where = [
                'title'=>[
                    'like',
                    "%$keyword%"
                ]
            ];
            if($type != 0){
                $column = Model('searchcolumn')->getField(['id'=>$type],'cid');
                $column_arr = Model('column')->getAll(['parent_id'=>$column],'id');
                $column_arr = array_column($column_arr,'id');
                $column_arr[] = $column;
                $where['column_id'] = [
                    'in',
                    $column_arr
                ];
            }
            $url = new Url();
            //记录开始查询微秒时间戳
            $start = microtime(true);
            Model('article')->limit = $this->limit;
            //获取文档列表
            $list = Model('article')->getList($where,'id,title,channel,description,pubdate,litpic,click,column_id');
            //循环处理列表数据
            foreach($list['data'] as $key => $value){
                //获取文档的tag标签
                $tag_arr = Model('taglist')->getAll(['article_id'=>$value['id']],'tag_id');
                $tag_arr = array_column($tag_arr,'tag_id');
                $tag_list = [];
                foreach($tag_arr as $tag_id){
                    $tag_list[] = [
                        'name'=> Model('tag')->getField(['id'=>$tag_id],'tag_name'),
                        'url'=>$url->getTagUrl($tag_id,true,true)
                    ];
                }
                //tag标签列表前添加文档所属栏目信息
                array_unshift($tag_list,[
                    'name'=>Model('column')->getField(['id'=>$value['column_id']],'type_name'),
                    'url'=>self::getColumnUrl($value['column_id'],true,true)
                ]);
                $list['data'][$key]['tag'] = $tag_list;
                $list['data'][$key]['url'] = self::getArticleUrl($value['id'],0,true,true);
                $list['data'][$key]['pubdate'] = date('Y-m-d',$value['pubdate']);
                if($value['channel'] == 1){

                }else if($value['channel'] == 2){
                    //获取图集的前4张照图像
                    $imgurls = Model('articleimages')->getField(['article_id'=>$value['id']],'imgurls');
                    $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
                    $imgurls_arr = explode(',',$imgurls);
                    $imgurls_list = [];
                    foreach($imgurls_arr as $k => $v){
                        if($k < 4){
                            preg_match($src_rule,$v,$match);
                            $imgurls_list[] = [
                                'img'=>(string)$match[3],
                                'url'=>self::getArticleUrl($value['id'],$k,true,true)
                            ];
                        }
                    }
                    $imgurls_arr = $imgurls_list;
                    $list['data'][$key]['imgurl'] = $imgurls_arr;
                }else if($value['channel'] == 3){

                }else if($value['channel'] == 4){

                }
            }
            //记录结束查询时微秒时间戳
            $end = microtime(true);
            $list['time'] = number_format(($end - $start), 2, '.', '');
            View::share('list',$list);
            //获取分页数据
            $pageobj = new Page($list['count'],$this->limit);
            View::share('paging',$pageobj->render());
            return view('./templates/search');
        }else{
            return view('./templates/search_empty');
        }

    }
}