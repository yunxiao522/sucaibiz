<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/3
 * Time: 12:49
 * Description：评论管理
 */

namespace app\admin\controller;

use app\model\Comment as Comment_Model;
use app\model\CommentKey;
use app\model\User;
use app\model\Article;
use think\Request;
use SucaiZ\config;

class Comment extends Common
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @return false|string
     * Description 获取评论列表数据
     */
    public function getCommentList()
    {
        $where = [];
        $aid = input('aid');
        if (isset($aid) && is_numeric($aid)) {
            $where['aid'] = $aid;
        }
        $parent_id = input('parent_id');
        if (isset($parent_id) && is_numeric($parent_id)) {
            $where['parent_id'] = $parent_id;
        }
        $uid = input('uid');
        if (isset($uid) && is_numeric($uid)) {
            $where['uid'] = $uid;
        }
        $status = input('status');
        if(!empty($status) && isset(Comment_Model::$comment_status[$status])){
            $where['status'] = $status;
        }
        $Comment_List = Comment_Model::getList($where, '*', 'id desc');
        foreach ($Comment_List['data'] as $key => $value) {
            $Comment_List['data'][$key]['nickname'] = User::getField(['id' => $value['uid']], 'nickname', 'id desc', true);
            $Comment_List['data'][$key]['title'] = Article::getField(['id'=>$value['aid']], 'title', 'id desc', true);
        }
        return self::ajaxOkdata($Comment_List, 'get data success');
    }

    /**
     * @return false|string
     * Description 获取关键词列表数据
     */
    public function getCommentKey()
    {
        $keyword = input('keyword');
        $where = [];
        if (!empty($keyword)) {
            $where = ['content' => [
                'like',
                "%$keyword%"
            ]];
        }
        $Comment_Key_List = CommentKey::getList($where, " * ", 'id Desc');
        return self::ajaxOkdata($Comment_Key_List, 'get data success');
    }

    /**
     * @return false|string
     * Description 删除评论关键词
     */
    public function delCommentKey()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $result = CommentKey::del($where);
        if ($result) {
            $this->refreshCommentKey();
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改评论关键词
     */
    public function alterCommentKey()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            //获取验证数据
            $data = $this->checkCommentKeyForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['alter_time'] = time();

            $result = CommentKey::edit($where, $data);
            if ($result) {
                $this->refreshCommentKey();
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            //获取关键词详细信息
            $Comment_Info = CommentKey::getOne($where);
            $this->assign('id', $id);
            $this->assign('comment_info', $Comment_Info);
            return View('Comment_alter_key');
        }
    }

    /**
     * @return array|string|true
     * Description 验证评论关键词表单数据
     */
    protected function checkCommentKeyForm()
    {
        $res = $this->validate(input(), [
            'filter_name' => 'require|max:20',
            'content' => 'require|max:50'
        ], [
            'filter_name.require' => '输入的过滤名称不能为空',
            'filter_name.max' => '输入的过滤名称不能超过20个字符',
            'content.require' => '输入的关键词不能为空',
            'content.max' => '输入的关键词不能超过50个字符'
        ]);
        if (true !== $res) {
            return $res;
        }
        $data = [];
        $data['filter_name'] = input('filter_name');
        $data['content'] = input('content');
        return $data;
    }

    /**
     * @return false|string|\think\response\View
     * Description 新增评论关键词
     */
    public function addCommentKey()
    {
        if (Request::instance()->isPost()) {
            //获取验证前台数据
            $data = $this->checkCommentKeyForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['create_time'] = time();
            $result = CommentKey::add($data);
            if ($result) {
                $this->refreshCommentKey();
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            return View('Comment_add_key');
        }
    }

    /**
     * @return false|string
     * Description 修改评论状态
     */
    public function alterCommentStatus()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $status = input('status');
        if (!isset(Comment_Model::$comment_status[$status])) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $d = [
            'status' => $status,
            'alter_time' => time()
        ];
        $result = Comment_Model::edit($where, $d);
        if ($result) {
            return self::ajaxOk('修改成功');
        } else {
            return self::ajaxError('修改失败');
        }
    }

    /**
     * @return false|string
     * Description 删除评论方法
     */
    public function delCommentInfo()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $result = Comment_Model::del($where);
        if ($result) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * Description 批量删除评论
     */
    public function dellMoreCommentInfo()
    {
        $ids = input()['ids'];
        if (!isset($ids) || !is_array($ids)) {
            return self::ajaxError('非法访问');
        }
        $res = Comment_Model::del([
            'id'=>[
                'in',
                $ids
            ]
        ]);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * Description 刷新评论关键词缓存
     */
    public function refresCommentKeyCache()
    {
        if ($this->refreshCommentKey()) {
            return self::ajaxOk('刷新成功');
        } else {
            return self::ajaxError('刷新失败');
        }
    }

    /**
     * @return bool
     * Description 刷新评论关键词方法
     */
    private function refreshCommentKey()
    {
        $Redis = getRedis();
        $Comment_Key_List = CommentKey::getAll([], " content ", 100000, 'id Desc');
        $comment_key_arr = [];
        foreach ($Comment_Key_List as $key => $value) {
            $comment_key_arr[] = $value['content'];
        }
        $comment_key_arr = array_unique($comment_key_arr);
        $res = $Redis->set(config::get('cfg_member_comment_key'), json_encode($comment_key_arr, JSON_UNESCAPED_UNICODE));
        return $res;
    }

}