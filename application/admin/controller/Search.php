<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/10 0010
 * Time: 1:57
 */

namespace app\admin\controller;


use think\Db;
use think\Request;
use think\View;

class Search extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    public function column(){
        return view('column');
    }

    //获取分类数据
    public function getColumn(){
        $list = Model('searchcolumn')->getList([],'*',' id desc ');
        foreach ($list['data'] as $key => $value){
            $list['data'][$key]['column'] = Model('column')->getField(['id'=>$value['cid']],'type_name');
            $list['data'][$key]['type'] = Model('channeltype')->getField(['id'=>$value['tid']],'typename');
            $list['data'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            if(!empty($value['alter_time'])){
                $list['data'][$key]['alter_time'] = date('Y-m-d H:i:s',$value['alter_time']);
            }
        }
        return $this->ajaxOkdata($list);
    }

    public function keyword(){
        return view('keyword');
    }

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
        $list = Model('search')->getList($where,'*',' id desc ');
        foreach ($list['data'] as $key => $value){
            $list['data'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            if(!empty($value['alter_time'])){
                $list['data'][$key]['alter_time'] = date('Y-m-d H:i:s',$value['alter_time']);
            }
        }
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
        $res = Model('search')->del(['id'=>$id]);
        if(!$res){
            Db::rollback();
            return $this->ajaxError('删除失败');
        }
        $res = Model('searchhistory')->del(['sid'=>$id]);
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
        if(!$id){
            return $this->ajaxError('参数错误');
        }
        $status = input('status');
        if(!$status){
            return $this->ajaxError('参数错误');
        }
        $res = Model('searchcolumn')->edit(['id'=>$id],['status'=>$status,'alter_time'=>time()]);
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
        $res = Model('searchcolumn')->del(['cid'=>$id]);
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
            $tid = Model('column')->getField(['id'=>$cid],'channel_type');
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
            $res = Model('searchcolumn')->add($a);
            if($res){
                return $this->ajaxOk('添加成功');
            }else{
                return $this->ajaxError('添加失败');
            }
        }else{
            //获取文档顶级分类
            $article_column = Model('column')->getAll(['parent_id'=>0],'id,type_name,channel_type');
            //获取搜索分类列表
            $search_column = Model('searchcolumn')->getAll([],'name,cid,tid,id');
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
        if(!$name){
            return $this->ajaxError('参数错误');
        }
        $id = input('id');
        if(!$id){
            return $this->ajaxError('参数错误');
        }
        $res = Model('searchcolumn')->edit(['id'=>$id],['name'=>$name]);
        if($res){
            return $this->ajaxOk('修改成功');
        }else{
            return $this->ajaxError('修改失败');
        }

    }
}