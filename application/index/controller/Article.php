<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/3 0003
 * Time: 22:59
 */

namespace app\index\controller;


use App\Http\Requests\Request;

class Article extends Common
{
    private $article_click_key = 'article_click_id';
    private $redis;
    private $ttl = 604800;
    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
    }

    /**
     * @return false|string
     * Decription 获取文档点击数
     */
    public function getArticleInfo(){
        //获取前台传递的参数
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return '';
        }
        $article_click_key = str_replace('id',$id,$this->article_click_key);
        //判断是否存在缓存,存在则直接读取数据
        $num = $this->redis->get($article_click_key);
        if(empty($num)){
            //组合查询条件查询点击数据
            $where = [
                'id'=>$id
            ];
            $click = Model('article')->getField($where,'click');
            $this->redis->set($article_click_key,$click,$this->ttl);
            $num = $click;
        }else{
            $this->redis->incr($article_click_key);
            $num ++ ;
        }
        return $this->ajaxOkdata(['click'=>$num]);
    }

    public function sj(){

    }
}