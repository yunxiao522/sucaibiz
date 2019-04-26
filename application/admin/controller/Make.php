<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/25
 * Time: 17:57
 * Description: 处理生成html页面相关操作类，第二层
 */


namespace app\admin\controller;
use SucaiZ\config;
use app\admin\model\Article;
use app\admin\model\Column;
use app\index\controller\Html;
use SucaiZ\Page;
use think\Db;
use think\View;

class Make extends Common
{
    //存储article实例
    private $article;
    //储存column实例
    private $column;
    //模板存储目录
    public $templates_path = '';
    //存储redis实例
    private $redis;
    //获取面包屑分隔符
    private $list_symbol;
    //生成的链接方式
    private $static;
    //存储每页的文档条数
    private $rol;
    //储存html实例
    private $html;

    //构造函数
    public function __construct()
    {
        parent::__construct();
        $this->article = new Article();
        $this->column = new Column();
        $this->templates_path = './' .config::get('cfg_template_file_path');
        $this->redis = getRedis();
        $this->list_symbol = config::get('cfg_list_symbol');
        $this->static = config::get('cfg_make_url_static');
        $this->rol = config::get('cfg_list_max_num');
        $this->html = new Html();
    }

    //生成文档内容静态页面
    public function htmlArticle($article_id ,$channel ,$sum ,$j ,$token){
        //获取文档基本信息
        $article_info = Model('article')->getOne(['id'=>$article_id]);
        //获取文档扩展信息
        $atter_table_name = Model('article')->affiliate_table_name[$channel];
        $article_attr_info = Db::name($atter_table_name)->where(['article_id'=>$article_id])->field('*')->find();
        //组合数据
        $info = array_merge($article_info,$article_attr_info);
        $article = new \app\common\controller\Article();
        //更多数据,减少生成数据时对数据库的查询
        $more = [
            'column_info' =>Model('column')->getOne(['id'=>$info['column_id']],'*'),
            //获取上一篇数据
            'previous_article' => $this->getPreviousArticleInfo($article_id,$info['column_id']),
            //获取下一篇数据
            'next_article' => $this->getNextArticleInfo($article_id,$info['column_id']),
            //获取文档的tag列表
            'tag_list'=>$article->getArticleTag($article_id),
            //获取相关文档
            'concern_articleList'=>$article->getConcernArticleList($article_id,$info['column_id'],true),
            'author_info'=>Model('user')->getOne(['id'=>$article_info['userid']],'id,nickname,face,level')
        ];
        $site = \app\common\controller\Article::getArticleSite($article_info['column_id'],$article_info['title'],$article_info['channel']);
        //获取文档位置信息
        $info['site'] = $article->getArticleSite($info['column_id'],$info['title'],$info['channel']);
        if ($info['channel'] == 2) {
            $info['smallimgurlsinfo'] = $article->getSmallimgurlsInfo($article_id,$info,$more['column_info']);
            for ($i = 1; $i <= $info['imgnum']; $i++) {
                $this->setInfo($token, $this->getPercentum($j, $sum), "生成栏目id为 $info[column_id] 文档id为 $info[article_id] 的第 $i 页");
                $j++;
                $res = $this->makeArticle($article_info,$article_attr_info,$i,$more['column_info'],$more['previous_article'],$more['next_article'],$more['tag_list'],$more['concern_articleList'],$more['author_info'],$site);
                if(!$res){
                    return false;
                }
            }
        } else {
            $this->setInfo($token, $this->getPercentum($j, $sum), "生成栏目id为 $info[column_id] 文档id为 $info[article_id]");
            return $this->makeArticle($article_info,$article_attr_info,1,$more['column_info'],$more['previous_article'],$more['next_article'],$more['tag_list'],$more['concern_articleList'],$more['author_info'],$site);
        }
    }

    //生成栏目列表页面

    /**
     * @param $column
     * @param $num
     * @param $token
     * @param $j
     * @param $t
     * @return array|bool
     */
    public function htmlColumn($column ,$num ,$token ,$j ,$t){
        //获取本栏目要生成的文件数
        $column_article_num = Model('article')->getCount(['column_id'=>$column],'id');
        $b = ceil($column_article_num /$this->rol) + 1;
        //判断是否超过生成的页面最大数
        if ($b <= $num) {
            $c = $b;
        }else{
            $c = $num;
        }
        //获取需要的数据,前置查询数据,减少重复查询数据.优化生成速度
        $column_info = Model('column')->getOne(['id'=>$column],'*');
        $column_list = Model('column')->getAll([],'id,parent_id,type_name',1000);
        $column_son_list = self::getSonList($column_info['id'],$column_list);
        $nav = \app\common\controller\Column::getNav($column_son_list,false);
        $crumb = \app\common\controller\Column::getCrumbs($column_info['id'],$column_list,true);
        $article_num = \app\common\controller\Column::getArticleCount($column_son_list);
        $tag_list = \app\common\controller\Column::getTagList($column_son_list,20,'daycc desc');
        $hot_article = \app\common\controller\Column::getHotArticle($column_son_list,20,true);
        $hot_tag_list = \app\common\controller\Column::getHotTag($column_info['id'],$column_son_list,20,'daycc desc' ,true);
        //生成栏目首页
        $this->setInfo($token, $this->getPercentum($j, $num), "生成栏目id为 $column 栏目的首页");
        $this->makeColumnDefault($column,$column_info,$column_list,$column_son_list,$nav,$crumb,$article_num,$tag_list,$hot_article,$hot_tag_list);
        //循环生成栏目列表
        for ($i = 1; $i <= $c - 1; $i++) {
            $this->setInfo($token, $this->getPercentum(($j + 1), $num), "生成栏目id为 $column 栏目列表页第 $i 页");
            $this->makeColumnList($column,$i,$column_info,$column_list,$column_son_list,$nav,$crumb,$article_num,$tag_list,$hot_article,$hot_tag_list);
        }
        //判断生成文数到达指定条数则返回
        if($c >= $num){
            return false;
        }else{
            return ['j'=>$j ,'num'=>$num-$c];
        }
    }
    //生成网站首页方法
    /**
     * @param $type
     */
    public function htmlIndex($type){
        if($type == 1){
            if(file_exists('./index.html'))
                unlink('./index.html');
        }else if($type == 2){
            $index = new \app\index\controller\Index();
            $html_data=$index->index();
            makeHtml($html_data ,'.' ,'index.html');
        }
    }
    /**
     * @param $article_info 文档信息
     * @param $page 文档分页
     * @return bool 生成结果
     * Description 生成文档html
     * 2.0版本,修改逻辑，只做逻辑处理，对数据库的读取放到上一层
     */
    private function makeArticle($article_info,$article_extend_info,$page,$column_info,$prev_article=[],$next_article=[],$tag_list = [],$concern_articleList=[],$author_info=[],$site=[])
    {
        $html_data = \app\common\controller\Html::htmlArticle($article_info['id'],$page,true,$article_info,$article_extend_info,$column_info,$prev_article,$next_article,$tag_list,$concern_articleList,$author_info,$site);
        //获取文件保存路径
        $file_path ='.' .rtrim(str_replace('{cmspath}' ,'' ,$column_info['type_dir']) ,'/');
        //获取要保存的文件名规则
        $namerule = strtolower($column_info['namerule']);
        //获取文档的创建时间
        $create_time = $article_info['pubdate'];
        if($page == 1){
            $page='';
        }else{
            $page='_' .$page;
        }
        //循环替换文档名规则内容
        $namerule = str_replace( [
            '{y}', '{m}', '{d}', '{aid}','_{page}'
        ],[
            date('Y' ,$create_time),date('m' ,$create_time),date('d' ,$create_time),$article_info['id'],$page
        ] ,$namerule);
        //过滤文件名规则无用字符
        $namerule = ltrim($namerule ,'{typedir}/');
        //调用生成html文件方法
        $res = $this->Html($html_data ,$file_path ,$namerule);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param $column
     * @param $column_info
     * @param $column_list
     * @param $column_son_list
     * @param $nav
     * @param $crumb
     * @param $article_num
     * @param $tag_list
     * @param $hot_article
     * @param $hot_tag_list
     * Description 生成栏目首页方法
     * 2.0以后,数据查询上移至上一层,本层只做整合数据和生成文件,处理逻辑放置common的html控制器中.
     */
    public function makeColumnDefault($column,$column_info,$column_list,$column_son_list,$nav,$crumb,$article_num,$tag_list,$hot_article,$hot_tag_list){
        //组合模板文件完整名字
        $template_file = $this->templates_path . $column_info['default_index'];
        //分配常用数据到页面
        $this->assignDate();
        $html_data = \app\common\controller\Html::htmlColumn($column,1,true,$this->rol,$template_file,$column_info,$column_list,$column_son_list,$nav,$crumb,$article_num,$tag_list,$hot_article,$hot_tag_list);
        //生成页面
        makeHtml($html_data, '.' . $column_info['type_dir'], $column_info['defaultname']);
    }

    /**
     * @param $column
     * @param $page
     * @param $column_info
     * @param $column_list
     * @param $column_son_list
     * @param $nav
     * @param $crumb
     * @param $article_num
     * @param $tag_list
     * @param $hot_article
     * @param $hot_tag_list
     * Description 生成栏目列表方法
     * 2.0以后,数据查询上移至上一层,本层只做整合数据和生成文件,处理逻辑放置common的html控制器中.
     */
    public function makeColumnList($column,$page,$column_info,$column_list,$column_son_list,$nav,$crumb,$article_num,$tag_list,$hot_article,$hot_tag_list){
        //组合模板文件完整名字
        $template_file = $this->templates_path . $column_info['templist'];
        //分配常用数据到页面
        $this->assignDate();
        $html_data = \app\common\controller\Html::htmlColumn($column,$page,true,$this->rol,$template_file,$column_info,$column_list,$column_son_list,$nav,$crumb,$article_num,$tag_list,$hot_article,$hot_tag_list);
        $filename = str_replace([
            '{page}','{tid}'
        ],[
            $page,$column
        ],$column_info['listrule']);
        //生成页面
        makeHtml($html_data, '.' . $column_info['type_dir'], $filename);
    }

    //根据栏目id获取查询子栏目信息的where
    /**
     * @param $column
     * @return string
     */
    public function getWhere($column){
        //获取栏目及子栏目
        $column_list = $this->getSonColumn($column);
        $column_list[] = $column;

        //获取文档数据
        //循环拼接查询条件
        $where = '';
        foreach ($column_list as $value) {
            $where .= " column_id = $value or ";
        }

        //去掉多余的or
        return rtrim($where, 'or ');
    }

    //生成网站首页方法
    public function makeIndex(){
        //获取网站设置参数
        $host_default = './' .config::get('cfg_host_default_name');
        if(file_exists($host_default)){
            unlink($host_default);
        }

    }

    //获取文档总条数
    /**
     * @param $where
     * @param $channel
     * @return float|int|string
     */
    public function getArticleSum($column ,$channel){
        $sum = $this->getRedis('a_sum_' .$column);
        if(empty($sum)){
            //获取查询条件
            $where = $this->getWhere($column);
            if($channel == 2){
                $sum = $this->article->getImagesSum($where);
            }else{
                $sum = $this->article->getArticleCount($where);
            }
        }

        return $sum;
    }


    //根据栏目id获取所有子栏目

    /**
     * @param $column_id 栏目id
     * @return array|false|mixed|\PDOStatement|string|\think\Collection 获取包含栏目本身的列表数据
     * Description: 使用redis,存储相应栏目的子栏目，避免运行递归话费过多时间
     */
    public function getSonColumn($column_id)
    {
        $column_list = $this->getRedis('son_' . $column_id);
        if (empty($column_list)) {

            //先查询出所有栏目
            $column_list = $this->column->getColumnListToCache([], ' id,parent_id ');

            //获取所有子栏目
            $column_list = $this->getSonList($column_id, $column_list);

            //添加该栏目到列表内
            $column_list[] = $column_id;
            $this->setRedis('son_' . $column_id, $column_list);
        }
        return $column_list;
    }

    //获取面包屑导航

    /**
     * @param $column_id
     * @param string $column_title
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCrumbs($column_id, $column_title = '')
    {

        //获取全部栏目数据
        $column_list = $this->column->getColumnList([], ' id,parent_id,type_name ');

        //获取父级栏目
        $column_arr = $this->getParentColumn($column_list, $column_id);

        //添加首页和文档到数组中
        krsort($column_arr);
        array_unshift($column_arr, '<a href="/" title="首页">首页</a>');

        //判断是否需要添加自身数据到面包屑
        if(empty($column_list)){
            array_push($column_arr ,$column_title);
        }

        //栏目字符串
        $crumb = implode($this->list_symbol, $column_arr);

        //分配面包屑导航数据到页面
        $this->assign('crumb', $crumb);

    }

    //导航数据接口

    /**
     * @param string $type
     * @param $column
     * @param $length
     * @param bool $static
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     */
    public function getNav($type = '',$column ,$length ,$static = false){
        if($type == 1){
            $column_info = $this->column->getColumnInfoToCache((['id'=>$column]) ,'parent_id');
            $column_list = $this->column->getColumnListToCache(['parent_id'=>$column_info['parent_id']] ,'id,type_name,type_dir,defaultname' ,$length ,' id asc ');
        }else{
            $column_list = $this->column->getColumnListToCache(['parent_id'=>$column] ,' id,type_name,type_dir,defaultname ' ,$length ,' id asc ');
        }
        //循环处理访问路径
        foreach($column_list as $key => $value){
            if($static){
                $url = $value['type_dir'] .'/' .$value['defaultname'];
            }else{
                $url = '/list?id=' .$value['id'];
            }
            $column_list[$key]['url'] = $url;
        }
        return $column_list;
    }

    //获取文档列表方法

    /**
     * @param $where
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticleList($where, $field = ' * ', $limit = 1000, $order = ' id desc ')
    {
        //取出文档列表
        $article_list = $this->article->getArticleList($where, $field, $limit, $order);

        //循环获取文档访问链接和所属栏目名称
        foreach ($article_list as $key => $value) {
            if($this->static == 1){
                $article_list[$key]['url'] = $this->html->getArticleUrl($value['id']);
            }else{
                $article_list[$key]['url'] = '/article?id=' .$value['id'];
            }
        }

        //返回组合后的数据
        return $article_list;
    }

    //根据栏目及子栏目文档列表

    /**
     * @param $column_id
     * @param string $field
     * @param int $limit
     * @param string $order
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticleToColumnId($column_id, $field = ' * ', $limit = 1000, $order = ' id desc ')
    {
        //获取栏目及子栏目
        $column_list = $this->getSonColumn($column_id);
        $column_list[] = $column_id;

        //获取文档数据
        //循环拼接查询条件
        $where = '';
        foreach ($column_list as $value) {
            $where .= " column_id = $value or ";
        }

        //去掉多余的or
        $where = '(' . rtrim($where, 'or ') .' ) and is_delete = 1 and is_audit = 1';

        $article_list = $this->getArticleList($where, $field, $limit, $order);

        //获取栏目列表
        $column_arr = $this->getColumnList([], ' id,type_name,type_dir,defaultname ');
        $column_list_arr = [];
        foreach ($column_arr as $value) {
            $column_list_arr[$value['id']] = $value['type_name'];
        }

        //向文档列表内添加栏目信息
        foreach ($article_list as $key => $value) {
            $article_list[$key]['column_info'] = $column_list_arr[$value['column_id']];
        }

        $this->assign('article_list', $article_list);


    }

    //获取栏目列表方法

    /**
     * @param $where
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return array|false|mixed|\PDOStatement|string|\think\Collection
     */
    private function getColumnList($where, $field = ' * ', $limit = 1000, $order = ' id desc ')
    {
        //取出栏目列表
        $column_list = $this->column->getColumnListToCache($where, $field, $limit, $order);

        //循环处理访问路径
        foreach($column_list as $key => $value){
            if($this->static == 1){
                $url = $value['type_dir'] .'/' .$value['defaultname'];
            }else{
                $url = '/list?id=' .$value['id'];
            }
            $column_list[$key]['url'] = $url;
        }

        //返回组合后的数据
        return $column_list;
    }

    //获取文档总条数

    /**
     * @param $column_id
     * @return int|string
     */
    private function getArticleCount($column_id)
    {
        //获取栏目及子栏目
        $column_list = $this->getSonColumn($column_id);
        $column_list[] = $column_id;

        //获取文档数据
        //循环拼接查询条件
        $where = '';
        foreach ($column_list as $value) {
            $where .= " column_id = $value or ";
        }

        //去掉多余的or
        $where = rtrim($where, 'or ');

        return $this->article->getArticleCount($where);
    }

    //获取栏目数据方法
    private function getColumnInfo($where)
    {
        $column_info = $this->column->getColumnInfoToCache($where);
        $this->assign('column', $column_info);
        return $column_info;
    }

    //根据栏目id获取tag列表
    private function getTagList($column_list)
    {

        //实例化tag实例
        $tag = model('Tag');

        //获取本栏目和子栏目的tag标签列表
        $tag_list = [];

        //循环读取tag列表数据
        foreach ($column_list as $value) {

            //构建查询tag标签条件
            $w = ['column_id' => $value];

            //查询tag列表
            $list = $tag->getTagList($w, ' id,tag_name ');
            foreach ($list as $value) {
                $tag_list[] = "<a href='/tag_list?id=$value[id]' title='$value[tag_name]' hreflang='zh' target='_blank'>$value[tag_name]</a>";
            }
            unset($w);
            unset($list);
        }

        return $tag_list;
    }

    //获取栏目文档列表
    public function getArticle($column_id, $field = ' * ', $limit = 1000, $order = ' id desc ')
    {
        $where = ['column_id' => $column_id];
        return $this->getArticleList($where, $field, $limit, $order);
    }

    //分配常用数据
    public function assignDate()
    {
        //获取系统设置参数
        $hoststie_info = config::get('cfg_hoststie_info');
        $hoststie_icp = config::get('cfg_hoststie_icp');

        //分配数据到页面
        $this->assign('host_info', $hoststie_info);
        $this->assign('host_icp', $hoststie_icp);
    }

    //写入数据到redis内
    private function setRedis($key, $value)
    {
        $this->redis->set($key, json_encode($value, JSON_UNESCAPED_UNICODE), 30);
    }

    //获取redis内数据
    private function getRedis($key)
    {
        $info = $this->redis->get($key);
        if (empty($info)) {
            return json_decode($info, true);
        } else {
            return [];
        }
    }

    //计算百分比

    /**
     * @param $num1
     * @param $num2
     * @return float|int
     */
    public function getPercentum($num1, $num2)
    {
        return floor(($num1 / $num2) * 100);
    }

    //设置执行信息方法

    /**
     * @param $token
     * @param $percentum
     * @param $info
     */
    public function setInfo($token, $percentum, $info)
    {
        $a = [
            'num' => $percentum,
            'info' => $info
        ];
        $this->redis->set($token, json_encode($a, JSON_UNESCAPED_UNICODE), 3600);
    }

    //生成tag静态页面方法
    public function htmlTag($id){
        $tag = new \app\admin\model\Tag();
        $html = new \app\index\controller\Html();
        //查询tag标签信息
        $tag_info = $tag->getTagInfo(['id'=>$id] ,' * ');
        $this->assign('tag' ,$tag_info);
        //根据tagid查询文档列表
        $tag_list = $tag->getTagListInfo(['l.tag_id'=>$id] ,' * ');
        $column = new \app\admin\model\Column();
        $column_list_arr = $column->getColumnList([] ,' id,type_name ');;
        $column_list = array_column($column_list_arr ,'type_name' ,'id');
        $this->assign('column' ,$column_list);
        //判断文档数量
        $article_num = count($tag_list);
        $rol = config::get('cfg_list_max_num');
        $article = new \app\admin\model\Article();
        $index = new \app\index\controller\Html();
        if($article_num > $rol){
            //计算总页数
            $limits = ceil($article_num/$rol)+1;
            for($i=1 ;$i<$limits;$i++){
                $limit = ($i-1)*$rol .',' .$rol;
                $article_list = [];
                foreach($tag_list as $value){
                    $list[] = $article->getArticleInfo(['id'=>$value['article_id'] ,'is_delete'=>1] ,' id,title,litpic,column_id,pubdate ',$limit);
                    foreach($list as $key => $v){
                        $list[$key]['column'] = $column_list[$v['column_id']];
                    }
                    $article_list += $list;
                }
                //实例化分页类
                $this->assign('article' ,$article_list);
                $list_rule = $index->getTagUrl($tag_info['tag_name'] ,$tag_info['column_id'],$i);
                $paging = new Page($article_num, $rol, 5, [], false, $list_rule, $i);
                $this->assign('paging' ,$paging);
                $this->fetch('./templates/scz/tag_list.html');
                $html_data = $this->fetch('./templates/scz/tag_list.html');
                $this->assign('paging' ,$paging->render());
                if(isset($article_list[0])){
                    $tag_file = $html->getTagUrl($tag_info['tag_name'] ,$article_list[0]['column_id'] ,1);
                    makeHtml($html_data ,'./tag' ,$tag_file);
                }
            }

        }else{
            $article_list = [];
            foreach($tag_list as $value){
                $list[] = $article->getArticleInfo(['id'=>$value['article_id'] ,'is_delete'=>1] ,' id,title,litpic,column_id,pubdate ');
                foreach($list as $key => $v){
                    $list[$key]['column'] = $column_list[$v['column_id']];
                }
                $article_list = $list;
            }
            //实例化分页类
            $this->assign('article' ,$article_list);
            $list_rule = $index->getTagUrl($tag_info['tag_name'] ,$tag_info['column_id']);
            $paging = new Page($article_num, $rol, 5, [], false, $list_rule, 1);
            $this->assign('paging' ,$paging->render());
            $html_data = $this->fetch('./templates/scz/tag_list.html');
            if(isset($article_list[0])){
                $tag_file = $html->getTagUrl($tag_info['tag_name'] ,$article_list[0]['column_id'] ,1);
                makeHtml($html_data ,'./tag' ,$tag_file);
            }

        }
    }

    //生成tag首页方法
    public function htmlTagDefault()
    {
        $tag = new \app\admin\model\Tag();
        $html = new \app\index\controller\Html();
        $tag_list = $tag->getTagList([], ' id,tag_name,total,column_id,total ');
        foreach ($tag_list as $key => $value) {
            $tag_file = $html->getTagUrl($value['tag_name'], $value['column_id'], 1);
            $tag_list[$key]['file'] = $tag_file;
        }
        $this->assign('tag_list', $tag_list);
        $html_data = $this->fetch('./templates/scz/tag_index.html');
        makeHtml($html_data, './tag', 'index.html');
    }

    //根据文档id和命名规则计算文档文件名

    /**
     * @param $article
     * @param $namerule
     * @param $time
     * @return mixed|string
     */
    public function getArticleFileName($article, $namerule, $time)
    {
        //将文档命名规则字符串处理成小写
        $namerule = strtolower($namerule);
        //取出年月日和文档id存入数组
        $name_info = [
            '{y}' => date('Y', $time),
            '{m}' => date('m', $time),
            '{d}' => date('d', $time),
            '{aid}' => $article,
            '_{page}' => ''
        ];

        //循环替换文档名规则内容
        foreach ($name_info as $key => $value) {
            $namerule = str_replace($key, $value, $namerule);
        }

        //返回处理后的文档文件名
        return $namerule;
    }

    /**
     * @param int $article_id 文档id
     * @param int $column_id 栏目id
     * @return bool|string 文档信息
     * Description 获取上一篇文档
     */
    public function getPreviousArticleInfo($article_id = 0 ,$column_id = 0){
        //组合redis储存的key
        $key = '__article_previous' .$article_id;
        $res = $this->redis->get($key);
        $where = [
            'id'=>[
                '<',
                $article_id
            ],
            'column_id' => $column_id,
            'is_delete'=>1,
            'is_audit'=>1
        ];
        if(empty($res)){
            $res = Model('article')->getAll($where,'id,title,litpic',1,'id desc');
            if(empty($res)){
                $res[0] = [
                    'title'=>'暂无文档',
                    'litpic'=>'/public/png/article.png',
                    'id'=>0
                ];
            }
            //处理标题长度
            $res[0]['title'] = cut_str($res[0]['title'] ,8);
            $this->redis->set($key,json_encode($res,JSON_UNESCAPED_UNICODE),10);
            return $res;
        }else{
            return json_decode($res,true);
        }
    }

    /**
     * @param int $article_id 文档id
     * @param int $column_id 栏目id
     * @return bool|string 文档信息
     * Description 获取下一篇文档
     */
    public function getNextArticleInfo($article_id = 0 ,$column_id = 0){
        //组合redis储存的key
        $key = '__article_next' .$article_id;
        $res = $this->redis->get($key);
        $where = [
            'id'=>[
                '>',
                $article_id
            ],
            'column_id' => $column_id,
            'is_delete'=>1,
            'is_audit'=>1
        ];
        if(empty($res)){
            $res = Model('article')->getAll($where,'id,title,litpic',1,'id asc');
            if(empty($res)){
                $res[0] = [
                    'title'=>'暂无文档',
                    'litpic'=>'/public/png/article.png',
                    'id'=>0
                ];
            }
            //处理标题长度
            $res[0]['title'] = cut_str($res[0]['title'] ,8);
            $this->redis->set($key,json_encode($res,JSON_UNESCAPED_UNICODE),10);
            return $res;
        }else{
            return json_decode($res,true);
        }
    }

    //获取热门文档

    /**
     * @param $columen_id
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    private function getHotArticle($columen_id){
        //获取栏目及子栏目
        $column_list = $this->getSonColumn($columen_id ,' id ');
        $column_list[] = $columen_id;
        //获取文档数据
        //循环拼接查询条件
        $where = '';
        foreach ($column_list as $value){
            $where .= " column_id = $value or ";
        }

        //去掉多余的or
        $where = rtrim($where ,'or ');

        //查询文档列表
        $article_list = $this->getArticleList($where ,' * ' ,20 ,' click desc ');

        //返回文档数据
        return $article_list;
    }

    //获取热门标签

    /**
     * @param $columen_id
     * @param bool $is_son
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getHotTag($columen_id ,$is_son = false){
        if(!$is_son){
            $where = ['column_id'=>$columen_id];
        }else{
            $column_list = Model('column')->getAll(['parent_id'=>['neq',0]],'id,parent_id');
            $column_son_list = $this->getSonList($columen_id,$column_list);
            $column_son_list[] = $columen_id;
            //获取栏目及子栏目
            $where = [
                'column_id'=>[
                    'in',
                    $column_son_list
                ]
            ];
        }
        $hot_tag_list = Model('tag')->getTagList($where ,' id,tag_name ' ,20 ,' count desc ');
        return $hot_tag_list;
    }

}