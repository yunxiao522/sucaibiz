<?php

namespace app\admin\controller;

use app\model\ArticleBody;
use app\model\ArticleImages;
use app\model\ArticleSource;
use SucaiZ\config;
use SucaiZ\File;
use SucaiZ\Zip;
use think\Image;
use think\Session;

class ArticleExtends
{
    //文档类型
    private $channel;
    //文档的扩展信息
    private $extend_info;
    //文档扩展表Model
    private $extend_model;
    //模型执行错误信息
    public $error;

    /**
     * ArticleExtends constructor.
     * @param int $article_id
     * @param int $channel
     */
    public function __construct($channel, $article_id = 0)
    {
        if (empty($channel)) {
            return false;
        }
        $this->channel = $channel;
        //设置扩展模型
        $this->extend_model = $this->getExtendModel($channel);
        //获取文档扩展信息
        if(!empty($article_id)){
            $this->extend_info = self::getInfo($article_id, $channel);
        }
    }

    /**
     * @param $article_id
     * @param $channel
     * @return mixed
     * Description 获取文档扩展信息
     */
    public static function getInfo($article_id, $channel)
    {
        $model = self::getExtendModel($channel);
        return $model::getOne($article_id, '*');
    }

    /**
     * @param $article_info
     * @param $data
     * @return mixed
     * Description 添加文档扩展信息
     */
    public function add($data = [])
    {
        if(!empty($this->error)){
            return false;
        }
        if(empty($data)){
            $data = $this->extend_info;
        }
        $res = $this->extend_model::add($data);
        if(!$res){
            $this->error = '发布失败';
            return false;
        }
        return true;
    }

    /**
     * @param $article_info
     * @param $data
     * @return mixed
     * Description 修改文档扩展信息
     */
    public function edit($data =[])
    {
        if(!empty($this->error)){
            return false;
        }
        if(empty($data)){
            $data = $this->extend_info;
        }
        $res = $this->extend_model::edit($data);
        if(!$res){
            $this->error = '发布失败';
            return false;
        }
        return true;
    }

    /**
     * @param $channel
     * @return string
     * Description 获取扩展表数据库Model模型
     */
    public function getExtendModel($channel)
    {
        if ($channel == 1) {
            return ArticleBody::class;
        } else if ($channel == 2) {
            return ArticleImages::class;
        } else if ($channel == 3) {

        } else if ($channel == 4) {
            return ArticleSource::class;
        }
    }

    /**
     * @param $article_info
     * @param $input
     * @return array|bool|string
     * Description 处理文档类型文章信息
     */
    public function articleBodyHandle($article_info, $input)
    {
        //判断文档栏目所属类型
        $column_type = $article_info['channel'];
        if ($column_type != 1) {
            $this->error = '文档类型错误';
            return $this;
        }
        if (!isset($input['content'])) {
            $this->error = '请输入文档内容';
            return $this;
        }
        //文档内容
        $content = $input['content'];
        if (empty($content)) {
            $this->error = '请输入文档内容';
            return $this;
        }
        $content = getContent($content, ['user_type' => 2, 'user_id' => Session::get('admin')['id']], ['article_id' => $article_info['id'], 'article_title' => $article_info['title']]);
        //组合数据添加到文档扩展表
        $this->extend_info = [
            'article_id' => $article_info['id'],
            'column_id' => $article_info['column_id'],
            'body' => $content,
            'redirecturl' => $article_info['redirecturl'],
            'templet' => $article_info['templet'],
            'user_ip' => $_SERVER['REMOTE_ADDR']
        ];
        return $this;
    }

    /**
     * @param $article_info
     * @param $input
     * @return array|bool|string
     * Description 处理图集类型文章信息
     */
    public function articleImageHandle($article_info, $input)
    {
        //判断文档栏目所属类型
        $column_type = $article_info['channel'];
        if ($column_type != 2) {
            $this->error = '文档类型错误';
            return $this;
        }
        //图集图像信息
        if (!isset($input['images'])) {
            $this->error = '';
            return $this;
        }
        $images_img = $input['images'];
        if (empty($images_img)) {
            $this->error = '请上传图集图片';
            return $this;
        }
        //判断是否需要生成压缩包
        if (isset($input['is_pack'])) {
            //压缩文件
            $zip = new Zip();
            $zip_file = $zip->zipToFile($images_img);
            //上传压缩包到阿里云OSS
            File::$filename = $zip_file;
            File::uplodOss();
            $zip_url = File::$url;
        } else {
            $zip_url = '';
        }
        //初始化字段数据
        $smallimgurl = '';
        $mediumimgurl = '';
        $images = '';
        //获取图集第一张图大小
        $img_info = getimagesize($images_img[0]);
        File::setArticleInfo($article_info['id'], $article_info['title']);
        //判断是否需要生成压缩图
        if (!isset($input['is_thumb'])) {
            //不生成压缩图
            foreach ($images_img as $value) {
                //获取原图大小
                $img_info = getimagesize($value);
                //上传原图到阿里云OSS
                File::$filename = $value;
                File::uplodOss();
                $img_url = File::$url;
                //生成原图字符串
                $images .= "<img src='" . $img_url . "' style='height:$img_info[1]px;width:$img_info[0]px'>,";
            }
        } else {
            //生成压缩图
            //生成小图，中图
            foreach ($images_img as $value) {
                //获取原图大小
                $img_info = getimagesize($value);
                //生成中图字符串
                $medium = $this->thumb($value, 800, 450);
                $mediumimgurl .= "<img src='" . $medium . "' style='height:450px;width:800px'>,";

                //生成小图字符串
                $smaill = $this->thumb($value, 355, 200);
                $smallimgurl .= "<img src='" . $smaill . "' style='height:200px;width:355px'>,";
                //上传原图到阿里云OSS
                File::$filename = $value;
                File::uplodOss();
                $img_url = File::$url;
                //生成原图字符串
                $images .= "<img src='" . $img_url . "' style='height:$img_info[1]px;width:$img_info[0]px'>,";
            }
        }
        //组合数据添加到图集扩展表
        $this->extend_info = [
            'article_id' => $article_info['id'],
            'column_id' => $article_info['column_id'],
            'width' => $img_info[0],
            'height' => $img_info[1],
            'imgurls' => rtrim($images, ','),
            'mediumimgurl' => rtrim($mediumimgurl, ','),
            'smallimgurl' => rtrim($smallimgurl, ','),
            'imgnum' => count($images_img),
            'templet' => $article_info['templet'],
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'redirecturl' => $article_info['redirecturl'],
            'body' => '',
            'packurl' => $zip_url
        ];
        return $this;
    }

    /**
     * @param $file
     * @param $width
     * @param $height
     * @return mixed
     * Description 处理图集的缩略图
     */
    public static function thumb($file, $width, $height)
    {
        $ext = File::getFileExtToFileName($file);
        //构建临时文件
        $tmp_file = './' . config::get('cfg_upload_tmp_dir') . File::getNewFileName() . '.' . $ext;
        //生成缩略图
        $image = Image::open($file);
        $image->thumb($width, $height, Image::THUMB_FIXED)->save($tmp_file);
        //上传缩略图到阿里云OSS
        File::$filename = $tmp_file;
        File::uplodOss();
        return File::$url;
    }

    /**
     * @param $article_id
     * @return mixed
     * Description 获取文档扩展信息
     */
    public function getExtendInfo($article_id){
        return $this->extend_info;
    }

    /**
     * @param $article 文档信息
     * @param $data 输入的数据
     * Description 处理数据
     */
    public function dealInfo($article, $data){
        if($this->channel == 1){
            $this->articleBodyHandle($article['id'], $data);
        }else if($this->channel == 2){
            $this->articleImageHandle($article['id'], $data);
        }else if($this->channel == 3){

        }else if($this->channel == 4){

        }
        return $this;
    }
}