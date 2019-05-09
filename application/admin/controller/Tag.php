<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/16
 * Time: 11:38
 * Description：TAG标签管理
 */

namespace app\admin\controller;

use app\common\controller\ArticlePush;
use app\model\Article;
use app\model\Tag as Tag_Model;
use app\model\TagList;
use think\Db;

class Tag extends Common implements ArticlePush
{
    public $error;
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $Article_Info
     * @param $data
     * @return bool
     * Description 添加tag标签
     */
    public static function add($Article_Info, $data)
    {
        $Add_List = [];
        //开启数据库事务
        Db::startTrans();
        foreach ($data as $value) {
            $where = ['tag_name' => $value, 'column_id' => $Article_Info['column_id']];
            $Tag_Id = Tag_Model::getField($where, 'id');
            if (!empty($Tag_Id)) {
                Tag_Model::fieldInc(['id' => $Tag_Id], 'total');
            } else {
                //插入tag列表数据
                $Tag_Id = Tag_Model::add([
                    'tag_name' => $value,
                    'column_id' => $Article_Info['column_id'],
                    'count' => 0,
                    'total' => 1,
                    'create_time' => time(),
                    'weekcc' => 0,
                    'daycc' => 0,
                    'monthcc' => 0
                ]);
                if (!$Tag_Id) {
                    Db::rollback();
                    return false;
                }
            }
            //插入tag_list列表数据
            $res = TagList::add([
                'article_id' => $Article_Info['id'],
                'tag_id' => $Tag_Id
            ]);
            if (!$res) {
                Db::rollback();
                return false;
            }
        }
        Db::commit();
        return true;
    }

    /**
     * @param $article_id
     * @param $data
     * @return bool
     * Description 修改tag标签列表方法
     */
    public static function edit($article_id, $data)
    {
        //查询文档数据
        $Article_Info = Article::getOne(['id' => $article_id], 'column_id');
        //查询tag标签id列表
        $Article_Tag_Ids = TagList::getAll(['article_id' => $article_id], 'tag_id');
        $Article_Tag_Ids = array_column($Article_Tag_Ids, 'tag_id');
        $Tag_Ids = Tag_Model::getAll(['column_id' => $Article_Info['column_id'], 'tag_name' => ['in' => $data]], 'id');
        $Tag_Ids = array_column($Tag_Ids, 'id');
        //查找出更改的tag标签id
        $Del_Tag_Ids = array_diff($Article_Tag_Ids, $Tag_Ids);
        Db::startTrans();
        foreach ($Del_Tag_Ids as $value) {
            $res = Tag_Model::fieldDec(['id' => $value], 'total');
            if (!$res) {
                Db::rollback();
                return false;
            }
            $res = TagList::del(['article_id' => $article_id, 'tag_id' => $value]);
            if (!$res) {
                Db::rollback();
                return false;
            }
        }
        Db::commit();
        return true;
    }

    /**
     * @return false|string
     * Description 获取tag标签列表页列表数据
     */
    public function gettaglistjson()
    {
        $Tag_List = Tag_Model::getList([], ' * ', 'id desc');
        return self::ajaxOkdata($Tag_List, 'get data success');
    }

    /**
     * @param $article_id
     * @param $column_id
     * @param $tag_arr
     * @return bool
     * Description 批量新增tag标签
     */
    public function relateAddTag($article_id, $column_id, $tag_arr)
    {
        //开启事务操作
        Db::startTrans();
        //循环tag列表
        foreach ($tag_arr as $key => $value) {
            //首先查询是否已经存在对应tag
            $Tag_Id = Tag_Model::getField(['tag_name' => $value, 'column_id' => $column_id], 'id');
            //存在则增加tag_list表数据,更新
            if (!empty($Tag_Id)) {
                $res = Tag_Model::fieldInc(['id' => $Tag_Id], 'total');
                if (!$res) {
                    Db::rollback();
                    return false;
                }
            } else {
                //不存在则增加tag表后再增加tag_list表数据
                $d = [
                    'tag_name' => $value,
                    'column_id' => $column_id,
                    'create_time' => time()
                ];
                $Tag_Id = Tag_Model::add($d);
                if (!$Tag_Id) {
                    Db::rollback();
                    return false;
                }
            }
            $res = TagList::add(['article_id' => $article_id, 'tag_id' => $Tag_Id]);
            if (!$res) {
                Db::rollback();
                return false;
            }
        }
        Db::commit();
        return true;
    }

    /**
     * @param string $article_id
     * @param string $column_id
     * @param array $tag_list
     * @return bool
     * Description 修改tag标签方法
     */
    public function alterTag($article_id = '', $column_id = '', $tag_list = [])
    {
        if (empty($article_id) || empty($tag_list)) {
            return false;
        }
        //删除tag_list表中数据
        $res = TagList::del(['article_id' => $article_id]);
        if ($res === false) {
            return false;
        }
        //修改引用数量
        foreach ($tag_list as $key => $value) {
            Tag_Model::fielddec([
                'column_id' => $column_id,
                'tag_name' => $value
            ], 'total', 1);
        }
        return $this->relateAddTag($article_id, $column_id, $tag_list);
    }

    /**
     * @return false|string
     * Description 根据栏目和tag关键字获取tag列表
     */
    public function getPushTagList()
    {
        $column_id = input('column_id');
        if (empty($column_id) || !is_numeric($column_id)) {
            return self::ajaxOkdata([], 'data is empty');
        }
        $tag = input('tag');
        if (empty($tag)) {
            return self::ajaxOkdata([], 'data is empty');
        }
        //组合查询条件
        $where = [
            'column_id' => $column_id,
            'tag_name' => [
                'like',
                "%$tag%"
            ]
        ];
        $tag_list = Tag::getAll($where, 'tag_name', 10);
        return self::ajaxOkdata($tag_list, 'get data success');
    }

    public function tag($data){

    }

    public function tagList(){

    }
}