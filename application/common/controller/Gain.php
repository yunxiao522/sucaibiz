<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/9/7
 * Time: 10:14
 * Description：获取消息控制器
 */

namespace app\common\controller;
use app\common\model\Article;
use app\common\model\Column;
use app\common\model\Tag;
use SucaiZ\config;
use think\Controller;

class Gain extends Controller
{
    //储存redis实例
    private $redis = null;
    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
    }
    //根据文档id获取tag标签数据
    /**
     * @param int $article_id 文档id
     * @param bool $state tag标签链接类型
     * @return array|string 返货所需的tag标签数据。（动态返回json字符串，静态返回数组数据）
     */
    public function getTagInfo($article_id = 0 ,$state = false){
        if($article_id == 0 || !is_numeric($article_id)){
            return [];
        }
        //实例化tag模型
        $tag = new Tag();
        $where = ['article_id'=>$article_id];
        $tag_list = $tag->getTagList($where);
        //循环列表获取tag详细信息
        $tag_info_list = [];
        foreach($tag_list as $key => $value){
            $tag_info = $tag->getTagInfo(['id'=>$value['tag_id']]);
            $tag_info_list[] = [
                'name'=>$tag_info['tag_name'],
                'id'=>$value['tag_id'],
            ];
        }
        if($state){
            return $tag_info_list;
        }else{
            return json_encode($tag_info_list ,JSON_UNESCAPED_UNICODE);
        }
    }
    //根据文档id获取文档输出内容
    public function getArticleInfo($article_id = 0 ,$page = 1){

    }
    //根据栏目id获取栏目输出内容
    public function getColumnListInfo($column_id = 0 ,$page = 1){

    }
    //获取文档位置
    /**
     * @param int $column_id 栏目id
     * @param string $article_title 文档标题
     * @param bool $state 获取的链接类型
     * @return string 返回整个文档位置的字符串
     */
    public function getArticleSiteInfo($column_id = 0 ,$article_title = '' ,$state = false){
        if(empty($column_id) || empty($article_title)){
            return '';
        }
        $column_list = $this->getColumnList();
        $column_arr = $this->getParentColumn($column_list ,$column_id);
        //添加首页和文档到数组中
        krsort($column_arr);
        array_unshift($column_arr, '<a href="/" title="首页">首页</a>');
        array_push($column_arr, $article_title);
        $interval = config::get('cfg_list_symbol');
        //文档位置字符串
        return implode($interval, $column_arr);

    }
    //私有获取整个栏目列表方法
    /**
     * @return 整个栏目列表数据
     */
    private function getColumnList(){
        $column_key = 'column_list_cache';
        $column_list = $this->redis->get($column_key);
        if(empty($column_list)){
            $column = new Column();
            $column_list = $column->getColumnList([] ,' * ' ,1000);
            //将取出的栏目列表存储redis中
            $column_list_string = json_encode($column_list ,JSON_UNESCAPED_UNICODE);
            $this->redis->set($column_key ,$column_list_string ,600);
            return $column_list;
        }else{
            return json_decode($column_list);
        }
    }
    //私有获取父级信息方法
    /**
     * @param $column_list 栏目列表数据
     * @param $column_id 获取父级栏目的栏目id
     * @return 父级栏目的数组
     */
    private function getParentColumn($column_list, $column_id){
        $parent_key  = 'parent_column_' .$column_id;
        $column_arr =  $this->redis->get($parent_key);
        if(empty($column_arr)){
            $column_arr = [];
            foreach ($column_list as $key => $value) {
                if ($value['id'] == $column_id) {
                    $column_path = rtrim(str_replace('{cmspath}', '', $value['type_dir']), '/') . '/';
                    $url = $column_path . $value['defaultname'];
                    $column_arr[] = "<a href='$url' title='$value[type_name]'>$value[type_name]</a>";
                    $value['type_name'];
                    if ($value['parent_id'] != 0) {
                        $this->getParentColumn($column_list, $value['parent_id']);
                    }
                }
            }
            //将数据 组合成json字符串,存入redis
            $column_string = json_encode($column_arr ,JSON_UNESCAPED_UNICODE);
            $this->redis->set($parent_key ,$column_string ,600);
            return $column_arr;
        }else{
            return json_decode($column_arr);
        }
    }
    //获取随机n篇文档信息
    /**
     * @param int $column_id 栏目id
     * @param int $limit 获取随机文档的数量
     * @param bool $state 获取连接的类型
     * @param bool $full 获取链接是否是完整
     * @param bool $json 获取返回数据的类型
     * @return array|string 返回最终的结果数据
     */
    public function getRandArticle($column_id = 0 ,$limit = 4 ,$state = false ,$full = false ,$json = false){
        $article = new Article();
        //组合查询条件
        $where = ['column_id'=>$column_id];
        $article_list = $article->getRandeArticleList($where ,' * ' ,$limit);
        //循环处理查询的信息
        $list = [];
        $url = new Url();
        foreach($article_list as $key => $value){
            $article_info = [
                'id'=>$value['id'],
                'title'=>$value['title'],
                'url'=>$url->getArticleUrl($value['id'] ,$state ,$full)
            ];
            $list[] = $article_info;
        }
        if($json){
            return json_encode($list ,JSON_UNESCAPED_UNICODE);
        }else{
            return  $list;
        }
    }
    //获取热门文档信息
    /**
     * @param int $column_id 栏目id
     * @param int $limit 查询文档数量
     * @param bool $is_son 是否查询子栏目
     * @param bool $state 文档链接类型
     * @param bool $full 文档链接是否全部
     * @param bool $json 返回数据类型
     * @return 热门文档列表数据
     */
    public function getHotArticle($column_id = 0 ,$limit = 4 ,$is_son = false ,$state = false ,$full = false ,$json = false){
        if(empty($column_id)){
            return [];
        }
        $article = new Article();
        if($is_son){
            //获取子栏目数据
            $son_column = $this->getSonColumn($column_id);
            //组合查询条件
            $where = ['column_id' ,['in' ,$son_column]];
        }else{
            $where = ['column_id' ,$column_id];
        }
        $article_list = $article->getArticleList($where ,' id,title,$column_id ' ,$limit ,' click desc ');
        //循环处理列表数据
        $list = [];
        $url = new Url();
        foreach($article_list as $key => $value){
            $article_info = [
                'id'=>$value['id'],
                'title'=>$value['title'],
                'url'=>$url->getArticleUrl($value['id'] ,$state ,$full)
            ];
            $list[] = $article_info;
        }
        if($json){
            return json_encode($list ,JSON_UNESCAPED_UNICODE);
        }else{
            return $list;
        }
    }
    //根据栏目id获取子栏目
    /**
     * @param int $column_id 栏目id
     * @return  子栏目数组
     */
    private function getSonColumn($column_id = 0){
        $column = new Column();
        //组合查询条件
        $where = ['parent_id'=>$column_id];
        $column_list = $column->getColumnList($where);
        return $column_list;
    }
}