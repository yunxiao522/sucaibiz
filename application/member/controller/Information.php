<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/8/18
 * Time: 13:07
 * Description：会员数据中心
 */

namespace app\member\controller;

use app\common\controller\Url;
use app\member\model\Article;
use app\member\model\Column;
use app\member\model\Down;
use app\member\model\Fens;
use app\member\model\Like;
use SucaiZ\File;
use SucaiZ\Page;
use think\Db;
use think\Request;
use think\View;
use app\member\model\Level;

class Information extends Common
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

    //数据中心首页
    public function index()
    {
        $information = new \app\member\model\Information($this->member_info['id']);
        //获取我的数据
        $mydata = $information->getInformationFind(['uid' => $this->member_info['id'], 'day' => date('Y-m-d')]);
        View::share('mydata', $mydata);
        View::share('type', '');
        return View('templates/information_index');
    }

    //获取我的数据
    public function getMyData()
    {
        //验证数据
        $class = input('class');
        if (!isset($class) || empty($class)) {
            echo '非法访问';
            die;
        }
        $time_type = input('type');
        if (!isset($time_type) || empty($time_type)) {
            echo '非法访问';
            die;
        }
        //根据查询时间类型计算X轴数据
        switch ($time_type) {
            case 1;
                $length = 7;
                break;
            case 2;
                $length = 31;
                break;
            case 3;
                $length = 92;
                break;
        }
        //根据查询类型组合查询的字段
        switch ($class) {
            case 'pop';
                $field = ' pop ';
                break;
            case 'fens';
                $field = ' fens ';
                break;
            case 'index_num';
                $field = ' index_num ';
                break;
            case 'production';
                $field = ' recommend ';
                break;
        }
        $day_data = [];
        $data = [];
        $information = new \app\member\model\Information($this->member_info['id']);
        for ($i = 0; $i < $length; $i++) {
            $day = date('m/d', strtotime("-$i days"));
            $day_data[] = trim($day, '"');
            $year_data = date('Y-m-d', strtotime("-$i days"));
            $where = ['day' => $year_data];
            $data[] = ($information->getInformationField($where, $field));
        }
        //组合返回后的数据
        $arr = [
            'errorcode' => 0,
            'msg' => '获取数据成功',
            'data' => [
                'data' => $data,
                'day' => $day_data
            ]
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //获取我获得的数据
    public function getObtainData()
    {
        //验证数据
        $time_type = input('type');
        if (!isset($time_type) || empty($time_type) || !is_numeric($time_type)) {
            echo '非法访问';
            die;
        }
        //根据类型构建查询的字段
        switch ($time_type) {
            case 1;
                $length = 1;
                break;
            case 2;
                $length = 7;
                break;
            case 3;
                $length = 30;
                break;
            case 4;
                $length = 0;
                break;
        }
        $information = new \app\member\model\Information($this->member_info['id']);
        //构建查询条件
        if($length == 0){
            $where = ['uid'=>$this->member_info['id']];
        }else{
            $where = "uid = " .$this->member_info['id'] ." and create_time <= " .strtotime(date('Y-m-d')) ." and create_time >= " .strtotime(date('Y-m-d' ,strtotime("-$length days")));
        }
        $data = [];
        $data['pop'] = $information->getSum($where ,'pop');
        $data['fens'] = $information->getSum($where ,'fens');
        $data['index_num'] = $information->getSum($where ,'index_num');
        $data['recommend'] = $information->getSum($where ,'recommend');
        $a = [
            'errorcode'=>0,
            'msg'=>'获取数据成功',
            'data'=>$data
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }
    //获取近30天的文档变化数据
    public function getArticleData(){
        //获取分页相关数据
        $limit = input('limit');
        if (!isset($limit) || !is_numeric($limit)) {
            $limit = 10;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合查询条件
        $where = ['user_type'=>1,'userid'=>1,'is_delete'=>1];
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        $article = new Article();
        $list = $article->getArticleList($where ,' * ' ,6);
        //获取文档总数
        $count = $article->getCount($where);
        //实例化分页类
        $paging = new Page($count ,$limit);
        $paging_data = $paging->render();
        if(empty($list)){
            //查询数据
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功'
            ];
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功,但是数据为空',
                'data'=>$list,
                'paging'=>$paging_data
            ];
        }
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }
    //显示我发布的文档统计
    public function myArticle(){
        //每页显示文档条数为10条
        $limit = 10;
        //验证数据
        $class = input('class');
        if(!isset($class) || empty($class) || !is_string($class)){
            $class = 'article';
        }
        $column_id = input('column');
        if(!isset($column_id) || empty($column_id) || !is_numeric($column_id)){
            $column_id = 999;
        }
        $status = input('status');
        if(!isset($status) || empty($status) || !is_numeric($status)){
            $status = 1;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        //获取栏目实例
        $column = new Column();
        //根据class构建查询条件
        switch($class){
            case 'article';
                $where = ['channel_type'=>1];
                break;
            case 'atlas';
                $where = ['channel_type'=>2];
                break;
            case 'resource';
                $where = ['channel_type'=>4];
                break;
        }
        $column_list = $column->getColumnList($where ,' id,type_name ' ,100 ,' id asc ');
        array_unshift($column_list ,['id'=>999,'type_name'=>'全部']);
        //组合查询文档列表条件
        $array_where = [
            'is_delete'=>1,
            'user_type'=>1,
            'userid'=>$this->member_info['id']
        ];
        if($column_id != 999){
            $array_where['column_id'] = $column_id;
        }
        switch ($class){
            case 'article';
                $array_where['channel'] = 1;
                break;
            case 'atlas';
                $array_where['channel'] = 2;
                break;
            case 'resource';
                $array_where['channel'] = 4;
                break;
        }
        switch($status){
            case 2;
                $array_where['is_audit'] = 2;
                break;
            case 3;
                $array_where['is_audit'] = 1;
                break;
            case 4;
                $array_where['is_audit'] = 3;
                break;
            case 5;
                $array_where['draft'] = 1;
                break;
        }
        $article = new Article();
        //获取文档列表数据
        $article_list = $article->getArticleList($array_where ,' id,title,create_time,click,comment_num,pubdate ' ,$limits ,' id desc ');
        //循环处理文档列表数据
        foreach($article_list as $key => $value){
            $article_list[$key]['title'] = cut_str($value['title'] ,16);
        }
        //获取文档总条数
        $article_count = $article->getCount($array_where);
        //实例化分页类
        $paging = new Page($article_count ,$limit);
        $paging_data = $paging->render();
        //分配分页数据到页面
        View::share('page' ,$paging_data);
        View::share('article_list' ,$article_list);
        View::share('column' ,$column_list);
        View::share('class' ,$class);
        View::share('type' ,'myarticle');
        View::share('column_id' ,$column_id);
        View::share('status' ,$status);
        return View('templates/information_myarticle');
    }
    //显示我的收藏
    public function myLike(){
        $type_arr = ['all' ,'privacy' ,'open'];
        $limit = 10;
        $type = input('type');
        if(!isset($type) || empty($type) || !is_string($type) || !in_array($type ,$type_arr)){
            $type = 'all';
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        //获取收藏夹列表
        $like = new Like();
        //组合查询条件
        $where = [
            'user_id'=>[
                ['=',0],
                ['=',$this->member_info['id']],
                'or'
            ]
        ];

        switch ($type){
            case 'privacy';
                $where['type'] = 1;
                break;
            case 'open';
                $where['type'] = 2;
                break;
        }
        $like_list = $like->getLikeClassList($where ,'  * ' ,$limits ,' id desc ');
        //获取我的收藏夹条数
        $count = $like->getLikeClassCount($where);
        //循环处理列表数据
        //获取当前时间戳
        $time = time();
        foreach($like_list as $key => $value){
            if($value['alter_time'] != 0){
                $alter_differ = $value['alter_time'] - $time;
                if($alter_differ < 86400 && $alter_differ > 3600){
                    $alter_differ_time = floor($alter_differ / 3600);
                    $like_list[$key]['alter_time'] = $alter_differ_time .'小时前更新';
                }else if($alter_differ > 60 && $alter_differ <= 3600){
                    $alter_differ_time = floor($alter_differ / 60);
                    $like_list[$key]['alter_time'] = $alter_differ_time .'分钟前更新';
                }else if($alter_differ <= 60){
                    $like_list[$key]['alter_time'] = '刚刚更新';
                }else{
                    $like_list[$key]['alter_time'] = date('Y-m-d' ,$value['alter_time']) .'更新';
                }
            }
            $create_differ = $value['create_time'] - $time;
            if($create_differ < 86400 && $create_differ > 3600){
                $create_differ_time = floor($create_differ / 3600);
                $like_list[$key]['create_time'] = $create_differ_time .'小时前创建';
            }else if($create_differ > 60 && $create_differ <= 3600){
                $create_differ_time = floor($create_differ / 60);
                $like_list[$key]['create_time'] = $create_differ_time .'分钟前创建';
            }else if($create_differ <= 60){
                $like_list[$key]['create_time'] = '刚刚创建';
            }else{
                $like_list[$key]['create_time'] = date('Y-m-d' ,$value['create_time']) .'创建';
            }
            $like_list[$key]['num'] = $like->getLikeCount(['class_id'=>$value['id'],'uid'=>$this->member_info['id']]);
            $like_list[$key]['litpic'] = $this->getLikeClassLitpic($value['id']);
        }
        //获取分页数据
        $page = new Page($count ,$limit);
        $pageing = $page->render();
        //分配列表数据到页面
        View::share('like_list' ,$like_list);
        View::share('count' ,$count);
        View::share('type' ,'mylike');
        View::share('class' ,$type);
        View::share('page' ,$pageing);
        return View('templates/information_mylike');
    }
    //显示我的下载
    public function myDown(){
        $limit = 8;
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        //获取我的下载总条数
        $where = [
            'uid'=>$this->member_info['id']
        ];
        $down = new Down();
        $count = $down->getDownCount($where);
        //获取我的下载列表
        $down_list = $down->getDownList($where ,' * ' ,$limits);
        $article = new Article();
        $user = new \app\member\model\User();
        $url = new Url();
        //循环列表数据
        foreach($down_list as $key => $value){
            $file_info = $value['file_url'];
            $file_ext = File::getRemoteFileExt($file_info);
            $file_ext = strtolower($file_ext);
            if($file_ext == 'zip'){
                $file_info = '/public/png/zip.png';
            }
            $down_list[$key]['url'] = $file_info;
            $article_info = $article->getArticleInfo(['id'=>$value['article_id']] ,' id,title,userid,column_id ');
            $article_info['url'] = $url->getArticleUrl($article_info['id'] ,true,true);
            $down_list[$key]['article_info'] = $article_info;
            $user_info = $user->getUser(['id'=>$article_info['userid'] ,' nickname,face ']);
            $down_list[$key]['user_info'] = $user_info;
            if(in_array($file_ext ,['jpg','jpeg','gif','png','ico'])){
                $down_list[$key]['type'] = '图像';
            }else if(in_array($file_ext ,['7z','rar','zip'])){
                $down_list[$key]['type'] = '压缩包';
            }
        }
        //实例化分页类
        $page = new Page($count ,$limit);
        $pageing = $page->render();
        View::share('count' ,$count);
        View::share('down_list' ,$down_list);
        View::share('page' ,$pageing);
        View::share('type' ,'mydown');
        return View('templates/information_mydown');
    }
    //显示我的关注
    public function myAttention(){

    }
    //根据数据获取url链接方法
    public function getUrl(){
        if(Request::instance()->isPost()){
            //验证前台提交的数据
            $type = input('type');
            if(!isset($type) || empty($type) || !is_string($type)){
                echo '非法访问';
            }
            $id = input('id');
            if(!isset($id) || empty($id) || !is_numeric($id)){
                echo '非法访问';
            }
            $url = new Url();
            if($type == 'article'){
                $getUrl = $url->getArticleUrl($id ,true ,true);
                if($getUrl){
                    $a = [
                        'errorcode'=>0,
                        'msg'=>'获取数据成功',
                        'url'=>$getUrl
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'获取数据失败'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
            }
        }else{
            return [];
        }
    }
    //创建收藏夹方法
    public function createLikeClass(){
        if(Request::instance()->isPost()){
            //验证前台提交的数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';
                die;
            }
            if(empty($name)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹名字不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($name ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹名字不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $type = input('type');
            if(!isset($type) || !is_numeric($type) || empty($type)){
                echo '非法访问';
                die;
            }
            $description = input('description');
            if(empty($description)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹简介不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($description ,'UTF-8') > 200){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹简介不能超过200个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据添加到数据库
            $arr = [
                'name'=>$name,
                'type'=>$type,
                'user_id'=>$this->member_info['id'],
                'create_time'=>time(),
                'create_type'=>2,
                'description'=>$description,
                'praise'=>0,
                'alter_time'=>time()
            ];
            $like = new Like();
            $result = $like->addLikeClass($arr);
            if($result){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'添加成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'添加失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            return View('templates/information_createlikeclass');
        }
    }
    //删除我的收藏夹操作
    public function delLikeClass(){
        if(Request::instance()->isPost()){
            //验证数据
            $id = input('id');
            if(!isset($id) || empty($id) || !is_numeric($id)){
                echo '非法访问';die;
            }
            //组合删除条件
            $where = [
                'id'=>$id
            ];
            $like = new Like();
            //开启数据库事务操作
            Db::startTrans();
            //删除收藏夹信息
            $res = $like->delLikeClass($where);
            if($res) {
                //删除收藏夹内的信息
                $res1 = $like->delLikeInfo(['uid' => $this->member_info['id'], 'class_id' => $id]);
                if ($res1) {
                    Db::commit();
                    $a = [
                        'errorcode' => 0,
                        'msg' => '删除成功'
                    ];
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            }
            Db::rollback();
            $a = [
                'errorcode'=>1,
                'msg'=>'删除失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //编辑我的收藏夹操作
    public function editLikeClass(){
        //验证前台提交的数据
        $id = input('id');
        if(!isset($id) || empty($id) || !is_numeric($id)){
            echo '非法访问';die;
        }
        if(Request::instance()->isPost()){
            //验证前台提交的数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';
                die;
            }
            if(empty($name)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹名字不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($name ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹名字不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $type = input('type');
            if(!isset($type) || !is_numeric($type) || empty($type)){
                echo '非法访问';
                die;
            }
            $description = input('description');
            if(empty($description)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹简介不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($description ,'UTF-8') > 200){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的收藏夹简介不能超过200个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据更新数据库信息
            $where = ['id'=>$id];
            $arr = [
                'name'=>$name,
                'type'=>$type,
                'description'=>$description,
                'alter_time'=>time()
            ];
            $like = new Like();
            $res = $like->editLikeClass($where ,$arr);
            if($res){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            //组合查询条件
            $where = ['id'=>$id];
            //查询信息
            $like = new Like();
            $like_class_info = $like->getLikeClassInfo($where ,' * ');
            View::share('like_class_info' ,$like_class_info);
            return View('templates/information_editlikeclass');
        }
    }
    //展示收藏夹内容方法
    public function showLikeClass(){
        $limit = 8;
        //验证参数
        $id = input('id');
        if(!isset($id) || empty($id) || !is_numeric($id)){
            echo '非法访问';die;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        $like = new Like();
        //判断id是否存在和会员是否对应,不存在或者不存在对应关系，则强制$id = 1;避免出现错误
        if(!in_array($id ,[1,2])){
            $c_where = [
                'id'=>$id,
                'user_id'=>$this->member_info['id']
            ];
            $c = $like->getLikeClassCount($c_where);
            if($c == 0){
                $id = 1;
            }
        }
        //查询我的收藏夹列表
        //计算我的收藏夹总条数
        $count = $like->getLikeClassCount(['user_id'=>$this->member_info['id']]);
        View::share('count' ,$count);
        $list_where = [
            'user_id'=>[
                ['eq' ,0],
                ['eq' ,$this->member_info['id']],
                'or'
            ]
        ];
        $like_class_list = $like->getLikeClassList($list_where ,' * ' ,100 ,' id asc ');
        //循环我的收藏夹列表数据
        foreach($like_class_list as $key => $value){
            $like_class_list[$key]['num'] = $like->getLikeCount(['class_id'=>$value['id'],'uid'=>$this->member_info['id']]);
            //获取收藏夹封面
            $like_class_list[$key]['litpic'] = $this->getLikeClassLitpic($value['id']);
        }
        View::share('like_class_list' ,$like_class_list);
        View::share('id' ,$id);
        //查询收藏夹详细信息
        $where = ['id'=>$id];
        $like_class_info = $like->getLikeClassInfo($where ,' * ');
        //获取收藏夹内文档总条数
        $num = $like->getLikeCount(['class_id'=>$like_class_info['id'] ,'uid'=>$this->member_info['id']]);
        $like_class_info['num'] = $num;
        $create_time = $like_class_info['create_time'];
        $like_class_info['create_time'] = $this->disposeLikeClassTime($create_time ,'创建');
        if($like_class_info['alter_time'] == 0){
            $like_class_info['alter_time'] =    $this->disposeLikeClassTime($like_class_info['create_time'] ,'更新');
        }else{
            $like_class_info['alter_time'] = $this->disposeLikeClassTime($like_class_info['alter_time'] ,'更新');
        }
        View::share('like_class_info' ,$like_class_info);
        //获取收藏夹里的文件列表
        $like_list_where = [
            'uid'=>$this->member_info['id'],
            'class_id'=>$like_class_info['id']
        ];
        $like_list = $like->getLikeList($like_list_where ,' * ' ,$limits ,' id desc ');
        //循环收藏列表数据
        $article = new Article();
        $user = new \app\member\model\User();
        $column = new Column();
        $url = new Url();
        foreach($like_list as $key => $value){
            $like_list[$key]['article_info'] = $article->getArticleInfo(['id'=>$value['article_id']] ,' * ');
            $like_list[$key]['article_info']['url'] = $url->getArticleUrl($like_list[$key]['article_info']['id'] ,true);
            $like_list[$key]['user_info'] = $user->getUser(['id'=>$like_list[$key]['article_info']['userid']]);
            $like_list[$key]['column_info'] = $column->getColumnInfo(['id'=>$value['channel']]);
        }
        View::share('like_list' ,$like_list);
        //获取分页数据
        $page = new Page($num ,$limit);
        $pageing = $page->render();
        View::share('page' ,$pageing);
        return View('templates/information_showlikeclass');
    }
    //处理收藏夹时间方法
    private function disposeLikeClassTime($time_data ,$text ){
        //获取当前时间戳
        $time = time();
        $differ =$time - $time_data;
        //处理创建时间和更新时间相关信息
        if($differ < 86400 && $differ > 3600){
            $differ_time = floor($differ / 3600);
            $time_text = $differ_time .'小时前' .$text;
        }else if($differ > 60 && $differ <= 3600){
            $create_differ_time = floor($differ / 60);
            $time_text = $create_differ_time .'分钟前' .$text;
        }else if($differ <= 60){
            $time_text = '刚刚' .$text;
        }else{
            $time_text = date('Y-m-d' ,$time_data) .$text;
        }
        return $time_text;
    }
    //查询文档父级栏目id方法
    public function getParentColumns(){
        if(Request::instance()->isPost()){
            //验证数据
            $column_id = input('column');
            if(!isset($column_id) || empty($column_id) || !is_numeric($column_id)){
                echo '非法访问';die;
            }
            //获取栏目信息
            $column = new Column();
            $column_info = $column->getColumnInfo(['id'=>$column_id]);
            if(!empty($column_info)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'获取数据成功',
                    'data'=>$column_info['parent_id']
                ];
                return  json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'获取数据失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }
    }
    //删除我的收藏夹内容方法
    public function delLikeContent(){
        //验证前台提交的数据
        $id = input('id');
        if(!isset($id) || empty($id) || !is_numeric($id)){
            echo '非法访问';die;
        }
        //组合删除条件
        $where = ['id'=>$id];
        $like = new Like();
        $res = $like->delLikeInfo($where);
        if($res){
            $a = [
                'errorcode'=>0,
                'msg'=>'删除成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'删除失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //获取收藏夹封面图像方法
    private function getLikeClassLitpic($like_class_id = 0){
        if(empty($like_class_id)){
            return '';
        }
        //组合查询条件，获取收藏夹内容列表
        $where = [
            'uid'=>$this->member_info['id'],
            'class_id'=>$like_class_id
        ];
        $like = new Like();
        $like_list = $like->getLikeList($where ,' article_id ' ,4  ,' id desc ');
        //循环内容列表，根据文档id获取文档缩略图
        $litpic_list = [];
        $article = new Article();
        foreach($like_list as $key => $value){
            $article_info = $article->getArticleInfo(['id'=>$value['article_id']] ,'litpic');
            $litpic_list[] = $article_info['litpic'];
        }
        return $litpic_list;
    }
    //我关注的
    public function attention(){
        $limit = 12;
        //验证前台的数据
        $class = input('class');
        if(!isset($class) || empty($class) || !is_string($class)){
            $class = 'dynamic';
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        //获取动态数据
        $getDynamic = function ($limit ,$limits){
            //获取我关注的人的列表
            $fens_where = [
                'fens_id'=>$this->member_info['id']
            ];
            $fens = new Fens();
            $user_list = $fens->getFensList($fens_where ,' uid ' ,10000 ,' create_time desc ');
            $user_id_list = array_column($user_list ,'uid');
            //组合查询文档列表条件
            $article_where = [
                'userid'=>[
                    'in',
                    $user_id_list
                ],
                'is_delete'=>1,
                'is_audit'=>[
                    ['eq',1],
                    ['eq',3],
                    'or'
                ]
            ];
            $article = new Article();
            $article_list = $article->getArticleList($article_where ,' id,column_id,title,litpic,click,comment_num,recommend,pubdate,userid ' ,$limits);
            //循环列表数据
            $column = new Column();
            $user = new \app\member\model\User();
            $fens = new Fens();
            $url = new Url();
            foreach($article_list as $key => $value){
                //获取栏目相关数据
                $column_info = $column->getColumnInfo(['id'=>$value['column_id']]);
                $article_list[$key]['column_info'] = $column_info;
                //获取作者相关数据
                $user_info = $user->getUser(['id'=>$value['userid']]);
                $article_list[$key]['user_info'] = $user_info;
                //获取用户粉丝和创作数量
                $article_list[$key]['user_info']['create'] = $article->getCount(['userid'=>$user_info['id'],'is_delete'=>1,'is_audit'=>[['eq',1],['eq',3],'or']],'id');
                $article_list[$key]['user_info']['fens'] = $fens->getCount(['uid'=>$user_info['id']]);
                //获取文档跳转链接
                $article_list[$key]['url'] = $url->getArticleUrl($value['id'] ,true ,false);
            }
            //获取总条数
            $count = $article->getCount($article_where);
            //实例化分页类
            $page = new Page($count ,$limit);
            $pageing = $page->render();
            $arr = [
                'list'=>$article_list,
                'page'=>$pageing,
            ];
            return $arr;
        };
        //获取关注的用户列表
        $getAttention = function (){
            //获取我关注的人 的列表
            $fens_where = [
                'fens_id'=>$this->member_info['id']
            ];
            $fens = new Fens();
            $user_list = $fens->getFensList($fens_where ,' uid ' ,10000 ,' create_time desc ');
            //循环列表数据,获取用户详细信息
            $user = new \app\member\model\User();
            $article = new Article();
            $fens = new Fens();
            $url = new Url();
            $user_info_list = [];
            foreach($user_list as $key => $value){
                $user_info= $user->getUser(['id'=>$value['uid']] ,' id,litpic,nickname,signature,description ');
                //获取关注用户的创作及粉丝数量
                $user_info['create'] = $article->getCount(['userid'=>$user_info['id'],'is_delete'=>1,'is_audit'=>[['eq',1],['eq',3],'or']],'id');
                $user_info['fens'] = $fens->getCount(['uid'=>$user_info['id']]);
                //获取用户发布的最新三篇文章
                $user_info['article'] = $article->getArticleList(['userid'=>$user_info['id'],'is_delete'=>1,'is_audit'=>[['eq',1],['eq',3],'or']] ,' id,title,litpic,column_id ' ,2 ,' pubdate desc ');
                //循环获取文档访问链接
                foreach($user_info['article'] as $key => $value){
                    $user_info['article'][$key]['url'] = $url->getArticleUrl($value['id'] ,true ,false);
                }
                $user_info_list[] = $user_info;
            }
            $arr = [
                'list'=>$user_info_list,
                'page'=>''
            ];
            return $arr;
        };
        //获取我的粉丝数据
        $getFens = function (){
            //获取我关注的人 的列表
            $fens_where = [
                'uid'=>$this->member_info['id']
            ];
            $fens = new Fens();
            $user_list = $fens->getFensList($fens_where ,' fens_id ' ,10000 ,' create_time desc ');
            //循环列表数据,获取用户详细信息
            $user = new \app\member\model\User();
            $user_info_list = array();
            foreach($user_list as $key => $value){
                $user_info_list[] = $user->getUser(['id'=>$value['fens_id']] ,' id,litpic,nickname ');
            }
            return $user_info_list;
        };
        switch($class){
            case 'dynamic';
                $list = $getDynamic($limit,$limits);
                break;
            case 'member';
                $list = $getAttention();
                break;
            case 'fens';
                $list = $getFens();
                break;
            case 'like';
                $list = [];
                break;
        }
//        dump($list);
        View::share('member_info' ,$this->member_info);
        View::share('class' ,$class);
        View::share('type' ,'attention');
        View::share('list' ,$list['list']);
        View::share('page' ,$list['page']);
        return View('templates/information_attention');
    }
}