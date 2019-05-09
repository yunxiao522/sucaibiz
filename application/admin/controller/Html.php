<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/16
 * Time: 12:42
 * Description：生成HTML静态文件控制器,只做路基处理，数据处理在make控制器
 */

namespace app\admin\controller;

use SucaiZ\config;
use think\Request;

class Html extends Common
{

    //错误信息
    public $error = '';
    //模板存储目录
    public $templates_path = '';
    //文档附加表信息
    public $article_affiliate_table = [1 => 'article_body', 2 => 'article_images', 3 => 'article_album', 4 => 'article_resource'];
    //栏目接个福
    public $list_symbol = '';
    //存储article实例
    private $article;
    //储存column实例
    private $column;
    //存储redis实例
    private $redis;
    //存储操作执行信息
    private $info;
    //储存tag实例
    private $tag;
    //储存html实例
    private $html;
    //存储make实例
    private $make;
    //储存单页文档条数
    private $rol;

    public function __construct()
    {
        parent::__construct();
        $this->templates_path = './' . config::get('cfg_template_file_path');
        $this->list_symbol = config::get('cfg_list_symbol');
        //实例化article
        $this->article = new \app\admin\model\Article();
        //实例化column
        $this->column = new \app\admin\model\Column();
        //获取redis实例
        $this->redis = getRedis();
        //实例化tag
        $this->tag = new \app\admin\model\Tag();
        //实例化html
        $this->html = new \app\index\controller\Html();
        //实例化make
        $this->make = new Make();
        //获取单页文档条数
        $this->rol = config::get('cfg_list_max_num');
    }

    //生成TAG show页面
    public function makeTagShow($where = [])
    {
        $tag = model('Tag');
        if (Request::instance()->isPost()) {

        } else {
            return View('Html_tag');
        }
    }

    //生成网站主页
    public function makeIndex(){
        if(Request::instance()->isPost()){
            $type = input('type');

            if(!isset($type)){
                echo '非法访问';die;
            }
            $this->make->htmlIndex($type);
            $a = [
                'errorcode'=>0,
                'msg'=>'生成成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            return View('Html_index');
        }

    }



    //生成TAG list页面
    public function makeTagList()
    {
        //生成tag首页
        $this->make->htmlTagDefault();
        //生成tag列表方法
        $tag = new \app\admin\model\Tag();
        $tag_list = $tag->getTagList([] ,'id');
        foreach($tag_list as $value){
            $this->make->htmlTag($value['id']);
        }
        $a = [
            'errorcode'=>0,
            'msg'=>'更新成功'
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //单独生成tag列表页面
    public function makeTagListOne(){
        $tag_id = input('id');
        if(!isset($tag_id)){
            echo '非法访问';die;
        }
        $this->make->htmlTag($tag_id);
        $a = [
            'errorcode'=>0,
            'msg'=>'生成成功'
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //生成栏目列表html页面
    public function makeColmunHtml()
    {
        //设置不超时，程序一直运行。
        set_time_limit(0);

        //即使Client断开(如关掉浏览器),PHP脚本也可以继续执行.
        ignore_user_abort(true);
        if (Request::instance()->isPost()) {

            //验证数据
            $column = input('column');
            if (!isset($column) || empty($column)) {
                echo '非法访问';
                die;
            }
            $type = input('type');
            if (!isset($type) || empty($type) || !is_numeric($type)) {
                echo '非法访问';
                die;
            }
            $num = input('num');
            if (!isset($num) || empty($num) || !is_numeric($num)) {
                echo '非法访问';
                die;
            }
            $token = input('token');
            if (!isset($token) || empty($token)) {
                echo '非法访问';
                die;
            }
            $this->htmlColumn($column ,$num ,$token ,$type);
            $this->setInfo($token, 100, "生成完成");
        } else {
            //获取操作token标识
            $token = getArticleToken();
            //获取栏目分类
            $column = model('Column');
            $column_list = $column->getColumnList([], 'id,type_name,parent_id');
            $column_list = getarticletype($column_list);
            foreach ($column_list as $key => $value) {
                $type_name_prefix = '└';
                for ($i = 0;
                     $i <= $value['lev'];
                     $i++) {
                    $type_name_prefix .= '─ ';
                }

                $column_list[$key]['type_name'] = $type_name_prefix . $value['type_name'];
            }
            //分配操作token到页面
            $this->assign('token', $token);
            //分配栏目数据
            $this->assign('column', $column_list);
            return View('Html_column');
        }

    }

    //生成文档页面
    public function makeArticle()
    {
        //设置不超时，程序一直运行。
        set_time_limit(0);

        //即使Client断开(如关掉浏览器),PHP脚本也可以继续执行.
//        ignore_user_abort(true);
        if (Request::instance()->isPost()) {
            $start_time = microtime(true);
            //验证数据
            $column = input('column');
            if (!isset($column) || empty($column)) {
                echo '非法访问';
                die;
            }
            $start = input('start');
            if (!isset($start) || !is_numeric($start)) {
                echo '非法访问';
                die;
            }
            $end = input('end');
            if (!isset($end) || !is_numeric($end)) {
                echo '非法访问';
                die;
            }
            if(!empty($start)){
                if(empty($end)){
                    return $this->ajaxError('输入的结束id不能为0');
                }
                if($end <= $start){
                    return $this->ajaxError('输入的结束id必须大于开始id');
                }
            }
            $token = input('token');
            if (!isset($token) || empty($token)) {
                echo '非法访问';
                die;
            }
            //根据栏目id获取栏目文档类型
            $column_arr = $this->column->getColumnInfo(['id' => $column], ' channel_type ');
            $sum = $this->make->getArticleSum($column ,$column_arr['channel_type']);

            //获取栏目信息
            $column_info = Model('column')->getOne(['id'=>$column],'channel_type');
            if(empty($column_info)){
                return $this->ajaxError('栏目不存在');
            }
            //获取栏目列表
            $column_list = Model('column')->getAll(['channel_type'=>$column_info['channel_type']],' id,parent_id ');
            $son_column_list = $this->getSonList($column,$column_list);
            $son_column_list[] = (int)$column;
            $where = [
                'column_id'=>[
                    'in',
                    $son_column_list
                ],
                'is_delete'=>1
            ];
            if(!empty($start) && !empty($end)){
                $where['id'] = [
                    [
                        '<',
                        $end
                    ],
                    [
                        '>',
                        $start
                    ]
                ];
            }
            //获取文档列表
            $article_list = Model('article')->getAll($where, ' id,channel ', 10000);
            $i = 0;
            //循环生成文档文件
            foreach ($article_list as $key =>  $value) {
                $i++;
                $this->make->htmlArticle($value['id'] ,$value['channel'] ,$sum ,$i ,$token);
            }
            $end_time = microtime(true);
            $times = round(($end_time-$start_time),3);
            $this->setInfo($token, 100, "生成完成");
            return $this->ajaxOkdata(['time'=>$times]);
        } else {
            //获取操作token标识
            $token = getArticleToken();
            //获取栏目分类
            $column = model('Column');
            $column_list = $column->getColumnList([], 'id,type_name,parent_id');
            $column_list = getarticletype($column_list);
            foreach ($column_list as $key => $value) {
                $type_name_prefix = '└';
                for ($i = 0;
                     $i <= $value['lev'];
                     $i++) {
                    $type_name_prefix .= '─ ';
                }

                $column_list[$key]['type_name'] = $type_name_prefix . $value['type_name'];
            }
            //分配操作token到页面
            $this->assign('token', $token);
            //分配栏目数据
            $this->assign('column', $column_list);
            return View('Html_article');
        }
    }

    //生成栏目方法
    public function htmlColumn($column ,$num ,$token ,$type)
    {
        //获取栏目列表
        $column_list = $this->make->getSonColumn($column);
        //生成本栏目文件
        $res = $this->make->htmlColumn($column ,$num ,$token ,1 ,$num);
        if(!$res){
            return true;
        }
        //判断是否需要生成子栏目
        if ($type == 1) {
            foreach ($column_list as $value) {
                $res = $this->make->htmlColumn($value ,$res['num'] ,$token ,$res['j'] ,$num);
            }
        }
        $this->setInfo($token, 100, "生成完成");
        return true;
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

    //获取执行状态信息
    public function getInfo()
    {
        //验证数据
        $token = input('token');
        if (!isset($token) || empty($token)) {
            echo '非法访问';
            die;
        }
        //判断token在redis中是否存在
        $info = $this->redis->get($token);
        if (!empty($info)) {
            $info_arr = json_decode($info, true);
            $a = [
                'errorcode' => 0,
                'num' => $info_arr['num'],
                'info' => $info_arr['info'],
                'msg' => '获取数据成功'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 1,
                'msg' => '访问参数错误'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //生成rss文件方法
    public function makeRss(){
        $article = new \app\admin\model\Article();
        //查询桌面壁纸和素材资讯
        $column_list = Model('column')->getAll([],'id,parent_id',10000);
        $bz_column_list = $this->getSonList(1,$column_list);
        $zx_column_list = $this->getSonList(24,$column_list);
        $id_arr = $bz_column_list + $zx_column_list;
        array_push($id_arr,1,24);
        $where = [
            'column_id'=>[
                'in',
                $id_arr
            ]
        ];
        $article_list = $article->getArticleListToCache($where ,' id,title,column_id,pubdate' ,10000000);
        foreach($article_list as $key => $value){
            $article_list[$key]['url'] = 'http://www.sucai.biz' .geturl($value['pubdate'] ,$value['id'] ,1 ,$value['column_id']);
            $article_list[$key]['time'] = date('r',$value['pubdate']);
        }
        $this->assign('article' ,$article_list);
        $html_data = $this->fetch('./templates/default/rss.xml');
        makeHtml($html_data ,'./' ,'rss.xml');
        $a = [
            'errorcode'=>0,
            'msg'=>'更新成功'
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //单独生成文档html页面方法
    public function makeArticleOne(){
        $article = input('article');
        if(!isset($article)){
            $a = [
                'errorcode'=>1,
                'msg'=>'非法访问'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        $channel_arr = $this->article->getArticleInfo(['id'=>$article] ,' channel ');
        $make = new make();
        //生成文档html页面
        $make->htmlArticle($article ,$channel_arr['channel'] ,1,1,1);
        $a = [
            'errorcode'=>0,
            'msg'=>'生成成功'
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //单独生成栏目html页面方法
    public function makeColumnOne(){
        $column = input('column');
        if(!isset($column)){
            $a = [
                'errorcode'=>1,
                'msg'=>'非法访问'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        $make = new make();
        //生成所属栏目首页及列表页
        $make->htmlColumn($column, 10000, 1, 1, 1);
        $a = [
            'errorcode'=>0,
            'msg'=>'生成成功'
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //genjwendangod生成tag标签页
    public function makeTagToArticle($article_id){
        $tag_list = $this->tag->getTagListInfo(['article_id'=>$article_id] ,'tag_id');
        foreach($tag_list as $value){
            $this->make->htmlTag($value['tag_id']);
        }
        $this->make->htmlTagDefault();
    }
}
