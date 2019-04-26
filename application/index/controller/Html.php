<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/23
 * Time: 17:39
 * Description:
 */

namespace app\index\controller;


use think\Controller;
use app\index\model\Article;
use app\index\model\Column;
use SucaiZ\Pinyin\ChinesePinyin;
use SucaiZ\config;

class Html extends Controller
{
    //存储article实例
    private $article;
    //存储column实例
    private $column;
    //存放redis实例
    private $redis;
    //构造函数

    /**
     * Html constructor.
     */
    public function __construct()
    {
        parent::__construct();
        //实例化article
        $this->article = new Article();
        //实例化column
        $this->column = new Column();
        //获取redis实例
        $this->redis = getRedis();
    }

    //根据文档id获取文档访问地址

    /**
     * @param $article_id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getArticleUrl($article_id)
    {
        //获取栏目id和模板文件信息
        $article_info = $this->getArticleToId($article_id, ' column_id,pubdate ');
        //获取栏目目录和栏目模板信息
        $column_info = $this->getColumnToId($article_info['column_id'], ' type_dir,namerule ');

        //返回组合后的文件名
        return $column_info['type_dir'] . $this->getArticleFileName($article_id, $column_info['namerule'], $article_info['pubdate']);
    }

    //根据栏目id获取栏目首页访问路径

    /**
     * @param $column_id
     * @param int $page
     * @return string
     */
    public function getColumnUrl($column_id, $page = 1)
    {
        //根据栏目id获取栏目信息
        $column_info = $this->column->getColumnInfo(['id' => $column_id], ' type_dir,defaultname,listrule ');
        if ($page == 1) {
            return $column_info['type_dir'] . '/' . $column_info['defaultname'];
        } else {
            return $column_info['type_dir'] . '/' . $this->getColumnFileName($column_id, $column_info['listrule'], $page);
        }
    }

    //根据栏目id获取栏目信息

    /**
     * @param $column_id
     * @param string $field
     * @return array|bool|false|\PDOStatement|string|\think\Model
     */
    private function getColumnToId($column_id, $field = ' * ')
    {
        $column_info = $this->getRedis('c' . $column_id);
        if ($column_info) {
            return $column_info;
        } else {
            $column_info = $this->column->getColumnInfo(['id' => $column_id], $field);
            //将数据存储redis
            $this->setRedis('c' . $column_id, $column_info);
        }
        return $column_info;
    }

    //根据文档id获取文档信息

    /**
     * @param $article_id
     * @param string $field
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getArticleToId($article_id, $field = ' * ')
    {
        //先查看redis内是否存在.不存在则到数据库中获取
        $article_info = $this->getRedis('a' . $article_id);
        if ($article_info) {
            return $article_info;
        } else {
            $article_info = $this->article->getArticleInfo(['id' => $article_id], $field);
            //将数据存储redis
            $this->setRedis('a' . $article_id, $article_info);
        }
        return $article_info;
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

    //根据栏目id和命名规则计算列表文件名

    /**
     * @param $columen_id
     * @param $listrule
     * @param int $page
     * @return mixed|string
     */
    private function getColumnFileName($columen_id, $listrule, $page = 1)
    {
        //将文档命名规则字符串处理成小写
        $listrule = strtolower($listrule);

        //将列表id和$page存入数组
        $name_info = [
            '{tid}' => $columen_id,
            '{page}' => $page
        ];

        //循环替换文档名规则内容
        foreach ($name_info as $key => $value) {
            $listrule = str_replace($key, $value, $listrule);
        }

        //返回处理后的文档文件名
        return $listrule;
    }

    //写入数据到redis内

    /**
     * @param $key
     * @param $value
     */
    private function setRedis($key, $value)
    {
        $this->redis->set($key, json_encode($value, JSON_UNESCAPED_UNICODE), 30);
    }
    /**
     * @param $key
     * @return array|mixed
     */
    //获取redis内数据
    private function getRedis($key)
    {
        $info = $this->redis->get($key);
        if (!empty($info)) {
            return json_decode($info, true);
        } else {
            return [];
        }
    }

    //处理tag标签链接

    /**
     * @param $tag_name
     * @param int $page
     * @param bool $static
     * @return string
     */
    public function getTagUrl($tag_name, $column, $page = 1, $static = false)
    {
        $pinyin = new ChinesePinyin();
        $string = $pinyin->TransformWithoutTone($tag_name, '' ,false);
        if ($page == 1) {
            return $string . '_' .$column . '.html';
        } else {
            return  $string . '_' .$column .'_' . $page . '.html';
        }
    }
}