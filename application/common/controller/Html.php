<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/9/6
 * Time: 20:30
 * Description：生成Html信息控制器
 */

namespace app\common\controller;

use SucaiZ\config;
use SucaiZ\Page;
use think\View;

class Html extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    //生成文档页面方法
    public static function htmlArticle($article_id = 0 ,$page = 1 ,$state = false,$article_info = [],$article_extend_info = [],$column_info = [],$prev_article=[],$next_article=[],$tag_list = [],$concern_articleList=[],$author_info=[],$site=[]){
        if(empty($article_info)){
            //获取文档信息
            $article_info = Model('article')->getOne(['id'=>$article_id],'*');
        }
        if(empty($article_extend_info)){
            //获取文档扩展表信息
            $table_name = Model('ColumnType')->getField(['id'=>$article_info['channel']],'table_name');
            Model('base')->table = $table_name;
            //获取文档扩展信息
            $article_extend_info = Model('base')->getOne(['article_id'=>$article_info['id']],'*');
        }
        $info = array_merge($article_info,$article_extend_info);
        if(empty($site)){
            //获取文档位置信息
            $info['site'] = Article::getArticleSite($article_info['column_id'],$article_info['title'],$article_info['channel']);
        }else{
            $info['site'] = $site;
        }
        if(empty($column_info)){
            //获取栏目信息
            $column_info = Model('column')->getOne(['id'=>$article_info['column_id']],'*');
        }
        if(empty($tag_list)){
            //获取文档tag标签列表数据
            $tag_list = Article::getArticleTag($article_info['id']);
        }
        if(empty($concern_articleList)){
            //获取相关文档信息
            $concern_articleList = Article::getConcernArticleList($article_info['id'],$article_info['column_id'],$state);
        }
        //根据文档类型不同，用不同的方式处理数据
        switch($article_info['channel']){
            case 1:

                break;
            case 2:
                $info['smallimgurlsinfo'] = Article::getSmallimgurlsInfo($article_info['id'],$info,$column_info);
                $imgUrl = Article::getImgInfo($article_extend_info['imgurls'],($page-1));
                $mediumimagurls_list = Article::getImgInfo($article_extend_info['smallimgurl']);
                //分配数据
                View::share('imgurl',$imgUrl);
                View::share('m_list',$mediumimagurls_list);
                View::share('num',$article_extend_info['imgnum']);
                break;
            case 3:

                break;
            case 4:

                break;
        }
        //拼装文档模板完整路径
        $template_file = config::get('cfg_template_file_path') .$article_extend_info['templet'];
        if(!file_exists($template_file)){
            return '模板文件不存在';
        }
        if(empty($prev_article)){
            //获取上一篇文档信息
            $prev_article = Article::getPreviousArticleInfo($article_info['id'],$article_info['column_id']);
        }
        if(empty($next_article)){
            //获取下一篇文档信息
            $next_article = Article::getNextArticleInfo($article_info['id'],$article_info['column_id']);
        }
        if(empty($author_info)){
            $author_info = Model('user')->getOne(['id'=>$article_info['userid']],'id,nickname,face,level');
        }
        //分配数据
        View::share('tag',$tag_list);
        View::share('concern_articleList',$concern_articleList);
        View::share('page',$page);
        View::share('page_article',$prev_article);
        View::share('next_article',$next_article);
        View::share('user_info',$author_info);
        View::share('article_info',$info);
        $view = new View();
        return $view->fetch($template_file);

    }
    //获取生成文档列表信息方法
    public static function htmlColumn($column_id ,$page = 1,$state = false ,$rol,$template_file=[],$column_info = [],$column_list = [],$column_son_list = [],$nav = [],$crumb = [],$num = 0,$tag_list=[],$hot_article = [],$hot_tag_list=[]){
        if(empty($column_info)){
            $column_info = Model('column')->getOne(['id'=>$column_id],'*');
        }
        //构建查询区间参数
        $limit = ($page-1) * $rol . ',' . $rol;
        if(empty($template_file)){
            //组合模板文件完整名字
            $template_file = config::get('cfg_template_file_path') .$column_info['templist'];
        }
        if(empty($column_list)){
            //获取所有栏目列表数据
            $column_list = Model('column')->getAll([],'id,parent_id,type_name',1000);
        }
        if(empty($column_son_list)){
            //获取栏目及子栏目id
            $column_son_list = self::getSonList($column_info['id'],$column_list);
        }
        if(empty($nav)){
            //根据子栏目id获取栏目链接（用做导航）
            $nav = Column::getNav($column_son_list,false);
        }
        if(empty($crumb)){
            //获取面包屑导航
            $crumb = Column::getCrumbs($column_info['id'],$column_list,$state);
        }
        //添加本栏目id到子栏目内
        $column_son_list[] = (int)$column_id;
        //获取栏目文档数据
        $article_list = Column::getColumnArticle($column_son_list,$limit,$state);
        if(empty($num)){
            //获取文档总条数
            $num = Column::getArticleCount($column_son_list);
        }
        $column_list_arr = array_column($column_list,'type_name','id');
        //向文档列表内添加栏目信息
        foreach($article_list as $key => $value){
            $article_list[$key]['column_info'] = $column_list_arr[$value['column_id']];
        }
        if(empty($tag_list)){
            //获取tag列表
            $tag_list = Column::getTagList($column_son_list,20,'daycc desc');
        }
        //获取同级栏目列表数据
        $peer_column = Model('column')->getAll(['parent_id'=>$column_info['parent_id']],' id,type_name ' ,100 ,' id asc ');
        if(empty($hot_article)){
            //获取热门文档列表
            $hot_article = Column::getHotArticle($column_son_list,20,$state);
        }
        if(empty($hot_tag_list)){
            //获取热门标签
            $hot_tag_list = Column::getHotTag($column_info['id'],$column_son_list,20,'daycc desc' ,$state);
        }
        $listrule = $column_info['type_dir'] . str_replace('{tid}',$column_info['id'],$column_info['listrule']);
        $paging = new Page($num ,$rol,5,[],!$state,$listrule,$page);

        //分配数据
        View::share('keywords' ,$column_info['keywords']);
        View::share('description' ,$column_info['description']);
        View::share('column' ,$column_info);
        View::share('nav' ,$nav);
        View::share('crumb' ,$crumb);
        View::share('tag_list' ,$tag_list);
        View::share('article_list' ,$article_list);
        View::share('peer_column' ,$peer_column);
        View::share('id',$column_id);
        View::share('hot' ,$hot_article);
        View::share('hot_tag' ,$hot_tag_list);
        View::share('paging' ,$paging->render());

        $view = new view();
        return $view->fetch($template_file);
    }
    //获取生成Tag标签信息方法
    public function getTagInfo(){

    }
}