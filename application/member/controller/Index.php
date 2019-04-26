<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/3
 * Time: 14:34
 * Description：会员控制器
 */

namespace app\member\controller;

use app\common\controller\Url;
use app\member\model\Admin;
use app\member\model\Article;
use app\member\model\Column;
use app\member\model\Fens;
use app\member\model\Like;
use SucaiZ\Page;
use think\Cookie;
use think\Request;
use think\Session;
use think\View;

class Index extends Common
{
    private $zmbz_id = 1;
    private $sjbz__id = 54;
    private $sczx_id = 24;
    public function index()
    {
        $id = input('id');
        if (!isset($id)) {
            header('location:/');
        }
        //获取用户基本信息
        $user = new \app\member\model\User();
        $user_info = $user->getUser(['id' => $id], ' * ');
        View::share('info', $user_info);
        //获取用户粉丝数据
        $fens = new Fens();
        $attention_num = $fens->getCount(['fens_id' => $id], 'fens_id');
        $fens_num = $fens->getCount(['uid' => $id], 'uid');
        $fens_data = [
            'attention' => $attention_num,
            'fens_num' => $fens_num
        ];
        View::share('fens', $fens_data);
        //分配登录的用户信息到页面
        View::share('user_info' ,$this->member_info);
        return View('templates/member');
    }

    public function showMain()
    {
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        $type = input('type');
        if (!isset($type) || empty($type)) {
            echo '非法访问';
            die;
        }
        //我的收藏
        if ($type == 'like ') {
            $like = new Like();
            $zmbz_where = $this->getLikeWhere($id ,$this->zmbz_id);
            $zmbz_count = $like->getLikeCount($zmbz_where);
            //获取收藏的手机壁纸数量
            $sjbz_where = $this->getLikeWhere($id ,$this->sjbz__id);
            $sjbz_count = $like->getLikeCount($sjbz_where);
            //获取收藏的素材资讯数量
            $sczx_where = $this->getLikeWhere($id ,$this->sczx_id);
            $sczx_count = $like->getLikeCount($sczx_where);
            //整合数据分配到页面
            $count = [
                'zmbz'=>$zmbz_count,
                'sjbz'=>$sjbz_count,
                'sczx'=>$sczx_count
            ];
            View::share('count' ,$count);
            return View('templates/like_list');
            //我发布的文档
        } else if ($type == 'article') {
            //我的关注
        } else if ($type == 'attention') {
            //获取分页相关数据
            $limit = input('limit');
            if (!isset($limit) || !is_numeric($limit)) {
                $limit = 10;
            }
            $page = input('page');
            if (!isset($page) || !is_numeric($page)) {
                $page = 1;
            }
            //组合分页数据
            $limits = ($page - 1) * $limit . ',' . $limit;
            $fens = new Fens();
            //组合查询条件
            $where =  ['fens_id'=>$id];
            $fens_list = $fens->getFensList($where);
            //循环处理列表数据
            $user = new \app\member\model\User();
            foreach($fens_list as $key => $value){
                $fens_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
                //获取用户信息
                $fens_list[$key]['user'] = $user->getUser(['id'=>$value['uid']]);
                //获取用户粉丝数量
                $fens_list[$key]['user']['fens_num'] = $fens->getCount(['uid'=>$fens_list[$key]['user']['id']] ,'fens_id');
            }
            //获取列表总条数
            $count = $fens->getCount($where);
            //实例化分页类
            $paging = new Page($count ,10);
            //分配分页数据到页面
            View::share('paging' ,$paging->render());
            //分配列表数据到页面
            View::share('list' ,$fens_list);
            return View('templates/attention_list');
            //我的粉丝
        } else if ($type == 'fens') {
            //获取分页相关数据
            $limit = input('limit');
            if (!isset($limit) || !is_numeric($limit)) {
                $limit = 10;
            }
            $page = input('page');
            if (!isset($page) || !is_numeric($page)) {
                $page = 1;
            }
            //组合分页数据
            $limits = ($page - 1) * $limit . ',' . $limit;
            $fens = new Fens();
            //组合查询条件
            $where = ['uid'=>$id];
            $fens_list = $fens->getFensList($where);
            //循环处理列表数据
            $user = new \app\member\model\User();
            foreach($fens_list as $key => $value){
                $fens_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
                //获取用户信息
                $fens_list[$key]['user'] = $user->getUser(['id'=>$value['fens_id']]);
                //获取用户粉丝数量
                $fens_list[$key]['user']['fens_num'] = $fens->getCount(['uid'=>$fens_list[$key]['user']['id']] ,'fens_id');
            }
            //获取我关注的数据分配到页面
            $my_atten = $fens->getFensList(['fens_id'=>$id]);
            $my_atten = array_column($my_atten ,'uid' ,'uid');
            View::share('atten' ,$my_atten);
            //获取列表总条数
            $count = $fens->getCount($where);
            //实例化分页类
            $paging = new Page($count ,10);
            //分配分页数据到页面
            View::share('paging' ,$paging->render());
            //分配列表数据到页面
            View::share('list' ,$fens_list);
            return View('templates/fens_list');
        } else if ($type == 'comment') {

        } else if ($type == 'upload') {

        } else {
            echo '1';
        }
    }

    //显示收藏列表
    public function showLikeList()
    {
        //验证数据
        $id = input('id');
        if(!isset($id) || empty($id) || !is_numeric($id)){
            echo '非法访问';die;
        }
        $type = input('type');
        if(!isset($type) || empty($type) || !is_string($type)){
            echo '非法访问';die;
        }
        //获取分页相关数据
        $limit = input('limit');
        if (!isset($limit) || !is_numeric($limit)) {
            $limit = 10;
        }
        $page = input('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        //组合分页数据
        $limits = ($page - 1) * $limit . ',' . $limit;
        //实例化like模型
        $like = new Like();
        //根据类型组合查询条件
        if($type == 'zm'){
            $where = $this->getLikeWhere($id ,$this->zmbz_id);
        }else if($type == 'sj'){
            $where = $this->getLikeWhere($id ,$this->sjbz__id);
        }else if($type == 'zx'){
            $where = $this->getLikeWhere($id ,$this->sczx_id);
        }
        //查询列表
        $list = $like->getLikeList($where, ' * ', $limits, 'create_time desc');
        //循环处理列表数据
        $article = new Article();
        $column = new Column();
        foreach ($list as $key => $value) {
            $list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            //获取文档名称
            $article_info = $article->getArticleInfo(['id' => $value['article_id']]);
            $list[$key]['article'] = $article_info;
            //获取栏目名称
            $column_info = $column->getColumnInfo(['id'=>$article_info['column_id']] ,'type_name');
            $list[$key]['article']['column'] = $column_info['type_name'];
            //根据作者类型获取作者昵称
            if($article_info['user_type'] == 1){
                $user = new \app\member\model\User();
                $user_info = $user->getUser(['id'=>$article_info['userid']]);
                $user_nickname = $user_info['nickname'];
            }else if($article_info['user_type'] == 2){
                $admin = new Admin();
                $user_info = $admin->getAdminInfo(['id'=>$article_info['userid']]);
                $user_nickname = $user_info['nick_name'];
            }
            $list[$key]['article']['user'] = $user_nickname;
        }
        //分配列表数据到页面
        View::share('list', $list);
        //获取收藏总条数
        $count = $like->getLikeCount($where);
        //实例化分页类
        $paging = new Page($count ,10);
        //分配分页数据到页面
        View::share('paging' ,$paging->render());
        return View('templates/show_like_list');
    }
    //根据栏目id,用户id获取用户收藏表的查询条件
    private function getLikeWhere($uid = 0 ,$column_id = 0){
        if(empty($uid) || empty($column_id)){
            return '';
        }
        $column = new Column();
        //获取收藏的桌面壁纸数量
        $column_list = $column->getColumnList(['parent_id'=>$column_id] ,' id ' ,100);
        $column_list = array_column($column_list ,'id');
        $string = implode(' or channel = ' ,$column_list);
        //组合查询条件
        $where = "uid = $uid and type = 1 and ( channel = $string )";
        return $where;
    }
    //重定向方法
    public function redirecturl(){
        $aid = input('aid');
        $common = new Url();
        if(isset($aid) && !empty($aid) && is_numeric($aid)){
            //根据aid获取文档访问url
            $article = new Article();
            $article_info = $article->getArticleInfo(['id'=>$aid]);
            if(!empty($article_info)){
                //判断文档状态
                if($article_info['is_audit'] == 2){
                    echo '访问的文档未被审核';
                    sleep(2);
                    header('location:/');
                }
                //判断删除状态
                if($article_info['is_delete'] == 2){
                    echo '访问的文档已被删除';
                    sleep(2);
                    header('location:/');
                }
                //获取文档访问链接
                $url = $common->getArticleUrl($aid ,true ,true);
                header('location:'.$url);
            }else{
                echo '访问的文档不存在';
                sleep(2);
                header('location:/');
            }
        }
        $cid = input('cid');
        if(isset($cid) && !empty($cid) && is_numeric($cid)){
            //获取栏目访问链接
            $url = $common->getColumnUrl($cid ,true ,true);
            header('location:'.$url);
        }
    }
    //首页访问计数方法
    public function indexIncr(){
        if(Request::instance()->isPost()){
            //验证数据
            $uid = input('uid');
            if(!isset($uid) || empty($uid) || !is_numeric($uid)){
                echo '非法访问';die;
            }
            $information = new \app\member\model\Information($uid);
            //组合更新条件
            $where = ['uid'=>$uid ,'day'=>date('Y-m-d')];
            $information->incInformationField($where ,'index_num' ,1);
            $information->incInformationField($where ,'pop' ,1);
        }
    }

    //注销账户方法
    public function logout(){
        Session::delete('user');
        Cookie::delete($this->cookie_name);
        if(Request::instance()->isGet()){
            header('location:/index.html');
        }else{
            $url  = input('url');
            return $this->ajaxOk('注销成功',$url);
        }
    }
}