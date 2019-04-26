<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/25 0025
 * Time: 11:05
 */

namespace app\common\controller;

use think\Request;

class Tag extends BaseController{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * @param $article_id
     * @return array
     * Description 获取文档tag列表
     */
    public static function getArticleTagList($article_id){
        $list = Model('TagList')->getAll(['article_id'=>$article_id],'tag_id',1000);
        $tag_list = [];
        foreach($list as $key => $value){
            $tag_list[] = Model('Tag')->getOne(['id'=>'tag_id'],'*');
        }
        return $tag_list;
    }
}