<?php

namespace app\index\controller;

use app\common\controller\BaseController;
use app\common\controller\Column;
use app\index\model\Click;
use app\index\model\Tag;
use SucaiZ\config;
use think\Request;
use think\View;
use app\common\controller\Article;
use app\common\controller\Html;

class Index extends BaseController
{
    //模板存储目录
    public $templates_path = '';
    //文档附加表信息
    public $article_affiliate_table = [1 => 'article_body', 2 => 'article_images', 3 => 'article_album', 4 => 'article_resource'];
    //栏目接个福
    public $list_symbol = '';
    public $cookie_name = 'member_info';
    public $member_info = '';
    //储存article实例
    private $article;
    //存储column实例
    private $column;
    //存储tag实例
    private $tag;
    //存储Html类
    private $html;
    private $article_click_key = 'article_click_id';

    public function __construct()
    {
        parent::__construct();
        $this->templates_path = './' . config::get('cfg_template_file_path');
        $this->list_symbol = config::get('cfg_list_symbol');

        $this->article = new Article();
        $this->column = new Column();
        $this->tag = new Tag();
        $this->html = new Html();
    }

    //分配常用数据
    public function assignDate()
    {
        //获取系统设置参数
        $hoststie_info = config::get('cfg_hoststie_info');
        $hoststie_icp = config::get('cfg_hoststie_icp');
        $hoststie_keywords = config::get('cfg_hostsite_keywords');
        $hoststie_description = config::get('cfg_hostsite_description');

        //分配数据到页面
        $this->assign('host_info', $hoststie_info);
        $this->assign('host_icp', $hoststie_icp);
        $this->assign('host_keywords', $hoststie_keywords);
        $this->assign('host_description', $hoststie_description);
    }

    public function index()
    {
        //分配常用数据到页面
        $this->assignDate();
        //获取系统参数设置
        $temp_path = './' . config::get('cfg_template_file_path');
        $host_default = config::get('cfg_host_default_temp_name');
        $temp_default_file = $temp_path . $host_default;
        if (!file_exists($temp_default_file)) {
            dump('模板文件不存在');
        } else {
            return $this->fetch($temp_default_file);
        }
    }

    //下载文件方法
    public function down()
    {
        return View();
    }

    //文档展示方法
    public function article()
    {
        //判断是否有参数输入
        $id = input('id');
        //不存在、参数为空、文档id不是数字
        if (empty($id) || !is_numeric($id)) {
            //跳转到网站首页
            header('location:/');
            die;
        }
        //获取文档数据
        $w = ['id' => $id];
        $article_info = Model('article')->getOne($w, 'id');
        //文档不存在，跳转至首页
        if (empty($article_info)) {
            header('location:/');
            die;
        }
        $page = input('page');
        if (empty($page) || !is_numeric($page)) {
            $page = 1;
        }
        if ($page <= 0) {
            $page = 1;
        }
        echo Html::htmlArticle($id, $page, false);
        die;
    }

    //文档列表展示方法
    public function showList()
    {
        //判断是否有参数输入
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            header('location:/');
        }

        $page = input('page');
        if (!isset($page) || empty($page) || !is_numeric($page)) {
            $page = 1;
        }

        $column_info = Model('column')->getOne(['id' => $id], '*');
        if (empty($column_info)) {
            header('location:/');
        }

        $rol = input('rol');
        if (!isset($rol) || empty($rol) || !is_numeric($rol)) {
            $rol = config::get('cfg_list_max_num');
        } else if ($rol > config::get('cfg_list_max_num')) {
            $rol = config::get('cfg_list_max_num');
        }
        echo Html::htmlColumn($id, $page, false, $rol);
        die;
    }

    //展示tag页面
    public function tag()
    {
        //判断是否有参数输入
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            echo '';die;
        }
        $tag = new \app\admin\model\Tag();
        $html = new \app\index\controller\Html();
        $tag_list = $tag->getTagList([], ' id,tag_name,total,column_id,total ');
        foreach ($tag_list as $key => $value) {
            $tag_file = $html->getTagUrl($value['tag_name'], $value['column_id'], 1);
            $tag_list[$key]['file'] = $tag_file;
        }
        $this->assign('tag_list', $tag_list);
        return $this->fetch('./templates/scz/tag_index.html');
    }

    //获取文档列表方法
    public function getArticleList($where, $field = ' * ', $limit = 20, $order = ' id desc ')
    {
        //取出文档列表
        return Article::getArticleList($where, $field, $limit, $order, true);
    }

    /**
     * @param $column_id
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * Description 根据栏目id获取栏目及子栏目文档列表
     */
    public function getArticleToColumnId($column_id, $field = ' * ', $limit = 1000, $order = ' id desc ')
    {
        $column_list = Model('column')->getAll([
            'parent_id' => [
                'neq',
                0
            ]
        ], 'id,type_name,parent_id');
        //获取栏目及子栏目
        $column_son_list = self::getSonList($column_id, $column_list);
        $column_son_list[] = $column_id;
        //组合查询条件获取数据
        $where = [
            'column_id' => [
                'in',
                $column_son_list
            ],
            'is_delete' => 1,
            'is_audit' => 1,
            'draft' => 2
        ];
        return $this->getArticleList($where, $field, $limit, $order);
    }
    //根据栏目id获取查询子栏目信息的where

    /**
     * @param $column
     * @return string
     */
    public function getWhere($column)
    {
        $column_list = Model('column')->getAll([
            'parent_id' => [
                'neq',
                0
            ]
        ]);
        //获取栏目及子栏目
        $column_son_list = self::getSonList($column, $column_list);
        $column_son_list[] = $column;
        $where = [
            'column_id' => [
                'in',
                $column_son_list
            ],
            'is_delete' => 1,
            'is_audit' => 1,
            'draft' => 2
        ];
        return $where;
    }

    //获取图集颜色列表
    public function getArticleColor()
    {

    }

    //更新文档点击数方法
    public function incr()
    {
        if (Request::instance()->isPost()) {
            $id = input('id');
            if (isset($id) && is_numeric($id)) {
                $where = ['id' => $id];
                $article = model('Article');
                $article->incrArticle($where);
            }
        }
    }

    //更新点击量
    public function click()
    {
        if (Request::instance()->isPost()) {
            //获取当前日期
            $day = date('Y-m-d');
            //组合查询条件
            $where = ['day' => $day];
            //查询是否已经有当前日期数据
            $click = new Click();
            $count = $click->getCount($where);
            if ($count == 0) {
                //初始化数据
                $arr = [
                    'day' => $day,
                    'click' => 0,
                    'create_time' => strtotime($day)
                ];
                //添加数据
                $click->add($arr);
            } else {
                //增加点击率
                $click->incrClick($where);
            }
        }
    }

    public function version()
    {
        $star = date('Y', strtotime('2014-1-1'));
        $end = date('Y');
        $list = [];
        for ($i = $end; $i >= $star; $i--) {
            $where = [
                'pubdate' => [
                    [
                        '>',
                        $i
                    ],
                    [
                        '<',
                        $i + 1
                    ],
                    'and'
                ]
            ];
            $info = Model('versions')->getAll($where);
            if (!empty($info)) {
                $list[] = [
                    'year' => $i,
                    'info' => $info
                ];
            }

        }
        View::share('info', $list);
        return view('templates/version');
    }

    /**
     * Description 记录详细访问记录
     */
    public function visit()
    {
        if (Request::instance()->isPost()) {
            $useragent = addslashes(strtolower($_SERVER['HTTP_USER_AGENT']));

            if (strpos($useragent, 'googlebot')!== false){$bot = 'Google';}
            elseif (strpos($useragent,'mediapartners-google') !== false){$bot = 'Google Adsense';}
            elseif (strpos($useragent,'baiduspider') !== false){$bot = 'Baidu';}
            elseif (strpos($useragent,'sogou spider') !== false){$bot = 'Sogou';}
            elseif (strpos($useragent,'sogou web') !== false){$bot = 'Sogou web';}
            elseif (strpos($useragent,'sosospider') !== false){$bot = 'SOSO';}
            elseif (strpos($useragent,'360spider') !== false){$bot = '360Spider';}
            elseif (strpos($useragent,'yahoo') !== false){$bot = 'Yahoo';}
            elseif (strpos($useragent,'msn') !== false){$bot = 'MSN';}
            elseif (strpos($useragent,'msnbot') !== false){$bot = 'msnbot';}
            elseif (strpos($useragent,'sohu') !== false){$bot = 'Sohu';}
            elseif (strpos($useragent,'yodaoBot') !== false){$bot = 'Yodao';}
            elseif (strpos($useragent,'twiceler') !== false){$bot = 'Twiceler';}
            elseif (strpos($useragent,'ia_archiver') !== false){$bot = 'Alexa_';}
            elseif (strpos($useragent,'iaarchiver') !== false){$bot = 'Alexa';}
            elseif (strpos($useragent,'slurp') !== false){$bot = '雅虎';}
            elseif (strpos($useragent,'bot') !== false){$bot = '其它蜘蛛';}
            if(isset($bot)){
                return '';
            }
            $data = [];
            $id = input('id');
            if (empty($id) || !is_numeric($id)) {
                return '';
            }
            $data['type'] = input('type');
            $data['url'] = input('url');
            $data['source'] = input('source');
            $data['session_id'] = input('sessionid');
            $data['ip'] = Request::instance()->ip();
            $data['create_time'] = time();
            if ($data['type'] == 1) {
                $data['article_id'] = $id;
                $data['column_id'] = Model('article')->getField(['id' => $id], 'column_id');
            } else if ($data['type'] == 2) {
                $data['article_id'] = 0;
                $data['column_id'] = $id;
            } else if ($data['type'] == 3) {
                $data['article_id'] = 0;
                $data['column_id'] = 0;
            }
            $data['user_id'] = input('uid') ? input('uid') : 0;
            //获取USER AGENT
            $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
            //分析数据
            $is_pc = (strpos($agent, 'windows nt')) ? true : false;
            $is_iphone = (strpos($agent, 'iphone')) ? true : false;
            $is_ipad = (strpos($agent, 'ipad')) ? true : false;
            $is_android = (strpos($agent, 'android')) ? true : false;
            //输出数据
            if ($is_pc) {
                $data['device'] = "pc";
            }
            if ($is_iphone) {
                $data['device'] = "iphone";
            }
            if ($is_ipad) {
                $data['device'] = "ipad";
            }
            if ($is_android) {
                $data['device'] = "android";
            }
            Model('logvisit')->add($data);
            $this->addArticleHotClick($id);
        }
    }

    /**
     * @param $article_id
     * Description 添加点击量
     */
    private function addClik()
    {
        //获取当前日期
        $day = date('Y-m-d');
        //组合查询条件
        $where = ['day' => $day];
        //查询是否已经有当前日期数据
        $click = new Click();
        $count = $click->getCount($where);
        if ($count == 0) {
            //初始化数据
            $arr = [
                'day' => $day,
                'click' => 0,
                'create_time' => strtotime($day)
            ];
            //添加数据
            $click->add($arr);
        } else {
            //增加点击率
            $click->incrClick($where);
        }
    }

    /**
     * @param $article_id
     * Description 添加文档点击量
     */
    private function addArticleClick($article_id){
        $where = ['id'=>$article_id];
        $key = str_replace('key',$article_id,$this->article_click_key);
        $redis = getRedis();
        $num = $redis->get($key);
        if(empty($num)){
            $click = Model('article')->getField($where,'click');
            $click ++ ;
            $redis->set($key,$click,604800);
        }
        $redis->incr($key);
        Model('article')->fieldinc($where,'click');
    }

    /**
     * @param $article_id
     * Description 添加文档相关tag点击量
     */
    private function addArticleTagClick($article_id){
        $where = ['article_id'=>$article_id];
        $tag_list = Model('taglist')->getAll($where,'tag_id');
        foreach($tag_list as $value){
            $where = ['id'=>$value['tag_id']];
            Model('taglist')->filedinc($where,'count');
            Model('taglist')->filedinc($where,'weekcc');
            Model('taglist')->fieldinc($where,'daycc');
            Model('taglist')->fieldinc($where,'monthcc');
        }
    }

    private function addArticleHotClick($article_id){
        $article_info =  Model('article')->getOne(['id'=>$article_id],'pubdate');
        $year = date('Y',$article_info['pubdate']);
        $mounth = date('m',$article_info['pubdate']);
        $where = [
            'type'=>1,
            'time'=>$year
        ];
        $year_id = Model('ArticleHot')->getField($where,'id');
        if(empty($year_id)){
            $year_id = Model('ArticleHot')->add([
                'type'=>1,
                'time'=>$year,
                'click'=>0,
                'create_time'=>time(),
                'parent_id'=>0
            ]);
        }
        Model('ArticleHot')->fieldinc(['id'=>$year_id],'click');
        $mouth_id = Model('ArticleHot')->getField([
            'parent_id'=>$year_id,
            'time'=>$mounth,
            'type'=>2
        ],'id');
        if(empty($mouth_id)){
            $mouth_id = Model('ArticleHot')->add([
                'type'=>2,
                'time'=>$mounth,
                'click'=>0,
                'create_time'=>time(),
                'parent_id'=>$year_id
            ]);
        }
        Model('ArticleHot')->fieldinc(['id'=>$mouth_id],'click');
    }
}
