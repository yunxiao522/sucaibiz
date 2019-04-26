<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/6
 * Time: 14:44
 */

namespace app\common\controller;
use app\common\model\Tag;
use app\common\model\Article;
use app\common\model\Column;
use SucaiZ\config;
use SucaiZ\Pinyin\ChinesePinyin;
use think\Controller;

class Url extends Controller
{
    private $article_state_url = '/article.html';
    private $column_state_url = '/column.html';
    public function __construct()
    {
        parent::__construct();
    }
    //获取文档访问url
    public static function getArticleUrl($aid = 0 ,$state = false ,$full = false,$page = 0){
        if($aid == 0 || !is_numeric($aid)){
            return '';
        }
        //判断获取的链接属性
        if($state){
            $article = new Article();
            $article_info = $article->getArticleInfo(['id'=>$aid]);
            //判断是否单独设置了文件路径
            if(empty($article_info['redirecturl'])){
                $column = new Column();
                $column_info = $column->getColumnInfo(['id'=>$article_info['column_id']]);
                //将文档命名规则字符串处理成小写
                $namerule = strtolower($column_info['namerule']);
                //取出年月日和文档id存入数组
                $name_info = [
                    '{y}' => date('Y', $article_info['pubdate']),
                    '{m}' => date('m', $article_info['pubdate']),
                    '{d}' => date('d', $article_info['pubdate']),
                    '{aid}' => $aid,
                ];
                if(!$page){
                    $name_info['_{page}'] = '';
                }else{
                    $page++;
                    $name_info['_{page}'] = "_$page";
                }
                //循环替换文档名规则内容
                foreach ($name_info as $key => $value) {
                    $namerule = str_replace($key, $value, $namerule);
                }
                //组合文档访问url
                $file = $column_info['type_dir'] . $namerule;
            }else{
                $file = $article_info['redirecturl'];
            }
            if($full){
                return config::get('cfg_hostsite') .rtrim($file ,'/');
            }else{
                return rtrim($file ,'/');
            }
        }else{
            if($full){
                return config::get('cfg_hostsite') .(new static())->article_state_url .'?id=' .$aid;
            }else{
                return (new static())->article_state_url .'?id=' .$aid;
            }
        }
    }
    //获取栏目访问url
    public function getColumnUrl($cid = 0 ,$state = false ,$full = false){
        if($cid == 0 || !is_numeric($cid)){
            return false;
        }
        //判断获取的链接属性
        if($state){
            //根据栏目id获取栏目信息
            $column = new Column();
            $column_info = $column->getColumnInfo(['id'=>$cid]);
            //组合栏目访问url
            $file= $column_info['type_dir'] .'/' .$column_info['defaultname'];
            if($full){
                return config::get('cfg_hostsite') .$file;
            }else{
                return $file;
            }
        }else{
            if($full){
                return config::get('cfg_hostsite') .$this->column_state_url .'?id=' .$cid;
            }else{
                return $this->column_state_url .'?id=' .$cid;
            }
        }
    }
    //获取tag标签访问url
    public function getTagUrl($tagid = 0 ,$state = false ,$full = false){
        if($tagid == 0 || !is_numeric($tagid)){
            return false;
        }
        //判断获取的链接属性
        if($state){
            //根据tagid获取tag信息
            $tag = new Tag();
            $chinesepinyin = new ChinesePinyin();
            $tag_info = $tag->getTagInfo(['id'=>$tagid]);
            //获取tag标签汉语拼音字符串
            $tag_name = $tag_info['tag_name'];
            $tag_name = $chinesepinyin->TransformWithoutTone($tag_name ,'');
            //组合tag访问的url
            $file = '/tag/' .$tag_name .'.html';
            if($full){
                return config::get('cfg_hostsite') .$file;
            }else{
                return $file;
            }

        }else{
            if($full){
                return config::get('cfg_hostsite') .'/tags?id=' .$tagid;
            }else{
                return '/tags?id=' .$tagid;
            }
        }
    }
}