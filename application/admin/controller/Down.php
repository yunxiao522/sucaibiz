<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 16:52
 */

namespace app\admin\controller;

class Down extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * Description 获取热门下载数据
     */
    public function getHotDown(){
        $list = Model('Down')->getList([],'','end_time desc');
        foreach($list['data'] as $key => $value){
            $list['data'][$key]['end_time'] = date('Y-m-d H:i:s',$value['end_time']);
            $list['data'][$key]['article_title'] = Model('article')->getField(['id'=>$value['article_id']],'title');
        }
        return self::ajaxOkdata($list,'获取数据成功','');
    }
}