<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/12 0012
 * Time: 14:40
 */

namespace app\common\controller;

use SucaiZ\config;

class Column extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $column_list 栏目列表数据
     * @param $state 是否获取静态访问链接
     * @return array
     * Description 获取栏目导航
     */
    public static function getNav($column_list,$state = true){
        //实例化column实例
        $column = model('Column');
        $nav = [];
        foreach ($column_list as $value){
            //构建查询条件
            $w = ['id'=>$value];
            $column_info = $column->getColumnInfo($w ,' id,type_name ');
            $nav[] = "<a href='". self::getColumnUrl($column_info['id'],$state,false) ."' title='$column_info[type_name]'>$column_info[type_name]</a>";
        }
        return $nav;
    }

    /**
     * @param $column_id 栏目id
     * @param $column_list 栏目列表数据
     * @param bool $state 是否获取静态访问链接
     * @return string
     * Description 获取栏目面包屑导航
     */
    public static function getCrumbs($column_id,$column_list,$state = true){
        $parent_list = self::getParentColumn($column_id,$column_list);
        $column_arr = [];
        foreach($parent_list as $value){
            $column_info = Model('column')->getOne(['id'=>$value],'id,type_name');
            $column_arr[] = "<a href='". self::getColumnUrl($column_info['id'],$state,false) ."' title='$column_info[type_name]'>$column_info[type_name]</a>";
        }
        //添加首页和文档到数组中
        krsort($column_arr);
        array_unshift($column_arr, '<a href="/" title="首页">首页</a>');
        //栏目字符串
        return implode(config::get('cfg_list_symbol'), $column_arr);
    }

    public static function getColumnArticle($column_son_list,$limit,$state = false){
        //组合查询条件获取数据
        $where = [
            'column_id'=>[
                'in',
                $column_son_list
            ],
            'is_delete'=>1,
            'is_audit'=>1,
            'draft'=>2
        ];
        $article_list = Model('article')->getAll($where,'id',$limit,'id desc');
        //循环获取文档信息
        $article_arr = [];
        foreach($article_list as $value){
            $article_info = Model('article')->getOne(['id'=>$value['id']],'*');
            $article_info['url'] = self::getArticleUrl($article_info['id'],0,$state,false);
            $article_arr[] = $article_info;
        }
        return $article_arr;
    }

    public static function getArticleCount($column_son_list){
        $where = [
            'column_id'=>[
                'in',
                $column_son_list
            ],
            'is_delete'=>1,
            'is_audit'=>1,
            'draft'=>2
        ];
        return Model('article')->getCount($where);
    }

    public static function getTagList($column_list,$limit,$order,$state = false){
        $where =  [
            'column_id'=>
            [
                'in',
                $column_list
            ]
        ];
        Model('tag')->table = 'tag';
        $tag_list = Model('tag')->getAll($where,'id,tag_name',$limit,$order);
        $list = [];
        foreach($tag_list as $value){
            $list[] = "<a href='". self::getTagUrl($value['id'],$state,false) ."' title='$value[tag_name]' hreflang='zh' target='_blank'>$value[tag_name]</a>";
        }
        return $list;
    }

    /**
     * @param $column_son_list
     * @param $limit
     * @param bool $state
     * @return mixed
     * Description 获取热门文档数据
     */

    public static function getHotArticle($column_son_list, $limit, $state = false){
        $where = [
            'column_id'=>[
                'in',
                $column_son_list
            ],
            'is_delete'=>1,
            'is_audit'=>1,
            'draft'=>2
        ];
        //查询文档列表
        $article_list = Model('article')->getAll($where ,' * ' ,$limit ,' click desc ');
        foreach($article_list as $key => $value){
            $article_list[$key]['url'] = self::getArticleUrl($value['id'],1,$state,false);
        }
        //返回文档数据
        return $article_list;
    }

    public static function getHotTag($columen_id ,$column_son_list = [] ,$limit ,$order ,$state = false){
        if(empty($column_son_list)){
            $where = ['column_id'=>$columen_id];
        }else{
            $where = [
                'column_id'=>[
                    'in',
                    $column_son_list
                ]
            ];
        }
        $hot_tag_list = Model('tag')->getALl($where ,' id,tag_name ' ,$limit ,$order);
        foreach($hot_tag_list as $key => $value){
            $hot_tag_list[$key]['url'] = self::getTagUrl(['id'=>$value['id']],$state,false);
        }
        return $hot_tag_list;
    }
}