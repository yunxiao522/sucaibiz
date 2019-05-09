<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/31
 * Time: 17:38
 * Description：文档控制器
 */

namespace app\admin\controller;

use app\model\Article as article_model;
use app\model\ArticleAuthor;
use app\model\ArticleSource;
use app\model\Attribute;
use app\validate\Attribute as Attribute_Validate;
use app\common\model\Base;
use app\model\Article as Articles_Model;
use app\model\Column;
use Baidu\Baidu\BaiduPush;
use Phpml\Clustering\DBSCAN;
use Sina\Sae\SaeTClientV2;
use SucaiZ\File;
use SucaiZ\Zip;
use SucaiZ\config;
use think\Exception;
use think\Image;
use think\Request;
use think\Db;
use think\Session;
use think\view;

class Article extends Common
{
    private $redis;
    //储存article实例
    private $article;
    //储存文档属性模型
    private $attribute;
    //储存临时文件夹
    private $tmp_dir;
    //储存tag实例
    private $tag;
    //储存member实例
    private $member;
    //储存column实例
    private $column;
    //前台提交的数据
    private $input;
    //存储文档信息
    private $article_info;
    //储存发布文档时需要通知的观察者
    private $pushServers = [];
    //文档模型错误信息
    private $error = '';

    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
        //获取article model实例
        $this->article = new article_model();
        //设置用户信息
        $this->article->user_id = $this->admin_info['id'];
        $this->article->user_type = 2;
        $this->tmp_dir = config::get('cfg_upload_tmp_dir');
        $this->attribute = new Attribute();
        $this->tag = new \app\admin\model\Tag();
        $this->member = new \app\admin\model\Member();
        $this->column = new Column();
        $this->input = input();
    }

    /**
     * Description 添加发布文档的观察者方法
     */
    private function addPushServers(){
        //增加tag标签内容
        $this->pushServers[] = new Tag();
        //发布新浪微博
        $this->pushServers[] = new Sina();
        //生成迅搜索引信息
        $this->pushServers[] = new Search();
    }

    /**
     * Description 执行发布文档通知
     */
    private function pushNotify(){
        foreach ($this->pushServers as $pushServer){
            $res = $pushServer->add($this->article_info, input());
            if(!$res){
                $this->error = $pushServer->error;
                return false;
            }
        }
        return true;
    }

    public function push(){
        Db::startTrans();
        //添加文档基础信息
        $this->add();
        if(!empty($this->error)){
            return self::ajaxError($this->error);
        }
        //添加扩展表数据
        $extend = new ArticleExtends($this->article_info['channel']);
        $res = $extend->dealInfo($this->article_info, input())->add();
        if(!$res){
            Db::rollback();
            return self::ajaxError($extend->error);
        }
        //执行发布通知
        $res = $this->pushNotify();
        if(!$res){
            Db::rollback();
            return self::ajaxError($this->error);
        }
        Db::commit();
        return self::ajaxOk('发布成功');
    }

    /**
     * @return bool
     * Description 添加基础文档信息
     */
    public function add(){
        //验证前台提交数据
        $data = $this->checkArticleForm();
        if (is_string($data)) {
            $this->error = $data;
            return false;
        }
        $data['is_delete'] = 1;
        $data['is_audit'] = 1;
        $data['create_time'] = time();
        $data['alter_time'] = '';
        $data['delete_time'] = '';
        $this->article_info = $data;
        //添加数据到文档基本数据表
        $article_id = Article_Model::add($data);
        if(!$article_id){
            $this->error = '发布失败';
        }
        $this->article_info['id'] = $article_id;
    }

    /**
     * @return false|string
     * Description 获取文档列表数据
     */
    public function getarticlelist()
    {
        //接收参数
        $is_delete = input('del');
        $is_my = input('my');
        $where = [];
        //判断是使用了搜索
        $keyword = input('keyword');
        if (!empty($keyword)) {
            $keyword_arr = explode(',', $keyword);
            foreach ($keyword_arr as $value) {
                $keyword_arr_info = explode(':', $value);
                if(!isset($keyword_arr_info[1])){
                    return self::ajaxOkdata([], '搜索条件错误');
                }
                if ($keyword_arr_info[0] == 'title') {
                    $where[$keyword_arr_info[0]] = ['like', "%$keyword_arr_info[1]%"];
                } else {
                    $where[$keyword_arr_info[0]] = $keyword_arr_info[1];
                }
            }
        }
        $where['is_delete'] = empty($is_delete) ? 1 : 2;
        if (!empty($is_my)) {
            $where['userid'] = Session::get('admin')['id'];
        }
        $Article_List = Articles_Model::getList($where, 'id,title,pubdate,column_id,arcatt,click,comment_num,is_make,is_audit,author,source,alter_time,arcrank');
        foreach ($Article_List['data'] as $key => $value) {
            $Article_List['data'][$key]['column_id'] = Column::getField(['id' => $value['column_id']], 'type_name', 'id desc', true);
        }
        return json_encode($Article_List, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return false|string
     * @throws Exception
     * @throws \think\exception\PDOException
     * Description 删除一篇文档方法
     */
    public function delarticleone()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $arr = [
            'is_delete' => 2,
            'delete_time' => time()
        ];
        $result = Articles_Model::edit($where, $arr, true, true);
        if ($result === false) {
            return self::ajaxError('删除失败');
        } else {
            return self::ajaxOk('删除成功');
        }
    }

    /**
     * @return false|string
     * @throws Exception
     * @throws \think\exception\PDOException
     * Description 删除多篇文档
     */
    public function delarticleall()
    {
        $ids = input()['data'];
        if (empty($ids) || !is_array($ids)) {
            return self::ajaxError('非法访问');
        }
        $res = Articles_Model::edit(['id'=>['in',$ids]],['is_delete'=>2,'delete_time'=>time()], true, true);
        if($res){
            return self::ajaxOk('删除成功');
        }else{
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * @throws Exception
     * @throws \think\exception\PDOException
     * Description 还原一篇文章
     */
    public function restoreone()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $arr = [
            'is_delete' => 1,
            'delete_time' => ''
        ];
        $where = [
            'id'=>$id
        ];
        $result = Articles_Model::edit($where, $arr, true, true);
        if (!$result) {
            return self::ajaxError('还原失败');
        } else {
            return self::ajaxOk('还原成功');
        }
    }

    /**
     * @return false|string
     * Description 还原多篇文档
     */
    public function restoreall()
    {
        $ids = input()['data'];
        if (empty($ids) || !is_array($ids)) {
            return self::ajaxError('非法访问');
        }
        $arr = [
            'is_delete' => 1,
            'delete_time' => ''
        ];
        $where = [
            'id'=>[
                'in',
                $ids
            ]
        ];
        $res = Articles_Model::edit($where, $arr, true, true);
        if($res){
            return self::ajaxOk('还原成功');
        }else{
            return self::ajaxError('还原失败');
        }
    }

    /**
     * @return false|string
     * Description 真删除一篇文章方法
     */
    public function realdelarticleone()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        //获取文档信息
        $article_info = Articles_Model::getOne($where, 'is_delete,is_make');
        if (empty($article_info)) {
            return self::ajaxError('删除失败');
        }
        $article_delete_status = $article_info['is_delete'];
        $article_make_status = $article_info['is_make'];
        if ($article_delete_status == 1) {
            return self::ajaxError('删除失败');
        }
        Db::startTrans();
        //删除文档html文件
        if ($article_make_status == 1) {
            $url = self::getArticleUrl($id, true, false);
            $article_file = '.' . $url;
            $unlink_status = @unlink($article_file);
            if (!$unlink_status) {
                Db::rollback();
                return self::ajaxError('删除失败');
            }
        }
        $result = Article_Model::del($where, true);
        if ($result) {
            Db::commit();
            return self::ajaxOk('删除成功');
        } else {
            Db::rollback();
            return self::ajaxError('删除失败');
        }
    }

    //真删除多篇文章方法
    public function realdelarticleall()
    {
        $data = input()['data'];
        if (!isset($data) || !is_array($data)) {
            echo '非法访问';
            die;
        }
        Db::startTrans();
        foreach ($data as $key => $value) {
            $where = [
                'id' => (int)$value
            ];
            $res = model('article')->del($where);
            if (!$res) {
                Db::rollback();
                return $this->ajaxError('删除失败');
            }
        }
        Db::commit();
        return $this->ajaxOk('删除成功');

    }

    /**
     * @return false|string
     * Description 获取栏目内容方法
     */
    public function getcolumninfojson()
    {
        $column_id = input('id');
        $column_list = Column::getAll([], 'id,parent_id');
        $column_son_list = self::getSonList($column_id, $column_list);
        array_push($column_son_list, $column_id);
        $where = [
            'is_delete' => 1,
            'column_id' => [
                'in',
                $column_son_list
            ],
            'is_audit' => 1

        ];
        $article_list = Articles_Model::getList($where, '*');
        $article_list['data'] = $this->dealArticleList($article_list['data']);
        return json_encode($article_list, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return false|string|\think\response\View
     * Description 发布文档
     */
    public function publish()
    {
        if (Request::instance()->isPost()) {
            //验证前台提交数据
            $d = $this->checkArticleForm();
            if (is_string($d)) {
                return $this->ajaxError($d);
            }
            $d['is_delete'] = 1;
            $d['is_audit'] = 1;
            $d['create_time'] = time();
            $d['alter_time'] = '';
            $d['delete_time'] = '';
            $article = model('Article');
            //开启数据库事务
            Db::startTrans();
            try {
                //添加数据到文档基本数据表
                $article_id = $article->addArticle($d);
            } catch (Exception $exception) {
                return $this->ajaxError('文档发布失败');
            }
            if (!$article_id) {
                return $this->ajaxError('文档发布失败');
            }
            try {
                $res = $this->pushArticleTagHandle($article_id, $d['column_id']);

            } catch (Exception $exception) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,tag标签处理失败');
            }
            if (!$res) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,tag标签处理失败');
            }
            //如果文档缩略图不为空,则更新附件表数据
            try {
                $litpic_arr = [];
                array_push($litpic_arr, input('slide_id'), input('roll_id'), input('litpic_id'));
                foreach ($litpic_arr as $value) {
                    if (!empty($value)) {
                        Model('upload')->edit(['id' => $value], ['article_id' => $article_id, 'article_title' => $d['title']]);
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                return self::ajaxError('修改失败');
            }
            //设置文档信息，为后续文件处理做准备
            File::setArticleInfo($article_id, $this->input['title']);
            //资源类型
            try {
                $res = $this->articleSourceHandle($article_id, $d);
            } catch (Exception $exception) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,资源信息处理错误');
            }
            if (is_string($res)) {
                Db::rollback();
                return $this->ajaxError($res);
            }
            if (!$res) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,资源信息处理错误');
            }
            //文档类型
            try {
                $res = $this->articleBodyHandle($article_id, $d);
            } catch (Exception $exception) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,文档信息处理错误');
            }
            if (is_string($res)) {
                Db::rollback();
                return $this->ajaxError($res);
            }
            if (!$res) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,文档信息处理错误');
            }
            //图集类型
            try {
                $res = $this->articleImageHandle($article_id, $d);
            } catch (Exception $exception) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,图集信息处理错误');
            }
            if (is_string($res)) {
                Db::rollback();
                return $this->ajaxError($res);
            }
            if (!$res) {
                Db::rollback();
                return $this->ajaxError('文档发布失败,图集信息处理错误');
            }
            Db::commit();
            //发布微博
            $res = $this->pushWeibo($d['title'], $d['litpic'], $article_id, $d['redirecturl']);
            if (!$res) {
                return $this->ajaxOk('文档发布成功,同步新浪微博发生错误');
            };
            //生成静态文件
            try {
                $this->makeHtml($article_id, $d['channel'], $d['column_id']);
            } catch (Exception $exception) {
                return $this->ajaxOk('文档发布成功,生成静态文件失败,可使用动态链接访问');
            }
            //百度链接推送
            try {
                $this->pushArticleBaiduPush($article_id, $d['redirecturl']);
            } catch (Exception $exception) {
                return $this->ajaxOk('文档发布成功,百度链接推送失败');
            }
            return $this->ajaxOk('发布成功');
        } else {
            //判断是否是栏目管理页面点击的
            $column_id = input('column');
            if (!isset($column_id)) {
                $column_id = 0;
            }
            //获取文档属性
            $attribute = model('Attribute');
            $attribute_list = $attribute->getAttributeList([], ' id,attrname,mark ');
            $this->assign('attribute', $attribute_list);

            //获取用户等级
            $member = model('Member');
            $member_level = $member->getMemberLevel([], ' id,level_name ');
            $this->assign('member_level', $member_level);

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
            $this->assign('column_list', $column_list);
            $this->assign('column', $column_id);
            //获取文章token,方便以后使用
            $article_token = getArticleToken();
            $this->assign('token', $article_token);
            return View();
        }
    }

    //处理图集的缩略图
    private function thumb($file, $width, $height)
    {
        $ext = File::getFileExtToFileName($file);
        //构建临时文件
        $tmp_file = './' . $this->tmp_dir . getNewFileName() . '.' . $ext;
        //生成缩略图
        $image = Image::open($file);
        $image->thumb($width, $height, Image::THUMB_FIXED)->save($tmp_file);
        //上传缩略图到阿里云OSS
        File::$filename = $tmp_file;
        File::uplodOss();
        return File::$url;
    }

    /**
     * @return false|string
     * Description 获取文档属性json数据
     */
    public function getattributetreejson()
    {
        $attribute_list = Attribute::getAll([], ' id,attrname as name');
        foreach ($attribute_list as $key => $value) {
            $attribute_list[$key]['children'] = [];
        }
        return self::ajaxReturn($attribute_list);
    }

    /**
     * @return false|string|\think\response\View
     * Description 编辑文档属性
     */
    public function alterattribute()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            $validate = new Attribute_Validate();
            if(!$validate->check(input())){
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData('', function($data, $input){
                $arr = [
                    'attrname'=>$input['att_name'],
                    'mark'=>$input['att']
                ];
                return $arr;
            });
            $res = Attribute::edit($where, $data, true, true);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $attribute_info = Attribute::getOne($where);
            view::share('attribute_info', $attribute_info);
            return View();
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 添加文档属性
     */
    public function addattribute()
    {
        if (Request::instance()->isPost()) {
            $validate = new Attribute_Validate();
            if(!$validate->check(input())){
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData('', function($data, $input){
                $arr = [
                    'attrname'=>$input['att_name'],
                    'mark'=>$input['att']
                ];
                return $arr;
            });
            $res = Attribute::add($data);
            if ($res) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            return View();
        }
    }

    /**
     * @return false|string
     * Description 根据栏目id获取栏目文档类型
     */
    public function getcolumnchannel()
    {
        $column_id = input('column_id');
        if (empty($column_id) || !is_numeric($column_id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $column_id];
        $column_info = Column::getOne($where, 'channel_type ,temparticle');
        if ($column_info) {
            $a['errorcode'] = 0;
            $a['msg'] = '获取数据成功';
            $a['channel_type'] = $column_info['channel_type'];
            $a['template'] = $column_info['temparticle'];
            return self::ajaxReturn($a);
        } else {
            return self::ajaxError('获取数据失败');
        }
    }

    //上传文档缩略图方法
    public function uploadlitpic()
    {
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $a['url'] = File::$url;
            $a['id'] = File::$upload_id;
            $a['errorcode'] = 0;
            $a['msg'] = '上传成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @return \think\response\View
     * Description 显示文档作者方法
     */
    public function showarticleauthor()
    {
        $author_list = ArticleAuthor::getAll([], '*', 1000, '');
        view::share('author_list', $author_list);
        return View('article_author_show');
    }

    /**
     * @return \think\response\View
     * Description 显示文档来源方法
     */
    public function showarticlesource()
    {
        $source_list = ArticleSource::getAll([], '*', 1000, '');
        view::share('source_list', $source_list);
        return View('article_source_show');
    }

    /**
     * @return \think\response\View
     * Description 设置文章作者方法
     */
    public function setarticleauthor()
    {
        if (Request::instance()->isPost()) {
            $auname = input('auname');
            $auname = str_replace('，', ',', $auname);
            $auname_arr = explode(',', $auname);
            $auname_list = [];
            foreach ($auname_arr as $key => $value) {
                $auname_list[$key]['auname'] = $value;
            }
            $res = ArticleAuthor::edit([1=>1], $auname_list, true, true);
            if($res){
                return self::ajaxOk('修改成功');
            }else{
                return self::ajaxError('修改失败');
            }

        } else {
            $author_list = ArticleAuthor::getAll();
            $author_arr = [];
            foreach ($author_list as $key => $value) {
                $author_arr[] = $value['auname'];
            }
            $author_string = implode(',', $author_arr);
            view::share('author', $author_string);
            return View('article_author_set');
        }
    }

    /**
     * @return false|mixed|string
     * Description 设置文档来源方法
     */
    public function setarticlesource()
    {
        if (Request::instance()->isPost()) {
            $soname = input('soname');
            $soname = str_replace('，', ',', $soname);
            $soname_arr = explode(',', $soname);
            $soname_list = [];
            foreach ($soname_arr as $key => $value) {
                $soname_list[$key]['soname'] = $value;
            }
            $res = ArticleSource::edit([1=>1], $soname_list);
            if ($res === false) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $source_list = ArticleSource::getAll();
            $source_arr = [];
            foreach ($source_list as $key => $value) {
                $source_arr[] = $value['soname'];
            }
            $source_string = implode(',', $source_arr);
            $this->assign('source', $source_string);
            return $this->fetch('article_source_set');
        }
    }

    /**
     * @return false|string
     * Description 检测文档是否已经存在方法
     */
    public function examinearticletitle()
    {
        $title = input('title');
        $where = [
            'title' => $title,
            'is_delete'=>1
        ];
        $article_info = Article_Model::getOne($where, 'id');
        if ($article_info) {
            $a['errorcode'] = 0;
            $a['msg'] = '获取到相应数据';
            $a['article_id'] = $article_info['id'];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcoe'] = 1;
            $a['msg'] = '无相应数据';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //上传资源展示图像方法
    public function uploadSourceImgOne()
    {

        //获取文章token
        $token = input('token');
        if (!isset($token) || empty($token)) {
            return self::ajaxError('非法访问');
        }

        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $a['errorcode'] = 0;
            $a['msg'] = '上传成功';
            $a['info'] = File::$url;
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //上传资源展示多张图片方法
    public function uploadSourceImgMore()
    {
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
            $key = $token . '_resuirce_img';
            $this->redis->rPush($key, $savename);
        }
    }

    //上传图集图像方法
    public function uploadImages()
    {
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
        File::setUserInfo(1, Session::get('admin')['id']);
        File::setArticleInfo(-1, "this's slicpUpload");
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

    //添加离线任务方法
    public function addLiXian()
    {

        //获取资源网址
        $url = input('url');

        //获取文档token
        $token = input('token');

        //校验前台提交的数据
        if (!isset($url) || !isset($token)) {
            echo '非法访问';
            die;
        }

        if (empty($url)) {
            $a = [
                'errorcode' => 1,
                'msg' => '输入的资源网址不能为空哦'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }

        //验证提交的网址能正常访问
        if (!File::checkUrl($url)) {
            $a = [
                'errorcode' => 1,
                'msg' => '输入的资源网址不能正常访问',

            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }

        //添加离线下载任务
        $d = [
            'function' => 'liXianDown',
            'url' => $url,
            'oss' => true,
            'token' => $token,
            'bucket' => 'data-sucaiz',
            'object' => date('Y-m-d', time()) . '/' . getNewFileName() . '.' . $ext,
            'ext' => $ext,
            'user_type' => 2,
            'user_id' => Session::get('admin')['id']
        ];

        //记录文件信息到redis
        $file = $d['bucket'] . ':' . $d['object'];

        //相关信息只存储1个小时
//        $this->redis->set($token, $value, 3600);
        task('liXianDown', $d);
        $a = [
            'errorcode' => 0,
            'msg' => '添加离线任务成功',

        ];
        return json_encode($a, JSON_UNESCAPED_UNICODE);


    }

    /**
     * @return false|string|\think\response\View
     * Description 编辑文档
     */
    public function alterArticle()
    {
        $id = input('id');
        if (!isset($id)) {
            return self::ajaxError('非法访问');
        }
        //取出文档栏目所属类型
        $article_info = Article_Model::getOne(['id' => $id], ' channel,column_id ');
        $channel = $article_info['channel'];
        if (!isset($channel)) {
            return self::ajaxError('非法访问');
        }
        $info = Model('article')->getArticleInfoAll(['a.id' => $id], $channel);
        if (Request::instance()->isPost()) {
            $data = $this->checkArticleForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['alter_time'] = time();
            //开启数据库事务处理
            Db::startTrans();
            try {
                Model('article')->edit(['id' => $id], $data);
            } catch (Exception $e) {
                Db::rollback();
                return self::ajaxError('修改失败');
            }
            //更新tag表信息
            try {
                $tag_arr = explode(',', input('tag'));
                $tag = new Tag();
                $tag->alterTag($id, $article_info['column_id'], $tag_arr);
            } catch (Exception $e) {
                Db::rollback();
                return self::ajaxError('修改失败');
            }
            //如果文档缩略图有变动,则更新附件表数据
            try {
                $litpic_arr = [];
                array_push($litpic_arr, input('slide_id'), input('roll_id'), input('litpic_id'));
                foreach ($litpic_arr as $value) {
                    if (!empty($value)) {
                        Model('upload')->edit(['id' => $value], ['article_id' => $id, 'article_title' => $data['title']]);
                    }
                }
            } catch (Exception $e) {
                Db::rollback();
                return self::ajaxError('修改失败');
            }

            $in = input();
            //根据文档类型不同,使用不同的方式处理数据
            switch ($article_info['channel']) {
                case 2:
                    //处理原图
                    $old = $in['old'];
                    $small_img = explode(',', $info['smallimgurl']);
                    $medium_img = explode(',', $info['mediumimgurl']);
                    $images_img = explode(',', $info['imgurls']);
                    foreach ($old as $key => $value) {
                        if (empty($value)) {
                            unset($small_img[$key]);
                            unset($medium_img[$key]);
                            unset($images_img[$key]);
                        }
                    }
                    //处理新图数据
                    File::setArticleInfo($id, $data['title']);
                    File::setUserInfo(2, Session::get('admin')['id']);
                    if (isset($in['new'])) {
                        $new = $in['new'];
                        foreach ($new as $value) {
                            //生成中图
                            $medium = $this->thumb($value, 800, 450);
                            $medium_img[] = "<img src='" . $medium . "' style='height:450px;width:800px'>";
                            //生成小图
                            $small = $this->thumb($value, 355, 200);
                            $small_img[] = "<img src='" . $small . "' style='height:200px;width:355px'>";
                            //获取原图大小
                            $img_info = getimagesize($value);
                            //上传原图到阿里云OSS
                            File::$filename = $value;
                            File::uplodOss();
                            $img_url = File::$url;
                            //生成原图字符串
                            $images_img[] = "<img src='" . $img_url . "' style='height:$img_info[1]px;width:$img_info[0]px'>";
                        }
                    }
                    //是否生成压缩包
                    if (isset($in['is_pack']) && (isset($in['new']) || in_array('', $in['old']))) {
                        foreach ($images_img as $value) {
                            $url = getImgUrl($value);
                            $filename = File::getRemoteFileName($url);
                            $file_info = Model('upload')->getOne(['url' => $url], 'oss_bucket,oss_object,file_type');
                            File::setOssInfo($file_info['oss_bucket'], $file_info['oss_object']);
                            File::$filename = './' . $this->tmp_dir . $filename . '.' . $file_info['file_type'];
                            if (File::getOssFile()) {
                                $arr[] = File::$filename;
                            }
                        }
                        //压缩文件
                        $zip = new Zip();
                        $zip_file = $zip->zipToFile($arr);
                        //上传缩略图到阿里云OSS
                        File::setOssInfo('', '', '');
                        File::$filename = $zip_file;
                        File::uplodOss();
                        $zip_url = File::$url;
                    } else {
                        $zip_url = $info['packurl'];
                    }
                    $base = new Base();
                    $base->table = Model('ColumnType')->getField(['typename' => '图集'], 'table_name');
                    try {
                        $base->edit([
                            'article_id' => $id
                        ], [
                            'imgurls' => implode(',', $images_img),
                            'mediumimgurl' => implode(',', $medium_img),
                            'smallimgurl' => implode(',', $small_img),
                            'imgnum' => count($images_img),
                            'templet' => $in['template'],
                            'redirecturl' => $in['redirecturl'],
                            'packurl' => $zip_url
                        ]);
                    } catch (Exception $e) {
                        Db::rollback();
                        return self::ajaxError('修改失败');
                    }
                    break;
                case 1:
                    if (!isset($in['content'])) {
                        return self::ajaxError('非法访问');
                    }
                    //更新文档附加表
                    $base = new Base();
                    $base->table = Model('ColumnType')->getField(['typename' => '文章'], 'table_name');
                    try {
                        $base->edit(['article_id' => $id], [
                            'body' => $in['content'],
                            'redirecturl' => $data['redirecturl'],
                            'templet' => $in['template']
                        ]);
                    } catch (Exception $e) {
                        Db::rollback();
                        return self::ajaxError('修改失败');
                    }
                    break;
            }
            //更新相关静态页面
            $this->makeHtml($id, $channel, $article_info['column_id']);
            Model('article')->edit(['id' => $id], ['is_make' => 1]);
            Db::commit();
            return self::ajaxOk('修改成功');
        } else {
            //获取文档基本数据
            $tag_list = $this->tag->getTagListInfo(['l.article_id' => $id], ' tag_name ');
            $tag_arr = array_column($tag_list, 'tag_name');
            $tag_string = implode(',', $tag_arr);
            $attribute = Model('Attribute')->getAll([], '*', 100, 'id asc');
            $member_level = Model('UserLevel')->getall([], '*');
            //获取栏目分类
            $column_list = model('Column')->getAll([], 'id,type_name,parent_id');
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
            //分配数据到页面
            $this->assign('column_list', $column_list);
            $this->assign('level', $member_level);
            $this->assign('tag', $tag_string);
            $this->assign('attribute', $attribute);
            $this->assign('article', $info);
            //判断文档类型
            if ($channel == 1) {
                return View('alter_article');
            } else if ($channel == 2) {
                $small_img = explode(',', $info['imgurls']);
                $this->assign('imgs', $small_img);
                return View('alter_images');
            } else if ($channel == 3) {

            } else if ($channel == 4) {
                $source_img = explode(',', $info['resource_img']);
            }
        }
    }

    //删除图集图像文件
    private function delImages($small_img_arr, $medium_img_arr, $images_arr)
    {
        $arr = [];
        foreach ($small_img_arr as $value) {
            $img_url = getImgUrl($value);
            if (strpos($img_url, 'http') !== false) {
                if (strpos($img_url, 'http://img.sucai.biz') !== false) {
                    $a['object'] = str_replace('http://img.sucai.biz/', '', $img_url);
                    $a['bucket'] = 'sucaiz';
                    $arr[] = $a;
                } else if (strpos($img_url, 'http://image.sucai.biz') !== false) {
                    $a['object'] = str_replace('http://image.sucai.biz/', '', $img_url);
                    $a['bucket'] = 'image-sucaibiz';
                    $arr[] = $a;
                }
            } else {
                unlink($img_url);
            }
        }
        foreach ($medium_img_arr as $value) {
            $img_url = getImgUrl($value);
            if (strpos($img_url, 'http') !== false) {
                if (strpos($img_url, 'http://img.sucai.biz') !== false) {
                    $a['object'] = str_replace('http://img.sucai.biz/', '', $img_url);
                    $a['bucket'] = 'sucaiz';
                    $arr[] = $a;
                } else if (strpos($img_url, 'http://image.sucai.biz') !== false) {
                    $a['object'] = str_replace('http://image.sucai.biz/', '', $img_url);
                    $a['bucket'] = 'image-sucaibiz';
                    $arr[] = $a;
                }
            } else {
                unlink($img_url);
            }
        }
        foreach ($images_arr as $value) {
            $img_url = getImgUrl($value);
            if (strpos($img_url, 'http') !== false) {
                if (strpos($img_url, 'http://img.sucai.biz') !== false) {
                    $a['object'] = str_replace('http://img.sucai.biz', '', $img_url);
                    $a['bucket'] = 'sucaiz';
                    $arr[] = $a;
                } else if (strpos($img_url, 'http://image.sucai.biz') !== false) {
                    $a['object'] = str_replace('http://image.sucai.biz', '', $img_url);
                    $a['bucket'] = 'image-sucaibiz';
                    $arr[] = $a;
                }
            } else {
                unlink($img_url);
            }
        }
        foreach ($arr as $value) {
            File::setOssInfo($value['bucket'], $value['object']);
            File::delOssFile();
        }
    }

    //修改文档基本信息方法
    private function alterArticleInfo($id, $data)
    {
        $arr = [
            'title' => $data['title'],
            'author' => $data['author'],
            'source' => $data['source'],
            'litpic' => $data['litpic'],
            'description' => $data['description'],
            'keywords' => $data['keywords'],
            'templet' => $data['templet'],
            'redirecturl' => $data['redirecturl'],
            'alter_time' => time(),
            'arcatt' => $data['att'],
            'arcrank' => $data['rank']
        ];
        if ($this->article->alterArticleInfo(['id' => $id], $arr)) {
            return true;
        } else {
            return false;
        }
    }

    //获取未审核的文档数据
    public function auditArticle()
    {
        if (Request::instance()->isPost()) {
            //获取数据
            $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        }

    }

    /**
     * @param $article_list
     * @return mixed
     * Description 处理文档列表数据
     */
    private function dealArticleList($article_list)
    {
        $column_list = Model('column')->getAll([], ' id,type_name ');
        $column_arr = array_column($column_list, 'type_name', 'id');
        foreach ($article_list as $key => $value) {
            $article_list[$key]['senddate'] = date('Y-m-d H:i:s', $value['create_time']);
            $article_list[$key]['column_id'] = $column_arr[$value['column_id']];
            if ($value['is_make'] == 1) {
                $article_list[$key]['is_make'] = '已生成';
            } else {
                $article_list[$key]['is_make'] = '未生成';
            }
            if ($value['arcrank'] == 0) {
                $article_list[$key]['arcrank'] = '游客';
            }
            if ($value['is_audit'] == 1) {
                $article_list[$key]['is_audit'] = '已审核';
            } else {
                $article_list[$key]['is_audit'] = '未审核';
            }
            if (!empty($value['alter_time'])) {
                $article_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
            $article_list[$key]['delete_time'] = date('Y-m-d H:i:s', $value['delete_time']);
        }
        return $article_list;
    }

    //修改文档tag标签方法
    private function alterTag($id, $column, $tag_string)
    {

    }

    //获取文档属性列表
    private function getAttribute()
    {
        return $this->attribute->getAttributeList();
    }


    /**
     * @param $text
     * @param bool $pic
     * @param int $article_id
     * @return bool
     * Description 发布新浪微博方法
     */
    private function pushWeibo($text, $pic = false, $article_id = 0, $redirecturl)
    {
        //发布微博
        if (!isset($this->input['weibo'])) {
            return true;
        }
        //判断是否填写访问url,如果没有填写，则手动拼写文档url地址
        if (empty($redirecturl)) {
            $url = self::getArticleUrl($article_id, 0, true, true);
        } else {
            $url = config::get('cfg_hostsite') . $redirecturl;
        }
        $sina = new Sina();
        $access_token = $sina->access_token;
        $sae = new SaeTClientV2(config::get('oauth-sina-appid'), config::get('oauth-sina-appkey'), $access_token);
        $content = $text . $url;
        $result = $sae->share($content, $pic, Request::instance()->ip());
        if (isset($result['created_at'])) {
            //组合数据写入数据库
            $arr = [
                'content' => $content,
                'url' => $url,
                'img' => $pic,
                'create_time' => time(),
                'article_id' => $article_id,
                'weibo_id' => $result['id']
            ];
            $model = new \app\admin\model\Sina();
            $add_result = $model->addWeiBo($arr);
            if (!$add_result) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    //修改文档属性
    public function alterAtt()
    {
        $id = input('id');
        if (!isset($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $data = input();
        if (Request::instance()->isPost()) {
            //验证数据
            if (isset($data['att'])) {
                $arr = array_keys($data['att']);
                $att = implode(',', $arr);
            } else {
                $att = '';
            }
            //组合数据更新数据库信息
            if (Article_Model::edit($where, ['arcatt' => $att, 'alter_time' => time()])) {
                $a = [
                    'errorcode' => 0,
                    'msg' => '修改成功'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a = [
                    'errorcode' => 1,
                    'msg' => '修改失败'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $info = Article_Model::getOne($where, ' id,arcatt ');
            view::share('info', $info);
            view::share('attribute', $this->getAttribute());
            return View('alter_article_attribute');
        }
    }

    //上传幻灯片图像
    public function uploadSlide()
    {
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $a['url'] = File::$url;
            $a['id'] = File::$upload_id;
            $a['errorcode'] = 0;
            $a['msg'] = '上传成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //上传滚动图像
    public function uploadRoll()
    {
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $a['url'] = File::$url;
            $a['id'] = File::$upload_id;
            $a['errorcode'] = 0;
            $a['msg'] = '上传成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @return array|string 数据|结果
     * Description 发布文档表单验证
     */
    private function checkArticleForm()
    {
        $input = input();
        $data = [];
        //文档token
        $data['token'] = input('token');
        if (!$data['token']) {
            return '非法访问';
        }
        //文档标题
        $data['title'] = input('title');
        if (empty($data['title'])) {
            return '输入的文档不能为空';
        }
        if (mb_strlen($data['title'], 'UTF-8') > 80) {
            return '输入的文档标题不能超过80个字符';
        }
        //文档自定义属性
        if (isset($input['attribute'])) {
            $attribute = $input['attribute'];
            $attribute_info = array_keys($attribute);
            $data['arcatt'] = implode(',', $attribute_info);
        } else {
            $data['arcatt'] = '';
        }
        //根据文档属性判断幻灯片图像
        $slide_url = input('slide');
        $slide_id = input('slide_id');
        if (isset($attribute['f'])) {
            if (empty($slide_url) || empty($slide_id)) {
                return '请上传幻灯片图像';
            }
        }
        $data['slide_img'] = $slide_url;
        //根据文档属性判断滚动图像
        $roll_url = input('roll');
        $roll_id = input('roll_id');
        if (isset($attribute['s'])) {
            if (empty($roll_url) || empty($roll_id)) {
                return '请上传滚动图像';
            }
        }
        $data['roll_img'] = $roll_url;
        //文档缩略图
        $data['litpic'] = input('litpic');
        if (empty($data['litpic'])) {
            return '请上传缩略图';
        }
        if (mb_strlen($data['litpic'], 'UTF-8') > 100) {
            return '缩略图地址不能超过100个字符';
        }
        //生成html页面
        $data['is_make'] = empty($input['makehtml']) ? 2 : 1;
        //能否评论
        $data['iscommend'] = empty($input['comment']) ? 2 : 1;
        //评论次数
        $click = input('click');
        if (!empty($click)) {
            $data['click'] = input('click');
            if (!is_numeric($data['click'])) {
                return '输入的点击次数必须是数字';
            }
            if ($data['click'] < 0) {
                return '输入的点击次数不能小于0';
            }
        }
        //文档来源
        $data['source'] = input('source');
        if (empty($data['source'])) {
            return '文档来源不能为空';
        }
        if (mb_strlen($data['source'], 'UTF-8') > 15) {
            return '文档来源不能超过15个字符';
        }
        //文档作者
        $data['author'] = input('author');
        if (empty($data['author'])) {
            return '文档作者不能为空';
        }
        if (mb_strlen($data['author'], 'UTF-8') > 15) {
            return '文档作者不能超过15个字符';
        }
        //文档栏目
        $data['column_id'] = input('column');
        if (empty($data['column_id'])) {
            return '请选择文档所属栏目';
        }
        if (!is_numeric($data['column_id'])) {
            return '输入的栏目所属栏目必须是数字';
        }
        //阅读文档所需的金币
        $gold = input('gold');
        if (!empty($gold)) {
            $data['gold'] = input('gold');
            if (!is_numeric($data['gold'])) {
                return '输入金币必须是数字';
            }
            if ($data['gold'] < 0) {
                return '输入的金币不能小于0';
            }
        }
        //文档发布时间
        $pubdate = input('pudate');
        if (empty($pubdate)) {
            return '发布日期不能为空';
        }
        if (mb_strlen($pubdate, 'UTF-8') > 20) {
            return '发布日期不能超过20个字符';
        }
        $pubdate = date('Y-m-d H:i:s', strtotime($pubdate));
        $data['pubdate'] = strtotime($pubdate);
        //文档关键字
        $data['keywords'] = input('keywords');
        if (empty($data['keywords'])) {
            return '文档关键字不能为空';
        }
        if (mb_strlen($data['keywords'], 'UTF-8') > 60) {
            return '文档关键字不能超过60个字符';
        }
        //文档摘要
        $data['description'] = input('description');
        if (empty($data['description'])) {
            return '文档摘要不能为空';
        }
        if (mb_strlen($data['description'], 'UTF-8') > 250) {
            return '文档摘要不能超过250个字符';
        }
        //阅读权限
        if (!isset($input['power'])) {
            return '请选择阅读权限后再次提交数据';
        }
        $power = $input['power'];
        if (!empty($power)) {
            $power_arr = array_keys($power);
            $data['arcrank'] = implode(',', $power_arr);
        } else {
            $data['arcrank'] = '';
        }
        //文档访问url
        $data['redirecturl'] = input('redirecturl');
        if (mb_strlen($data['redirecturl'], 'UTF-8') > 50) {
            return '输入的存储文件名不能超过50个字符';
        }
        //文档栏目所属类型
        $data['channel'] = input('column_type');
        if (empty($data['channel'])) {
            return '非法访问';
        }
        //文档阅读需要的钱数
        $data['money'] = 0;
        //用户类型
        $data['user_type'] = 2;
        //用户id
        $data['userid'] = Session::get('admin')['id'];
        //文档模板
        $data['templet'] = input('template');
        if (empty($data['templet'])) {
            return '文档模板不能为空';
        }
        if (mb_strlen($data['templet'], 'UTF-8') > 60) {
            return '文档模板字数不能超过20个字符';
        }
        //短标题
        $data['shorttitle'] = '';
        return $data;
    }


    /**
     * @param $article_id 文档id
     * @param $channel 栏目类型
     * @param $column 栏目id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 生成静态文件方法
     */
    private function makeHtml($article_id, $channel, $column)
    {
        //判断是否需要生成静态页面
        if (!isset($this->input['makehtml'])) {
            return true;
        }
        //实例化Html类
        $make = new Make();
        //获取发布文档后生成html的内容设置
        $type_string = config::get('cfg_make_html_type');
        $type_arr = explode(',', $type_string);
        //判断内容
        if (in_array(1, $type_arr)) {
            //生成文档html页面
            $make->htmlArticle($article_id, $channel, 1, 1, 1);
        }
        if (in_array(2, $type_arr)) {
            //生成所属栏目首页及列表页
            $make->htmlColumn($column, 10000, 1, 1, 1);
        }
        if (in_array(3, $type_arr)) {
            //更新网页首页
            $make->htmlIndex(2);
        }
        if (in_array(4, $type_arr)) {
            //更新tag标签页
            $html = new Html();
            $html->makeTagToArticle($article_id);
        }
    }

    /**
     * @param $article_id
     * @param $column_id
     * @return bool|string
     * Description 发布文档的Tag标签处理
     */
    private function pushArticleTagHandle($article_id, $column_id)
    {
        //处理文档tag标签
        $tag_string = input('tag');
        //处理中文，
        $tag_string = str_replace('，', ',', $tag_string);
        //转换成tag 数组
        if (!empty($tag_string)) {
            $tag_arr = explode(',', $tag_string);
        } else {
            $tag_arr = [];
        }
        $tag = new Tag();
        //验证每一个tag标签字数
        foreach ($tag_arr as $v) {
            if (mb_strlen($v, 'UTF-8') > 40) {
                return 'tag标签不能超过40个字符';
            }
        }
        //添加tag数据到数据表
        return $tag->relateAddTag($article_id, $column_id, $tag_arr);
    }

    /**
     * @param $article_id
     * @param $d
     * @return mixed
     * Description 资源文档处理
     */
    private function articleSourceHandle($article_id, $d)
    {
        //判断文档栏目所属类型
        $column_type = (int)$this->input['column_type'];
        if ($column_type != 4) {
            return true;
        }
        //获取所属栏目类型提交的数据
        if (!isset($this->input['source_img'])) {
            return '请上传资源图像';
        }
        $source_img = $this->input['source_img'];
        if (!isset($this->input['fileurl'])) {
            return '非法访问';
        }
        $fileurl = $this->input['fileurl'];
        if (empty($fileurl)) {
            return '资源下载地址不能为空';
        }
        if (mb_strlen($fileurl, 'UTF-8') > 200) {
            return '资源下载地址不能超过200个字符';
        }
        if (!File::checkUrl($fileurl)) {
            return '资源地址不能正常访问';
        }
        $show = isset($this->input['show']) ? 1 : 2;
        $show_type = isset($this->input['show_type']) ? 2 : 1;
        //设置阿里云的Oss bucket
        $bucket = 'compress-sucaibiz';
        //获取资源链接的文件后缀名
        $ext = File::getRemoteFileExt($fileurl);
        //组合上传到阿里云Oss的object
        $object = date('Y-m-d', time()) . '/' . getNewFileName() . '.' . $ext;
        //组合上传后的下载地址
        $downhost = 'http://compress.sucai.biz' . '/' . $object;
        //获取资源链接的文件名
        $filename = File::getRemoteFileName($fileurl);
        //组合文件真实存储目录
        $real_name = $bucket . ':' . $object;
        //创建离线下载任务
        $lixian_info = [
            'function' => 'liXianDown',
            'url' => $fileurl,
            'user_type' => 2,
            'user_id' => Session::get('admin')['id'],
            'bucket' => $bucket,
            'object' => $object
        ];
        task('publish', $lixian_info);
        $resource_img = [];
        //资源展示图上传到阿里云OSS
        foreach ($source_img as $value) {
            //获取图像大小
            $info = getimagesize($value);
            //上传缩略图到阿里云OSS
            File::$filename = $value;
            File::uplodOss();
            $url = File::$url;
            $resource_img[] = "<img src='" . $url . "' style=' width:" . $info[0] . ";height:" . $info[1] . "' />";
        }
        //组合数据添加到资源扩展表
        $s = [
            'article_id' => $article_id,
            'column_id' => $d['column_id'],
            'synopsis' => $d['description'],
            'resource_img' => implode(',', $resource_img),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'url' => $fileurl,
            'resource_down' => $downhost,
            'resource_name' => $d['title'],
            'resource_filename' => $filename,
            'templet' => $d['templet'],
            'show_type' => $show_type,
            'resource_show' => '',
            'is_show' => $show,
            'real_name' => $real_name
        ];

        $article = model('Article');
        //添加到扩展资源表
        return $article->addArticleResource($s);
    }

    /**
     * @param $article_id
     * @param $d
     * @return bool|string
     * Description 文档类型处理方法
     */
    private function articleBodyHandle($article_id, $d)
    {
        //判断文档栏目所属类型
        $column_type = (int)$this->input['column_type'];
        if ($column_type != 1) {
            return true;
        }
        if (!isset($this->input['content'])) {
            return '非法访问';
        }
        //文档内容
        $content = $this->input['content'];
        if (empty($content)) {
            return '文档内容不能为空';
        }
        $content = getContent($content, ['user_type' => 2, 'user_id' => Session::get('admin')['id']], ['article_id' => $article_id, 'article_title' => $d['title']]);
        //组合数据添加到文档扩展表
        $b = [
            'article_id' => $article_id,
            'column_id' => $d['column_id'],
            'body' => $content,
            'redirecturl' => $d['redirecturl'],
            'templet' => $d['templet'],
            'user_ip' => $_SERVER['REMOTE_ADDR']
        ];
        $article = model('Article');
        return $article->addArticleBody($b);
    }

    private function articleImageHandle($article_id, $d)
    {
        //判断文档栏目所属类型
        $column_type = (int)$this->input['column_type'];
        if ($column_type != 2) {
            return true;
        }
        //图集图像
        if (!isset($this->input['images'])) {
            return '非法访问';
        }
        $images_img = $this->input['images'];
        if (empty($images_img)) {
            return '请上传图集内图片';
        }
        //判断是否需要生成压缩包
        if (isset($this->input['is_pack'])) {
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
        File::setArticleInfo($article_id, $d['title']);
        //判断是否需要生成压缩图
        if (!isset($this->input['is_thumb'])) {
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
        $i = [
            'article_id' => $article_id,
            'column_id' => $d['column_id'],
            'width' => $img_info[0],
            'height' => $img_info[1],
            'imgurls' => rtrim($images, ','),
            'mediumimgurl' => rtrim($mediumimgurl, ','),
            'smallimgurl' => rtrim($smallimgurl, ','),
            'imgnum' => count($images_img),
            'templet' => $d['templet'],
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'redirecturl' => $d['redirecturl'],
            'body' => '',
            'packurl' => $zip_url
        ];
        $article = model('Article');
        //添加到文档图集附加表
        return $article->addArticleImages($i);
    }

    private function pushArticleBaiduPush($article_id, $redirecturl)
    {
        //判断是否推送百度链接
        if (!isset($this->input['baidupush'])) {
            return true;
        }
        //判断是否填写访问url,如果没有填写，则手动拼写文档url地址
        if (empty($redirecturl)) {
            $url = self::getArticleUrl($article_id, 0, true, true);
        } else {
            $url = config::get('cfg_hostsite') . $redirecturl;
        }
        $push = new BaiduPush();
        $push->pushUrl([$url]);
    }

    //生成文档手机二维码
    public function createPhoneQr()
    {

    }

    //生成文档小程序二维码
    public function createMiniQr()
    {

    }
}