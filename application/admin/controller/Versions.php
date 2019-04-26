<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/14 0014
 * Time: 14:45
 * Description: 版本管理
 */

namespace app\admin\controller;


use think\Request;
use think\View;

class Versions extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \think\response\View
     * Description 版本管理
     */
    public function manage(){
        return View('');
    }

    /**
     * @return false|string
     * Description 获取版本列表数据
     */
    public function getVersions(){
        $list = Model('versions')->getList([],'*');
        foreach($list['data'] as $key => $value){
            $list['data'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        }
        return $this->ajaxOkdata($list);
    }

    /**
     * @return \think\response\View
     * Description 版本详情
     */
    public function show(){
        return View('');
    }

    /**
     * @return \think\response\View
     * Description 修改版本信息
     */
    public function edit(){
        $id = input('id');
        if(!$id){
            return $this->ajaxError('非法访问');
        }
        if(Request::instance()->isPost()){
            $data = $this->checkForm();
            $where = [
                'id'=>$id
            ];
            $res = Model('versions')->edit($where,$data);
            if($res){
                return $this->ajaxOk('修改成功');
            }else{
                return $this->ajaxError('修改失败');
            }
        }else{
            $version_info  = Model('versions')->getOne(['id'=>$id],'*');
            View::share('version_info',$version_info);
            return View('');
        }
    }

    /**
     * @return false|string
     * Description 添加版本信息
     */
    public function add(){
        $data = $this->checkForm();
        $res = Model('versions')->add($data);
        if($res){
            return $this->ajaxOk('添加成功');
        }else{
            return $this->ajaxError('添加失败');
        }
    }

    /**
     * @return false|string
     * Description 验证表单数据
     */
    private function checkForm(){
        $data['number'] = input('number');
        if(!$data['number']){
            return $this->ajaxError('参数错误');
        }
        if(mb_strlen($data['number'],'UTF-8') > 10){
            return $this->ajaxError('输入的版本号不能超过10个字符');
        }
        $data['pubdate'] = input('pubdate');
        if(!$data['pubdate']){
            return $this->ajaxError('参数错误');
        }
        $data['pubdate'] = date('Y-m-d',strtotime($data['pubdate']));
        $data['content'] = input('content');
        if(empty($data['content'])){
            return $this->ajaxError('输入的版本内容不能为空');
        }
        $data['title'] = input('title');
        if(empty($data['title'])){
            return $this->ajaxError('输入的版本描述不能为空');
        }
        if(mb_strlen($data['title'],'UTF-8') > 20){
            return $this->ajaxError('输入的版本描述不能超过20个字符');
        }
        $is_height = input('is_height');
        if($is_height){
            $data['is_height'] = 2;
        }else{
            $data['is_height'] = 1;
        }
        $data['create_time'] = time();
        return $data;
    }

    /**
     * @return false|string
     * Description 删除版本信息
     */
    public function del(){
        $id = input('id');
        if(!$id){
            return $this->ajaxError('参数错误');
        }
        $res = Model('versions')->del(['id'=>$id]);
        if($res){
            return $this->ajaxOk('删除成功');
        }else{
            return $this->ajaxError('删除失败');
        }
    }
}