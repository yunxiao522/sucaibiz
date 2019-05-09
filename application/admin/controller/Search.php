<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/10 0010
 * Time: 1:57
 */

namespace app\admin\controller;

use app\common\controller\ArticlePush;
use app\model\Search as Search_Model;
use app\model\SearchColumn;
use app\model\SearchHistory;
use app\model\Column;
use think\Db;
use think\Request;
use think\View;

class Search extends Common implements ArticlePush
{
    public function __construct()
    {
        parent::__construct();
    }

    public function column(){
        return view('column');
    }

    /**
     * @return false|string
     * Description 获取搜索分类数据
     */
    public function getColumn(){
        $list = SearchColumn::getList([],'*',' id desc ');
        foreach ($list['data'] as $key => $value){
            $list['data'][$key]['cid'] = Column::getField(['id'=>$value['cid']], 'type_name');
        }
        return $this->ajaxOkdata($list, 'get data success');
    }

    /**
     * @return false|string
     * Description 获取搜索列表数据
     */
    public function getKeyword(){
        $keyword = input('keyword');
        $where = [];
        if($keyword){
            $where = [
                'keyword'=>[
                    'like',
                    "%$keyword%"
                ]
            ];
        }
        $list = Search_Model::getList($where,'*','alter_time desc');
        return $this->ajaxOkdata($list);
    }

    /**
     * @return false|string
     * Description 删除搜索词
     */
    public function delKeyword(){
        $id = input('id');
        if(!$id){
            return $this->ajaxError('参数错误');
        }
        Db::startTrans();
        $res = Search_Model::del(['id'=>$id]);
        if(!$res){
            Db::rollback();
            return $this->ajaxError('删除失败');
        }
        $res = SearchHistory::del(['sid'=>$id]);
        if($res){
            Db::commit();
            return $this->ajaxOk('删除成功');
        }else{
            Db::rollback();
            return $this->ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * Description 修改搜索分类状态
     */
    public function altercolumnstatus(){
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return $this->ajaxError('参数错误');
        }
        $status = input('status');
        if(!$status){
            return $this->ajaxError('参数错误');
        }
        $res = SearchColumn::edit(['id'=>$id],['status'=>$status,'alter_time'=>time()]);
        if($res){
            return $this->ajaxOk('修改成功');
        }else{
            return $this->ajaxError('修改失败');
        }
    }

    /**
     * @return false|string
     * Description 删除搜索分类
     */
    public function delcolumn(){
        $id = input('id');
        if(!$id){
            return $this->ajaxError('参数错误');
        }
        $res = SearchColumn::del(['cid'=>$id]);
        if($res){
            return $this->ajaxOk('删除成功');
        }else{
            return $this->ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * Description 添加搜索分类
     */
    public function addcolumn(){
        if(Request::instance()->isPost()){
            $cid = input('cid');
            if(!$cid){
                return $this->ajaxError('参数错误');
            }
            $tid = Column::getField(['id'=>$cid],'channel_type');
            if(!$tid){
                return $this->ajaxError('文档分类不存在');
            }
            $name = input('name');
            if(!$name){
                return $this->ajaxError('参数错误');
            }
            $status = input('status');
            if($status){
                $status = 1;
            }else{
                $status = 2;
            }
            $a = [
                'cid'=>$cid,
                'tid'=>$tid,
                'name'=>$name,
                'status'=>$status,
                'create_time'=>time(),
                'alter_time'=>0
            ];
            $res = SearchColumn::add($a);
            if($res){
                return $this->ajaxOk('添加成功');
            }else{
                return $this->ajaxError('添加失败');
            }
        }else{
            //获取文档顶级分类
            $article_column = Column::getAll(['parent_id'=>0],'id,type_name,channel_type');
            //获取搜索分类列表
            $search_column = SearchColumn::getAll([],'name,cid,tid,id');
            foreach($article_column as $key => $value){
                foreach($search_column as $k => $v){
                    if($v['cid'] == $value['id']){
                        unset($article_column[$key]);
                    }
                }
            }
            View::share('article_column',$article_column);
            View::share('search_column',$search_column);
            return view('addcolumn');
        }
    }

    /**
     * @return false|string
     * Description: 修改分类名称
     */
    public function editcolumnname(){
        $name = input('name');
        if(empty($name)){
            return $this->ajaxError('参数错误');
        }
        if(mb_strlen($name, 'UTF-8') > 10){
            return self::ajaxError('分类名称不能超过10个字符');
        }
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return $this->ajaxError('参数错误');
        }
        $res = SearchColumn::edit(['id'=>$id],['name'=>$name]);
        if($res){
            return $this->ajaxOk('修改成功');
        }else{
            return $this->ajaxError('修改失败');
        }
    }

    public static function add($article_info, $data)
    {
        // TODO: Implement add() method.
    }

    public static function edit($article_info, $data)
    {
        // TODO: Implement edit() method.
    }
}