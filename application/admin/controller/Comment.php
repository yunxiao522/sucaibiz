<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/3
 * Time: 12:49
 * Description：评论管理
 */
namespace app\admin\controller;
use SucaiZ\config;
use think\Request;
use think\Db;
class Comment extends Common{
    public function __construct()
    {
        parent::__construct();

    }

    //评论管理
    public function manage(){
        return View('Comment_show_manage');
    }

    //获取评论列表数据
    public function getCommentList(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $status = input('status');
        $where = [];
        $Comment = model('Comment');
        $table_pprefix = config('database.prefix');
        $comment_table_name = $table_pprefix .$Comment->table_name;
        if(isset($status) && is_numeric($status)){
            $status_field = $comment_table_name .'.status';
            if($status == 2){
                $where[$status_field] = 2;
            }else if($status == 1){
                $where[$status_field] = 1;
            }
        }
        $aid = input('aid');
        if(isset($aid) && is_numeric($aid)){
            $where['aid']=$aid;
        }
        $parent_id = input('parent_id');
        if(isset($parent_id) && is_numeric($parent_id)){
            $where['parent_id'] = $parent_id;
        }
        $uid = input('uid');
        if(isset($uid) && is_numeric($uid)){
            $where['uid'] = $uid;
        }

        $Comment = model('Comment');
        //设置查询的表字段
        $Comment_list = $Comment->getCommentList($where, " c.*,u.nickname,a.title ", $limit, 'c.id DESC');
        $Comment_count = $Comment->getCommentCount($where);
        foreach ($Comment_list as $key => $value) {
            $Comment_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if($value['status'] == 2){
                $Comment_list[$key]['status_t'] = '禁用';
            }else{
                $Comment_list[$key]['status_t'] = '启用';
            }
            if(!empty($value['alter_time'])){
                $Comment_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
        }
        $arr = [
            'data' => $Comment_list,
            'count' => $Comment_count,
            'code' => 0,
            'page' =>input('page'),
            'limit'=>input('limit')
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //评论关键词维护
    public function showCommentKey(){
        return View('Comment_show_key');
    }

    //获取关键词列表数据
    public function getCommentKey(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $keyword = input('keyword');
        if(!empty($keyword)){
            $where = " filter_name like '%$keyword%' or content like '%$keyword%' ";
        }
        $Comment_key = model('Comment');
        //设置查询的表字段
        $Comment_key_list = $Comment_key-> getCommentKeyList($where, " * ", $limit, 'id Desc');
        $Comment_key_count = $Comment_key->getCommentKeyCount($where);
        //获取会员等级列表
        foreach ($Comment_key_list as $key => $value) {
            $Comment_key_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if(isset($value['alter_time'])){
                $Comment_key_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
        }
        $arr = [
            'data' => $Comment_key_list,
            'count' => $Comment_key_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //删除评论关键词
    public function delCommentKey(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        $comment = model('Comment');
        $result = $comment->delCommentKey($where);
        if($result){
            $a['errorcode'] = 0;
            $a['msg'] = '删除成功';
            $this->refreshCommentKey();
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a['errorcode'] = 1;
            $a['msg'] = '删除失败';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //修改评论关键词
    public function alterCommentKey(){
        $id = input('id');
        if(!isset($id) || !is_numeric($id)){
            echo '非法访问';
            die;
        }
        if(Request::instance()->isPost()){
            //验证前台提交数据
            $filter_name = input('filter_name');
            if(!isset($filter_name)){
                echo '非法访问';
                die;
            }
            if ($filter_name == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的过滤名称不能为空';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($filter_name ,'UTF-8') > 20){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的过滤名称不能超过20个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $content = input('content');
            if(!isset($content)){
                echo'非法访问';
                die;
            }
            if($content == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的关键词不能为空';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($content ,'UTF-8') >50){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的关键词不能超过50个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $where = ['id'=>$id];
            $comment = model('Comment');
            $b = [
                'filter_name'=>$filter_name,
                'content'=>$content,
                'alter_time'=>time()
            ];
            $result = $comment->alterCommentKeyInfo($where ,$b);
            if($result){
                $a['errorcode'] = 0;
                $a['msg'] = '修改成功';
                $this->refreshCommentKey();
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a['errorcode'] = 1;
                $a['msg'] = '修改失败';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $where = ['id'=>$id];
            $comment = model('Comment');
            $comment_info = $comment->getCommentKeyInfo($where);
            $this->assign('id' ,$id);
            $this->assign('comment_info' ,$comment_info);
            return View('Comment_alter_key');
        }
    }
    //新增评论关键词
    public function addCommentKey(){
        if(Request::instance()->isPost()){
            //验证前台提交数据
            $filter_name = input('filter_name');
            if(!isset($filter_name)){
                echo '非法访问';
                die;
            }
            if ($filter_name == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的过滤名称不能为空';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($filter_name ,'UTF-8') > 20){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的过滤名称不能超过20个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $content = input('content');
            if(!isset($content)){
                echo'非法访问';
                die;
            }
            if($content == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的关键词不能为空';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($content ,'UTF-8') >50){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的关键词不能超过50个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $comment = model('Comment');
            $b = [
                'filter_name'=>$filter_name,
                'content'=>$content,
                'create_time'=>time()
            ];
            $result = $comment->addCommentKeyInfo($b);
            if($result){
                $a['errorcode'] = 0;
                $a['msg'] = '新增成功';
                $this->refreshCommentKey();
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a['errorcode'] = 1;
                $a['msg'] = '新增失败';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            return View('Comment_add_key');
        }
    }
    //修改评论状态
    public function alterCommentStatus(){
        $id = input('id');
        $status = input('status');
        if(!isset($id) || !is_numeric($id)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        $d=[
            'status'=>$status,
            'alter_time'=>time()
        ];
        $comment = model('Comment');
        $result = $comment->alterCommentInfo($where ,$d);
        if($result){
            $a['errorcode'] = 0;
            $a['msg'] = '修改成功';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a['errorcode'] = 1;
            $a['msg'] = '修改失败';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //删除评论方法
    public function delCommentInfo(){
        $id = input('id');
        if(!isset($id) || !is_numeric($id)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        $comment = model('Comment');
        $result = $comment->delCommentInfo($where);
        if($result){
            $a['errorcode'] = 0;
            $a['msg'] = '删除成功';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a['errorcode'] = 1;
            $a['msg'] = '删除失败';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //批量删除评论方法
    public function dellMoreCommentInfo(){
        $ids = input()['ids'];
        if(!isset($ids) || !is_array($ids)){
            echo '非法访问';
            die;
        }
        //开启数据库事务操作
        Db::startTrans();
        $comment = model('Comment');
        $status = [];
        foreach($ids as $key => $value){
            if(is_numeric($value)){
                $where = ['id' => $value];
            }
            $result = $comment->delCommentInfo($where);
            if($result){
                $status[] = 1;
            }else {
                $status[] = 2;
            }
        }
        //判断修改状态值，如果存在删除失败则回滚事务，否则则提交事务
        if(in_array(2 ,$status)){
            //回滚事务
            Db::rollback();
            $a['errorcode'] = 1;
            $a['msg'] = '删除失败';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            //提交事务
            Db::commit();
            $a['errorcode'] = 0;
            $a['msg'] = '删除成功';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //刷新评论关键词缓存
    public function refresCommentKeyCache(){
        if($this->refreshCommentKey()){
            $a['errorcode'] = 0;
            $a['msg'] = '刷新成功';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a['errorcode'] = 1;
            $a['msg'] = '刷新失败';
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //刷新评论关键词方法
    private function refreshCommentKey(){
        $redis = getRedis();
        $comment = model('Comment');
        $Comment_key_list = $comment-> getCommentKeyList([], " content ", 100000, 'id Desc');
        $comment_key_arr = [];
        foreach($Comment_key_list as $key => $value){
            $comment_key_arr[] = $value['content'];
        }
        $comment_key_arr = array_unique($comment_key_arr);
        $res = $redis->set(config::get('cfg_member_comment_key') ,json_encode($comment_key_arr ,JSON_UNESCAPED_UNICODE));
        return $res;

    }

}