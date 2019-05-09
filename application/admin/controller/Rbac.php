<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/6
 * Time: 18:28
 * Description: 权限管理模块
 */


namespace app\admin\controller;

use app\model\AdminUser;
use app\model\RbacModel;
use app\model\AdminLevel;
use think\Request;
use think\view;

class Rbac extends Common
{
    //构造函数
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * Description 获取模块列表数据
     */
    public function getModelListJson(){
        $Model_List = RbacModel::getList([] ,' * ' , 'id asc');
        //处理列表数据
        foreach($Model_List['data'] as $key => $value){
            $Model_List['data'][$key]['type'] = RbacModel::$model_type[$value['type']];
        }
        return self::ajaxOkdata($Model_List, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 新建模块
     */
    public function addModel(){
        if(Request::instance()->isPost()){
            //验证数据
            $data = $this->checkModelInfoForm();
            $state = input('state');
            if(isset($state)){
                $data['state'] = 1;
            }else{
                $data['state'] = 2;
            }
            $data['create_time'] = time();
            $res = RbacModel::add($data);
            if($res){
                return self::ajaxOk('添加成功');
            }else{
                return self::ajaxError('添加失败');
            }
        }else{
            $Model_List = RbacModel::getAll([], 'id,name', 1000);
            view::share('list' ,$Model_List);
            return View('rbac_add_model');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改模块信息
     */
    public function alterMode(){
        $id = input('id');
        if(!isset($id)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        if(Request::instance()->isPost()){
            //验证数据
            $data = $this->checkModelInfoForm();
            if(is_string($data)){
                return self::ajaxError($data);
            }
            $state = input('state');
            if(!empty($state)){
                $data['state'] = 1;
            }else{
                $data['state'] = 2;
            }
            $data['alter_time'] = time();
            $res = RbacModel::edit($where, $data);
            if($res){
                return self::ajaxOk('修改成功');
            }else{
                return self::ajaxError('修改失败');
            }
        }else{
            $Model_Info = RbacModel::getOne($where ,' * ');
            view::share('info', $Model_Info);
            $Model_List = RbacModel::getAll([], 'id,name', 1000);
            view::share('list', $Model_List);
            return View('rbac_alter_model');
        }
    }

    /**
     * @return array|string|true
     * Description 验证获取模块信息表单数据
     */
    protected function checkModelInfoForm(){
        $rule = [
            'name'=>'require|max:15',
            'parent_id'=>'require|number',
            'ico'=>'max:50',
            'app'=>'require|max:25',
            'controller'=>'max:25',
            'method'=>'max:25',
            'url'=>'max:100',
            'description'=>'require|max:100',
            'type'=>'require|number'
        ];
        $msg = [
            'name.require'=>'请输入模块名称',
            'name.max'=>'模块名称不能超过15个字符',
            'parent_id.require'=>'请选择父级模块',
            'parent_id.number'=>'父级模块id只能是数字',
            'ico.max'=>'模块图标地址不能超过50个字符',
            'app.require'=>'请输入应用',
            'app.max'=>'应用名称不能超过25个字符',
            'controller.max'=>'控制器名称不能超过25个字符',
            'method.max'=>'方法名称不能超过25个字符',
            'url.max'=>'跳转链接不能超过100个字符',
            'description.require'=>'请输入模块说明',
            'description.max'=>'模块说明不能超过100个字符',
            'type.require'=>'请选择模块类型',
            'type.number'=>'模块类型值只能是数字'
        ];
        return $this->checkForm($rule, $msg, function($input){
            $data = [
                'name'=>$input['name'],
                'parent_id'=>$input['parent_id'],
                'ico'=>$input['ico'],
                'app'=>$input['app'],
                'controller'=>$input['controller'],
                'method'=>$input['method'],
                'url'=>$input['url'],
                'description'=>$input['description'],
                'type'=>$input['type']
            ];
            return $data;
        });
    }

    /**
     * @return false|string
     * Description 删除模块
     */
    public function delModel(){
        $id = input('id');
        if(!isset($id)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        $res = RbacModel::del($where);
        if($res){
            return self::ajaxOk('删除成功');
        }else{
            return self::ajaxError('删除失败');
        }
    }

    //权限管理页面
    public function access(){
        if(Request::instance()->isPost()){

        }else{
            //获取角色列表
            $Level_List = AdminLevel::getAll([], 'id,name');
            view::share('level' ,$Level_List);
            $User_List = AdminUser::getAll([], 'id,nick_name');
            view::share('user' ,$User_List);
            return View('rbac_show');
        }
    }

    //显示权限页面
    public function showAccess(){
        //判断查询条件
        $level = input('level');
        if(isset($level)){
            $where['level'] = $level;
        }
        $id = input('id');
        if(isset($id)){
            $where['id'] = $id;
        }
        //获取模块列表数据
        $Model_List = $this->getModelList();
        view::share('list', $Model_List);
        return View('rbac_show_access');
    }

    //获取模块列表
    private function getModelList($where = [] ,$field = ' * ' ,$limit = 1000){
        $Model_List = RbacModel::getAll($where, $field, $limit);
        return getarticletype($Model_List ,0 ,0);
    }
}