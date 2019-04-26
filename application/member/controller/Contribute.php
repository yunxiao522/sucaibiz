<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/7
 * Time: 13:21
 * Description：会员投稿
 */

namespace app\member\controller;


use app\member\model\Article;
use app\member\model\Column;
use app\member\model\Tag;
use SucaiZ\File;
use think\Request;
use think\View;
use app\member\model\Level;

class Contribute extends Common
{
    public function __construct()
    {
        parent::__construct();
        //获取用户等级信息
        $level = new Level();
        $level_info = $level->getLevelInfo(['id' => $this->member_info['type']]);
        $this->member_info['level_info'] = $level_info['level_name'];
        //分配会员数据到页面
        View::share('user_info', $this->member_info);
    }

    //会员投稿方法
    public function add()
    {
        //验证数据
        $type = input('type');
        if (!isset($type) || empty($type) || !is_string($type)) {
            $type = 'article';
        }
        if (Request::instance()->isPost()) {
            //验证前台提交的数据
            $title = input('title');
            if (!isset($title)) {
                echo '非法访问';
                die;
            }
            if (empty($title)) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '输入的标题不能为空'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($title, 'UTF-8') > 80) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '输入的标题不能超过80个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $column = input('column');
            if (!isset($column) || !is_numeric($column)) {
                echo '非法访问';
                die;
            }
            $column_obj = new Column();
            $column_info = $column_obj->getColumnInfo(['id' => $column]);
            if (empty($column_info)) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '请选择正确的栏目'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $pubdate = input('pubdate');
            if (!isset($pubdate)) {
                echo '非法访问';
                die;
            }
            $pubdate = strtotime($pubdate);
            $link = input('article_url');
            if (!isset($link)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($link, 'UTF-8') > 200) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '输入的原文链接不能超过200个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $source = input('source');
            if (!isset($source)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($source, 'UTF-8') > 30) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '来源不能超过30个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $author = input('author');
            if (!isset($author)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($author, 'UTF-8') > 20) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '作者不能超过20个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $tag = input('tag');
            if (!isset($tag)) {
                echo '非法访问';
                die;
            }
            //处理tag标签
            $tag = str_replace('，', ',', $tag);
            $tag_list = explode(',', $tag);
            //循环检测tag列表数据
            foreach ($tag_list as $key => $value) {
                if (mb_strlen($value, 'UTF-8') > 12) {
                    $a = [
                        'errorcode' => 1,
                        'msg' => '输入的单个tag标签字数不能超过12个'
                    ];
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            }
            $keyword = input('keyword');
            if (!isset($keyword)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($keyword, 'UTF-8') > 60) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '输入的关键词不能超过60个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if (!isset($description)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($description, 'UTF-8') > 200) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '输入的描述不能超过200个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $content = input('content');
            if (!isset($content)) {
                echo '非法访问';
                die;
            }
            $litpic_img = input('litpic_img');
            $litpic_id = input('litpic_id');
            if (!isset($litpic_id) || !isset($litpic_img) || !is_numeric($litpic_id)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($litpic_img, 'UTF-8') > 100) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '缩略图的地址不能超过100个字符'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $token = input('token');
            if(!isset($token) || empty($token)){
                echo '非法访问';die;
            }
            //组合数据添加到数据库
            $article = new Article();
            $arr = [
                'column_id' => $column,
                'iscommend' => 1,
                'channel' => 1,
                'arcrank' => '',
                'click' => 0,
                'money' => 0,
                'title' => $title,
                'shorttitle' => '',
                'author' => $author,
                'source' => $source,
                'litpic' => $litpic_img,
                'slide_img' => '',
                'roll_img' => '',
                'arcatt' => '',
                'user_type' => 1,
                'userid' => $this->member_info['id'],
                'description' => $description,
                'keywords' => $keyword,
                'templet' => $column_info['temparticle'],
                'redirecturl' => '',
                'token' => $token,
                'create_time' => time(),
                'alter_time' => '',
                'pubdate' => $pubdate,
                'is_delete' => 1,
                'is_make' => 2,
                'is_audit' => 2,
                'comment_num' => 0,
                'link' => $link
            ];
            //开启数据库事务
            //添加基本表数据
            $article_id = $article->addArticleInfo($arr);
            if ($article_id) {
                //添加tag信息
                $tag_obj = new Tag();
                $tag_obj->addFullTagInfo($tag_list, $article_id, $column);
                //根据类型向不同的扩展表添加数据
                //添加文档扩展表
                if($type == 'article'){
                    //处理文档内容
                    $content = getContent($content ,['user_type'=>2 ,'user_id'=>$this->member_info['id']] ,['article_id'=>$article_id ,'article_title'=>$title]);
                    //组合数据
                    $b = [
                        'article_id'=>$article_id,
                        'column_id'=>$column,
                        'body'=>$content,
                        'redirecturl'=>'',
                        'templet'=>$column_info['temparticle'],
                        'user_ip'=>Request::instance()->ip()
                    ];
                    $res = $article->addArticleExtendInfo($b ,1);
                    if($res){
                        $a = [
                            'errorcode'=>0,
                            'msg'=>'发布成功'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }else{
                        $a = [
                            'errorcode'=>1,
                            'msg'=>'发布失败'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }
                    //添加图集扩展表
                }else if($type == 'atlas'){
                    $b = [
                        'article_id'=>$article_id,
                        'column_id'=>$column,
                        'width'=>'',
                        'height'=>'',
                        'imgurls'=>'',
                        'mediumimgurl'=>'',
                        'smallimgurl'=>'',
                        'imgnum'=>'',
                        'templet'=>$column_info['temparticle'],
                        'user_ip'=>Request::instance()->ip(),
                        'redirecturl'=>'',
                        'body'=>'',
                        'packurl'=>'',
                        'color'=>'',
                        'size'=>''
                    ];
                    $res = $article->addArticleExtendInfo($b ,2);
                    if($res){
                        $a = [
                            'errorcode'=>0,
                            'msg'=>'发布成功'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }else{
                        $a = [
                            'errorcode'=>1,
                            'msg'=>'发布失败'
                        ];
                        return json_encode($a ,JSON_UNESCAPED_UNICODE);
                    }
                    //添加资源扩展表
                }else if($type == 'resources'){

                }


            } else {
                $a = [
                    'errorcode' => 1,
                    'msg' => '发布失败'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }

            //添加扩展表信息
        } else {
            //根据请求类型获取栏目列表数据
            $column = new Column();
            if ($type == 'article') {
                $where = ['channel_type' => 1];
            } else if ($type == 'atlas') {
                $where = ['channel_type' => 2];
            } else if($type == 'resources'){
                $where = ['channel_type' => 3];
            }
            $column_list = $column->getColumnList($where, ' id,type_name ');
            $article_token = getArticleToken();
            View::share('column_list', $column_list);
            View::share('type', $type);
            View::share('token' ,$article_token);
            return View('templates/contribute_add');
        }
    }
    //上传封面图像方法
    public function uploadLitpic()
    {
        //验证数据
        $img = input('img');
        $name = input('name');
        File::$info = ['name' => $name];
        if (File::uploadBase64File($img, '', '', true)) {
            $a = [
                'errorcode' => 0,
                'msg' => '上传成功',
                'url' => File::$url,
                'upload_id' => File::$upload_id
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 1,
                'msg' => '上传失败',
                'errormsg' => File::$error,
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //上传图集图像方法
    public function uploadImages()
    {
        //上传图集图像方
        //获取文章token
        $token = input('token');
        if (!isset($token) || empty($token)) {
            echo '非法访问';
            die;
        }
        $data = input();
        $_FILES['file']['type'] = input('type');

        $chunk = isset($data['chunk']) ? $data['chunk'] : 0;
        $chunks = isset($data['chunks']) ? $data['chunks'] : 1;
        //使用分片上传文件方法
        $savename = File::sliceUploadFile($_FILES['file'], $chunk, $chunks);
        if ($savename != 'loading') {
            $a = [
                'errorcode' => 0,
                'msg' => '上传成功',
                'info' => $savename
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 2,
                'msg' => '分片数据上传成功'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }

    }
}