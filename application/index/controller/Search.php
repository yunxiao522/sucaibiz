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
use app\model\ArticleImages;
use app\model\Column;
use app\model\SearchColumn;
use app\model\SearchHistory;
use app\model\TagList;
use app\model\Tag;
use SucaiZ\Page;
use think\View;
use XS;

class Search extends Common
{
    private $limit = 10;
    // 迅搜实例化对象
    private $Xs;
    // 搜索词
    public $keyword;
    // 搜索使用的分类
    public $column_type = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(){

        $this->getKeyWord();

        $this->shareData();

        if(!empty($this->keyword)){
            //记录开始查询微秒时间戳
            $start = microtime(true);

            $this->addSearchHistory();

            $this->getColumnType();

            $this->getXs();

            $list = $this->search();

            $list = $this->dealList($list);

            //记录结束查询时微秒时间戳
            $end = microtime(true);
            $list['time'] = number_format(($end - $start), 2, '.', '');
            //获取分页数据
            $pageobj = new Page($list['count'],$this->limit);
            View::share('list',$list);
            View::share('paging',$pageobj->render());
            return view('./templates/search');
        }else{
            return 404;
        }

    }

    /**
     * @return mixed
     * Description 执行搜索操作
     */
    protected function search(){
        //处理搜索词
        $this->dealKeyword();
        //组合查询条件
        if($this->column_type == 0){
            $where = $this->keyword;
        }else{
            $where = "$this->keyword  column_type:$this->column_type";
        }
        $search = $this->Xs->getSearch();
        $search = $search->setQuery($where);
        $result = $search->setLimit($this->limit, $this->getLimits())->search();
        $list = [];
        foreach ($result as $value){
            $list['data'][] = $value->toArray();
        }
        $list['count']  = $search->getLastCount();
        return $list;
    }

    /**
     * Description 处理搜索词
     */
    protected function dealKeyword(){
        switch ($this->keyword){
            case strpos($this->keyword, '明星') !== false:
                $this->keyword = str_replace('明星', '', $this->keyword);
                break;
            case strpos($this->keyword, '电视剧') !== false:
                $this->keyword = str_replace('电视剧', '', $this->keyword);
                break;
        }
    }

    /**
     * @param $list
     * Description 处理搜索结果列表数据
     */
    protected function dealList($list){
        //循环处理列表数据
        foreach($list['data'] as $key => $value){
            //获取文档的tag标签
            $tag_arr = TagList::getAll(['article_id'=>$value['id']],'tag_id');
            $tag_arr = array_column($tag_arr,'tag_id');
            $tag_list = [];
            foreach($tag_arr as $tag_id){
                $tag_list[] = [
                    'name'=> Tag::getField(['id'=>$tag_id],'tag_name'),
                    'url'=>Url::getTagUrl($tag_id,true,true)
                ];
            }
            //tag标签列表前添加文档所属栏目信息
            array_unshift($tag_list,[
                'name'=>Column::getField(['id'=>$value['column_id']],'type_name'),
                'url'=>self::getColumnUrl($value['column_id'],true,true)
            ]);
            $list['data'][$key]['tag'] = $tag_list;
            $list['data'][$key]['url'] = self::getArticleUrl($value['id'],0,true,true);
            $list['data'][$key]['pubdate'] = date('Y-m-d',$value['pubdate']);
            if($value['channel'] == 1){

            }else if($value['channel'] == 2){
                //获取图集的前4张照图像
                $imgurls = ArticleImages::getField(['article_id'=>$value['id']],'imgurls');
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
        return $list;
    }

    /**
     * Description 获取前台提交搜索的词
     */
    protected function getKeyWord(){
        $this->keyword = input('keyword');
    }

    /**
     * Description 添加搜索记录
     */
    protected function addSearchHistory(){
        if(!empty($this->keyword)){
            SearchHistory::addHistory($this->keyword,$this->uid);
        }
    }

    /**
     * Description 获取搜索类型
     */
    protected function getColumnType(){
        $type = input('type');
        $type = empty($type)?0:$type;
        if($type != 0){
            $this->column_type = SearchColumn::getField(['id'=>$type],'cid');
        }
    }

    /**
     * Description 分配数据到页面
     */
    protected function shareData(){
        $type_list = SearchColumn::getAll(['status'=>1],'id,name',' id asc ');
        View::share('type_list',$type_list);
        View::share('type',input('type'));
        View::share('keyword',$this->keyword);
    }

    /**
     * Description 获取迅搜实例
     */
    protected function getXs(){
        $this->Xs = new XS('./application/xunsearch.ini');
    }

    /**
     * @return float|int
     * Description 获取分页相关信息
     */
    protected function getLimits(){
        $page = input('page');
        $page = empty($page) ? 1 : $page;
        return ($page - 1) * $this->limit;
    }
}