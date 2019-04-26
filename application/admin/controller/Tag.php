<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/16
 * Time: 11:38
 * Description：TAG标签管理
 */

namespace app\admin\controller;

use think\Db;
use app\admin\model\Tag as t;
class Tag extends Common
{
    private $error = '';
    //存储tag模型
    private $tag;
    public function __construct()
    {
        parent::__construct();
        $this->tag = new t();
    }

    public function manage()
    {
        return View('tag_manage');
    }

    public function gettaglistjson()
    {
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $tag = model('Tag');
        $tag_list = $tag->getTagList([], ' * ', $limit);
        foreach ($tag_list as $key => $value) {
            $tag_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        $tag_count = $tag->getTagCount([]);
        $arr = [
            'data' => $tag_list,
            'count' => $tag_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //判断是否存在对应tag
    public function checkExists($tag, $column_id)
    {

    }

    //批量新增tag标签
    public function relateAddTag($article_id, $column_id, $tag_arr)
    {
        //实例化tag Model
        $tag = model('Tag');
        //开启事务操作
        Db::startTrans();
        //循环tag列表
        foreach ($tag_arr as $key => $value) {
            //首先查询是否已经存在对应tag
            $info = $tag->getOne(['tag_name' => $value, 'column_id' => $column_id], 'id');
            //存在则增加tag_list表数据,更新
            if (isset($info['id'])) {
                $id = $info['id'];
                if(!$tag->updateTotal(['id'=>$id])){
                    $this->error = 'update tag total is bad';
                }
            } else {
                //不存在则增加tag表后再增加tag_list表数据
                $d = [
                    'tag_name'=>$value,
                    'column_id'=>$column_id,
                    'create_time'=>time()
                ];
                $id = $tag->insertTag($d);
                if(!$id){
                    $this->error = 'insert tag error';
                }
            }
            if(!$tag->insertTagList(['article_id' => $article_id ,'tag_id' => $id])){
                $this->error = 'insert tag_list error';
            }
        }
        //检查添加过程是否有失败
        if(empty($this->error)){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }

    //修改tag标签方法
    public function alterTag($article_id = '', $column_id = '' ,$tag_list = []){
        if(empty($article_id) || empty($tag_list)){
            return false;
        }
        //删除tag_list表中数据
        $res = Model('TagList')->del(['article_id'=>$article_id]);
        if($res === false){
            return false;
        }
        //修改引用数量
        foreach($tag_list as $key => $value){
            Model('Tag')->fielddec([
                'column_id'=>$column_id,
                'tag_name'=>$value
            ],'total',1);
        }
        return $this->relateAddTag($article_id ,$column_id ,$tag_list);
    }

    /**
     * @return false|string
     * Description 根据栏目和tag关键字获取tag列表
     */
    public function getPushTagList(){
        $column_id = input('column_id');
        if(empty($column_id) || !is_numeric($column_id)){
            return self::ajaxOkdata([],'data is empty');
        }
        $tag = input('tag');
        if(empty($tag)){
            return self::ajaxOkdata([],'data is empty');
        }
        //组合查询条件
        $where = [
            'column_id'=>$column_id,
            'tag_name'=>[
                'like',
                "%$tag%"
            ]
        ];
        $tag_list = Model('Tag')->getAll($where,'tag_name',10);
        return self::ajaxOkdata($tag_list,'get data success');
    }
}