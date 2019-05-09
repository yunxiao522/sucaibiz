<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/9
 * Time: 19:33
 */

namespace app\admin\controller;

use app\model\AdminLevel;
use app\model\AdminUser;
use app\validate\AdminUser as AdminUser_Validate;
use app\validate\AdminLevel as AdminLevel_Validate;
use think\Request;
use think\view;

class User extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \think\response\View
     * Description 显示管理员列表
     */
    public function show(){
        view::share('level' ,$this->getLevel());
        return View('user_show_list');
    }

    /**
     * @return false|string
     * Description 获取管理员列表数据
     */
    public function getUserList(){
        $username = input('user_name');
        $where = [];
        if(!empty($username)){
            $where['user_name'] = $username;
        }
        $uid = input('uid');
        if(!empty($uid)){
            $where['id']=$uid;
        }
        $level = input('level');
        if(!empty($level)){
            $where['type']=$level;
        }
        $Admin_List = AdminUser::getList($where ,' * ', 'id desc');
        //处理列表信息
        foreach($Admin_List['data'] as $key => $value){
            $Admin_List['data'][$key]['type'] = AdminLevel::getField(['id'=>$value['type']], 'name');
            $Admin_List['data'][$key]['state'] = AdminUser::$status[$value['state']];
        }
        return self::ajaxOkdata($Admin_List, 'get data success');
    }

    //添加管理员账号方法
    public function add(){
        if(Request::instance()->isPost()){
            $validate = new AdminUser_Validate();
            if(!$validate->scene('add')->check(input())){
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData('add', function($data, $input){
                if(!isset($input['state'])){
                    $data['state'] = 2;
                }else{
                    $data['state'] = 1;
                }
                $data['type'] = $data['level'];
                $data['real_name'] = $data['realname'];
                $data['user_password'] = getAdminPassword($data['password']);
                unset($data['level']);
                unset($data['verfypassword']);
                unset($data['realname']);
                unset($data['password']);
                return $data;
            });
            $data['create_time'] = time();
            $res = AdminUser::add($data);
            if($res){
                return self::ajaxOk('添加成功');
            }else{
                return self::ajaxError('添加失败');
            }
        }else{
            $this->assign('level' ,$this->getLevel());
            return View('user_add');
        }
    }

    /**
     * @return false|string
     * Description 删除管理员方法
     */
    public function del(){
        $id = input('id');
        if(!isset($id)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        $res = AdminUser::del($where);
        if($res){
            return self::ajaxOk('删除成功');
        }else{
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 编辑管理员信息方法
     */
    public function alter(){
        $id = input('id');
        if(!isset($id)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        if(Request::instance()->isPost()){
            $validate = new AdminUser_Validate();
            if(!$validate->scene('edit')->check(input())){
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData('edit', function($data, $input){
                if(!isset($input['state'])){
                    $data['state'] = 2;
                }else{
                    $data['state'] = 1;
                }
                $data['type'] = $data['level'];
                $data['real_name'] = $data['realname'];
                $data['alter_time'] = time();
                unset($data['level']);
                unset($data['realname']);
                return $data;
            });
            $res = AdminUser::edit($where, $data);
            if($res){
                return self::ajaxOk('修改成功');
            }else{
                return self::ajaxError('修改失败');
            }
        }else{
            $Admin_Info = AdminUser::getOne($where);
            view::share('info', $Admin_Info);
            view::share('level' ,$this->getLevel());
            return View('user_alter');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改权限
     */
    public function alterPower(){
        $id = input('id');
        if(empty($id)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        if(Request::instance()->isPost()){
            //验证数据
            $level = input('level');
            if(!isset($level)){
                return self::ajaxError('非法访问');
            }
            if($level == 0){
                return self::ajaxError('请选择账号类型');
            }
            //组合数据更新数据库信息
            $arr = [
                'type'=>$level,
                'alter_time'=>time()
            ];
            $res = AdminUser::edit($where, $arr);
            if($res){
                return self::ajaxOk('修改成功');
            }else{
                return self::ajaxError('修改失败');
            }
        }else{
            $Admin_Info = AdminUser::getOne($where);
            view::share('info' ,$Admin_Info);
            view::share('level' ,$this->getLevel());
            return View('user_alter_power');
        }
    }

    /**
     * @return false|string
     * Description 修改账号状态
     */
    public function alterState(){
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return self::ajaxError('非法访问');
        }
        $state = input('state');
        if(empty($state) || !is_numeric($state)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        $arr = [
            'state'=>$state,
            'alter_time'=>time()
        ];
        $res = AdminUser::edit($where, $arr);
        if($res){
            return self::ajaxOk('修改成功');
        }else{
            return self::ajaxError('修改失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改账号密码
     */
    public function alterPassword(){
        $id = input('id');
        if(empty($id)){
            return self::ajaxError('非法访问');
        }
        if(Request::instance()->isPost()){
            $validate = new AdminUser_Validate();
            if($validate->scene('alter_password')->check(input())){
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData('alter_password', function ($data, $input){
                $data['user_password'] = getAdminPassword($data['password']);
                unset($data['password']);
                unset($data['verfypassword']);
                $data['alter_time'] = time();
                return $data;
            });
            $where = ['id'=>$id];
            $res = AdminUser::edit($where, $data);
            if($res){
                return self::ajaxOk('修改成功');
            }else{
                return self::ajaxError('修改失败');
            }
        }else{
            view::share('id' ,$id);
            return View('user_alter_password');
        }
    }

    /**
     * @return false|string
     * Description 获取角色列表
     */
    public function getRoleList(){
        $Level_List = AdminLevel::getList([], '*', 'id desc');
        return self::ajaxOkdata($Level_List, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 新建角色
     */
    public function addRole(){
        if(Request::instance()->isPost()){
            $validate = new AdminLevel_Validate();
            if(!$validate->check(input())){
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData();
            $data['create_time'] = time();
            $res = AdminLevel::add($data);
            if($res){
                return self::ajaxOk('添加成功');
            }else{
                return self::ajaxError('添加失败');
            }
        }else{
            return View('user_role_add');
        }
    }

    /**
     * @return false|string
     * Description 删除角色信息
     */
    public function delRole(){
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        $res = AdminLevel::del($where);
        if($res){
            return self::ajaxOk('删除成功');
        }else{
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改角色信息
     */
    public function alterRole(){
        $id = input('id');
        if(empty($id) || !is_numeric($id)){
            return self::ajaxError('非法访问');
        }
        $where = ['id'=>$id];
        if(Request::instance()->isPost()){
            $validate = new AdminLevel_Validate();
            if(!$validate->check(input())){
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData();
            $data['alter_time'] = time();
            $res = AdminLevel::edit($where, $data);
            if($res){
                return self::ajaxOk('修改成功');
            }else{
                return self::ajaxError('修改失败');
            }
        }else{
            $Info = AdminLevel::getOne($where);
            view::share('info' ,$Info);
            return View('user_role_alter');
        }
    }

    //获取账号级别列表
    private function getLevel(){
        return AdminLevel::getAll([], '*');
    }
}