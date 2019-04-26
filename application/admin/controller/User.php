<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/9
 * Time: 19:33
 */

namespace app\admin\controller;


use think\Request;

class User extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    //显示管理员列表
    public function show(){
        $this->assign('level' ,$this->getLevel());
        return View('user_show_list');
    }

    //获取管理员列表数据
    public function getUserList(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $username = input('user_name');
        $where = [];
        if(isset($username) && !empty($username)){
            $where['user_name'] = $username;
        }
        $uid = input('uid');
        if(isset($uid) && !empty($uid)){
            $where['id']=$uid;
        }
        $level = input('level');
        if(isset($level) && !empty($level)){
            $where['type']=$level;
        }
        $admin = new \app\admin\model\User();
        $user_list = $admin->getUserList($where ,' * ' ,$limit);
        $level_list = $this->getLevel();
        $level_arr = array_column($level_list ,'name' ,'id');
        $state_arr = [1=>'启用' ,2=>'禁用'];
        //处理列表信息
        foreach($user_list as $key => $value){
            if(!empty($value['create_time'])){
                $user_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
            }
            if(!empty($value['alter_time'])){
                $user_list[$key]['alter_time'] = date('Y-m-d H:i:s' ,$value['alter_time']);
            }
            $user_list[$key]['type'] = $level_arr[$value['type']];
            $user_list[$key]['state'] = $state_arr[$value['state']];
        }
        $user_count = $admin->getUserCount($where);
        $arr = [
            'data' => $user_list,
            'count' => $user_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //添加管理员账号方法
    public function add(){
        if(Request::instance()->isPost()){
            $user = new \app\admin\model\User();
            //验证数据
            $username = input('user_name');
            if(!isset($username)){
                echo '非法访问';die;
            }
            if(empty($username)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的用户名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($username ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的用户名不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $namerule = '/^[A-Za-z0-9]+$/';
            if(!preg_match($namerule,$username)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的用户名只能是字母和数字的组合'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }

            //验证账号是否重复
            $where = ['user_name'=>$username];
            if($user->getUserCount($where) !=0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'账号重复,请更换'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $password = input('password');
            if(!isset($password)){
                echo '非法访问';die;
            }
            if(empty($password)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的密码不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $verfypassword = input('verfypassword');
            if(!isset($verfypassword)){
                echo '非法访问';die;
            }
            if($password != $verfypassword){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'两次输入的密码不一致'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $nickname = input('nick_name');
            if(!isset($nickname)){
                echo '非法访问';die;
            }
            if(empty($nickname)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的昵称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($nickname ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的昵称不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $email = input('email');
            if(!isset($email)){
                echo '非法访问';die;
            }
            if(mb_strlen($email ,'UTF-8') >50){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的邮箱字符不能超过50个'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //验证邮箱格式
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的邮箱格式不正确'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $state = input('state');
            if(!isset($state)){
                $state = 2;
            }else{
                $state = 1;
            }
            $level = input('level');
            if(!isset($level)){
                echo '非法访问';die;
            }
            if($level == 0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'请选择账号等级'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $phone = input('phone');
            if(!isset($phone)){
                echo '非法访问';die;
            }else if(!empty($phone)){
                $phone_rule = '/^0?(13|14|15|17|18|16)[0-9]{9}$/';
                //验证手机号格式
                if(!preg_match($phone_rule,$phone)){
                    return '输入的手机号格式不正确';
                }
            }
            $realname = input('realname');
            if(!isset($realname)){
                echo '非法访问';die;
            }
            if(mb_strlen($realname ,'UTF-8') >15){
                $a =[
                    'errorcode'=>1,
                    'msg'=>'输入的真实姓名不能超过15个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据添加到数据库
            $arr = [
                'user_name'=>$username,
                'nick_name'=>$nickname,
                'real_name'=>$realname,
                'phone'=>$phone,
                'user_password'=>getAdminPassword($password),
                'email'=>$email,
                'type'=>$level,
                'state'=>$state,
                'create_time'=>time()
            ];

            if($user->addUserInfo($arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'添加成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'添加失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $this->assign('level' ,$this->getLevel());
            return View('user_add');
        }
    }

    //删除管理员方法
    public function del(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $admin = new \app\admin\model\User();
        $where = ['id'=>$id];
        if($admin->delUserInfo($where)){
            $a = [
                'errorcode'=>0,
                'msg'=>'删除成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'删除失败'
            ];

            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //编辑管理员信息方法
    public function alter(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $where = ['id'=>$id];
        $user = new \app\admin\model\User();
        if(Request::instance()->isPost()){
            //验证数据
            $username = input('user_name');
            if(!isset($username)){
                echo '非法访问';die;
            }
            if(empty($username)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的用户名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($username ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的用户名不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $namerule = '/^[A-Za-z0-9]+$/';
            if(!preg_match($namerule,$username)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的用户名只能是字母和数字的组合'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $nickname = input('nick_name');
            if(!isset($nickname)){
                echo '非法访问';die;
            }
            if(empty($nickname)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的昵称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($nickname ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的昵称不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $email = input('email');
            if(!isset($email)){
                echo '非法访问';die;
            }
            if(mb_strlen($email ,'UTF-8') >50){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的邮箱字符不能超过50个'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //验证邮箱格式
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的邮箱格式不正确'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $state = input('state');
            if(!isset($state)){
                $state = 2;
            }else{
                $state = 1;
            }
            $level = input('level');
            if(!isset($level)){
                echo '非法访问';die;
            }
            if($level == 0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'请选择账号等级'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $phone = input('phone');
            if(!isset($phone)){
                echo '非法访问';die;
            }else if(!empty($phone)){
                $phone_rule = '/^0?(13|14|15|17|18|16)[0-9]{9}$/';
                //验证手机号格式
                if(!preg_match($phone_rule,$phone)){
                    return '输入的手机号格式不正确';
                }
            }
            $realname = input('realname');
            if(!isset($realname)){
                echo '非法访问';die;
            }
            if(mb_strlen($realname ,'UTF-8') >15){
                $a =[
                    'errorcode'=>1,
                    'msg'=>'输入的真实姓名不能超过15个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据,更新数据库内容
            $arr = [
                'nick_name'=>$nickname,
                'real_name'=>$realname,
                'phone'=>$phone,
                'email'=>$email,
                'type'=>$level,
                'state'=>$state,
                'alter_time'=>time()
            ];
            $res = $user->editUserInfo($where ,$arr);
            if($res){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $admin_info = $user->getUserInfoOne($where);
            $this->assign('info' ,$admin_info);
            $this->assign('level' ,$this->getLevel());
            return View('user_alter');
        }
    }

    //修改权限
    public function alterPower(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $user = new \app\admin\model\User();
        $where = ['id'=>$id];
        if(Request::instance()->isPost()){
            //验证数据
            $level = input('level');
            if(!isset($level)){
                echo '非法访问';die;
            }
            if($level == 0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'请选择账号类型'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据更新数据库信息
            $arr = [
                'type'=>$level,
                'alter_time'=>time()
            ];
            if($user->editUserInfo($where ,$arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $admin_info = $user->getUserInfoOne($where);
            $this->assign('info' ,$admin_info);
            $this->assign('level' ,$this->getLevel());
            return View('user_alter_power');
        }
    }

    //修改账号状态
    public function alterState(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $state = input('state');
        if(!is_numeric($state)){
            echo '非法访问';die;
        }
        $user = new \app\admin\model\User();
        $where = ['id'=>$id];
        $arr = [
            'state'=>$state,
            'alter_time'=>time()
        ];
        if($user->editUserInfo($where ,$arr)){
            $a = [
                'errorcode'=>0,
                'msg'=>'修改成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'修改失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //修改账号密码
    public function alterPassword(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        if(Request::instance()->isPost()){
            $password = input('password');
            $verfy = input('verfy');
            if(!isset($password) || !isset($verfy)){
                echo '非法访问';die;
            }
            if($password != $verfy){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'两次输入的密码不一致'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $user = new \app\admin\model\User();
            $where = ['id'=>$id];
            $arr = [
                'user_password'=>getAdminPassword($password),
                'alter_time'=>time()
            ];
            if($user->editUserInfo($where ,$arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $this->assign('id' ,$id);
            return View('user_alter_password');
        }
    }

    //角色管理
    public function role()
    {
        return View('user_role');
    }

    //获取角色列表
    public function getRoleList(){
        $user = new \app\admin\model\User();
        $level_list = $user->getUserLevelList();
        $level_count = $user->getUserLevelCount();
        $arr = [
            'data' => $level_list,
            'count' => $level_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //新建角色
    public function addRole(){
        if(Request::instance()->isPost()){
            //验证数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';die;
            }
            if($name == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($name ,'UTF-8')>20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色名不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $level = input('level');
            if(!isset($level)){
                echo '非法访问';die;
            }
            if(!is_numeric($level)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色值只能是数字'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(!isset($description)){
                echo '非法访问';die;
            }
            if(mb_strlen($description ,'UTF-8')>40){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色描述不能超过40个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据添加到数据库
            $arr = [
                'name'=>$name,
                'level'=>$level,
                'description'=>$description,
                'create_time'=>time()
            ];
            $user = new \app\admin\model\User();
            if($user->addLevel($arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'添加成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'添加失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            return View('user_role_add');
        }
    }

    //删除角色信息
    public function delRole(){
        $id = input('id');

        if(!isset($id)){
            echo '非法访问';die;
        }
        $where = ['id'=>$id];
        $user = new \app\admin\model\User();
        if($user->delLevel($where)){
            $a = [
                'errorcode'=>0,
                'msg'=>'删除成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'删除失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //修改角色信息
    public function alterRole(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $where = ['id'=>$id];
        $user = new \app\admin\model\User();
        if(Request::instance()->isPost()){
            //验证数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';die;
            }
            if($name == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($name ,'UTF-8')>20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色名不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $level = input('level');
            if(!isset($level)){
                echo '非法访问';die;
            }
            if(!is_numeric($level)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色值只能是数字'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(!isset($description)){
                echo '非法访问';die;
            }
            if(mb_strlen($description ,'UTF-8')>40){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的角色描述不能超过40个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $arr = [
                'name'=>$name,
                'level'=>$level,
                'description'=>$description,
                'alter_time'=>time()
            ];
            if($user->alterLevelInfo($where ,$arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $info = $user->getLevelInfo($where);
            $this->assign('info' ,$info);
            return View('user_role_alter');
        }

    }
    //获取账号级别列表
    private function getLevel(){
        //分配管理员级别数据到页面
        $admin = new \app\admin\model\User();
        return $admin->getUserLevelList();
    }
}