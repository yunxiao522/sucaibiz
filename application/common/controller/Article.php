<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/17 0017
 * Time: 20:00
 */

namespace app\common\controller;


use app\common\model\Tag;
use SucaiZ\config;
use think\Request;

class Article extends BaseController
{
    private static $redis;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        self::$redis = getRedis();
    }

    /**
     * @param $column_id 栏目id
     * @param $article_title 文档标题
     * @return string 文档位置信息
     * Description 获取文档位置信息
     */
    public static function getArticleSite($column_id, $article_title,$channel_type){
        //获取文档所属栏目及父级栏目信息
        $column_list = Model('column')->getAll(['channel_type'=>$channel_type], ' id,parent_id,type_name,type_dir,defaultname ');
        $column_arr = self::getParentColumnInfo($column_id,$column_list);
        //添加首页和文档到数组中
        krsort($column_arr);
        array_unshift($column_arr, '<a href="/" title="首页">首页</a>');
        array_push($column_arr, $article_title);
        //文档位置字符串
        return implode(config::get('cfg_list_symbol'), $column_arr);
    }

    /**
     * @param $column_id
     * @param $column_list
     * @return array
     * Description 获取栏目的父级只到最顶层
     */
    private static function getParentColumnInfo($column_id,$column_list)
    {
        $column_arr = [];
        foreach ($column_list as $key => $value) {
            if ($value['id'] == $column_id) {
                $column_path = rtrim(str_replace('{cmspath}', '', $value['type_dir']), '/') . '/';
                $url = $column_path . $value['defaultname'];
                $column_arr[] = "<a href='$url' title='$value[type_name]'>$value[type_name]</a>";
                $value['type_name'];
                if ($value['parent_id'] != 0) {
                    self::getParentColumnInfo($value['parent_id'],$column_list);
                }
            }
        }
        return $column_arr;
    }

    /**
     * @param string $article_id 文档id
     * @param $article_info 文档信息
     * @param $column_info 文档所属栏目信息
     * @return array 小图数组
     * Description 获取图集小图信息
     */
    public static function getSmallimgurlsInfo($article_id = '',$article_info ,$column_info){
        //匹配src图片路径方法
        $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
        $smallimgurls = explode(',', $article_info['smallimgurl']);
        $smallimgurls_list = [];
        //取出年月日和文档id存入数组
        $name_info = [
            '{y}' => date('Y', $article_info['pubdate']),
            '{m}' => date('m', $article_info['pubdate']),
            '{d}' => date('d', $article_info['pubdate']),
            '{aid}' => $article_id,
        ];
        $page = 1;
        if($smallimgurls[0] == ''){
            $smallimgurls = [];
        }
        //循环存储数据
        foreach ($smallimgurls as $value) {
            $namerule = strtolower($column_info['namerule']);
            //取出图像地址
            preg_match($src_rule, $value, $match);
            $a['imgurl'] = $match[3];
            //获取文档的创建时间
            $create_time = $article_info['pubdate'];
            if($page == 1){
                $page='';
            }else{
                $page='_' .$page;
            }
            //循环替换文档名规则内容
            $namerule = str_replace( ['{y}', '{m}', '{d}', '{aid}','_{page}'],[date('Y' ,$create_time),date('m' ,$create_time),date('d' ,$create_time),$article_info['article_id'],$page] ,$namerule);
            //过滤文件名规则无用字符
            $namerule = ltrim($namerule ,'{typedir}/');
            //拼接访问路径
            $a['url'] = $column_info['type_dir'] . '/'.$namerule;
            $smallimgurls_list[] = $a;
            $page++;
        }
        return $smallimgurls_list;
    }

    /**
     * @param $article_id 文档id
     * @return array tag列表
     * Description 获取文档tag标签
     */
    public static function getArticleTag($article_id)
    {
        //获取文档的tag列表
        $tag_list = Model('tag_list')->getAll(['article_id'=>$article_id],'tag_id');
        //循环列表数据
        $list = [];
        $tag = new Tag();
        foreach($tag_list as $key => $value){
            $list[] = $tag->getOne(['id'=>$value['tag_id']],'*');
        }
        return $list;
    }

    /**
     * @param $article_id 文档id
     * @param $column_id 栏目id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getConcernArticleList($article_id, $column_id,$state = false)
    {
        //组合查询条件
        $where = [
            'id'=>[
               '<',
                $article_id
            ],
            'column_id'=>$column_id
        ];
        $list = Model('article')->getAll($where, 'id,title,litpic', '20', 'id desc');
        //循环列表数据
        foreach($list as $key => $value){
            $list[$key]['url'] = self::getArticleUrl($value['id'],0,$state,false);
        }
        return $list;
    }

    /**
     * @param int $article_id 文档id
     * @param int $column_id 栏目id
     * @return bool|string 文档信息
     * Description 获取上一篇文档
     */
    public static function getPreviousArticleInfo($article_id = 0 ,$column_id = 0){
        //组合redis储存的key
        $key = '__article_previous' .$article_id;
        $res = self::$redis->get($key);
        if(empty($res)){
            $where = [
                'id'=>[
                    '<',
                    $article_id
                ],
                'column_id' => $column_id,
                'is_delete'=>1,
                'is_audit'=>1
            ];
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
            self::$redis->set($key,json_encode($res,JSON_UNESCAPED_UNICODE),10);
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
    public static function getNextArticleInfo($article_id = 0 ,$column_id = 0){
        //组合redis储存的key
        $key = '__article_next' .$article_id;
        $res = self::$redis->get($key);
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
            self::$redis->set($key,json_encode($res,JSON_UNESCAPED_UNICODE),10);
            return $res;
        }else{
            return json_decode($res,true);
        }
    }

    /**
     * @param $imgInfo
     * @param null $page
     * @return array|mixed
     * Description 获取图集图片信息方法
     */
    public static function getImgInfo($imgInfo ,$page = null){
        if(empty($imgInfo)){
            return '';
        }
        //匹配src图片路径方法
        $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
        $img_arr = explode(',',$imgInfo);
        $img = [];
        if($page === null){
            foreach($img_arr as $key => $value){
                preg_match($src_rule,$value,$match);
                $img[] = $match[3];
            }
            return $img_arr;
        }else{
            if(!isset($img_arr[$page])){
                $img[] = '';
            }else{
                preg_match($src_rule,$img_arr[$page],$match);
                $img[] = $match[3];
            }
            return $img[0];
        }
    }

    /**
     * @param $where
     * @param $field
     * @param $limit
     * @param $order
     * @param bool $state
     * @return mixed
     * Description 获取文档列表数据
     */
    public static function getArticleList($where ,$field ,$limit ,$order,$state = false){
        $list = Model('article')->getAll($where,$field,$limit,$order);
        foreach($list as $key => $value){
            $list[$key]['url'] = self::getArticleUrl($value['id'],1,$state,false);
        }
        return $list;
    }
}