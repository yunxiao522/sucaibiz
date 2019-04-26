<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/18
 * Time: 10:31
 * Description: 会员管理
 */

namespace app\admin\controller;

use think\Request;


class Member extends Common
{
    private $user_status = [1 => '未激活', 2 => '已激活'];
    private $user_sms_status = [1=>'发送成功',2=>'发送失败',3=>'发送中'];
    private $user_email_status = [1=>'发送成功',2=>'发送失败',3=>'发送中',0=>'发送成功'];

    public function __construct()
    {
        parent::__construct();
    }

    //显示用户会员账号列表
    public function show()
    {
        return View();
    }

    //获取会员账号列表数据
    public function getMemberList()
    {
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $member = model('Member');
        $user_count = $member->getMemberCount($where);
        $user_list = $member->getMemberList($where, ' * ', $limit, 'id desc');
        //获取会员等级列表
        $user_level_list = $member->getMemberLevel('', 'id ,level_name');
        $level_list = array();
        foreach ($user_level_list as $key => $value) {
            $level_list[$value['id']]['level_name'] = $value['level_name'];
        }
        foreach ($user_list as $key => $value) {
            $user_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if (!empty($value['alter_time'])) {
                $user_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
            $user_list[$key]['type'] = $level_list[$value['type']]['level_name'];
            $user_list[$key]['status'] = $this->user_status[$value['status']];
        }
        $arr = [
            'data' => $user_list,
            'count' => $user_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //获取会员登录日志数据
    public function getMemberLoginLog()
    {
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        $member = model('Member');
        $where = ['uid' => $id ,'type'=>1];
        $login_log_list = $member->getMemberLoginLogList($where, ' * ', $limit);
        $login_log_count = $member->getMemberLoginLogCpunt($where);
        foreach ($login_log_list as $key => $value) {
            $login_log_list[$key]['login_time'] = date('Y-m-d H:i:s', $value['login_time']);
        }
        $arr = [
            'data' => $login_log_list,
            'count' => $login_log_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //显示会员账号登录日志页面
    public function showMemberLoginLog()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        $this->assign('uid', $id);
        return View('get_member_login_log');
    }

    //显示会员等级
    public function level()
    {

        return View();
    }

    //修改会员等级
    public function modMemberLevel()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        if (Request::instance()->isPost()) {
            $level = input('level');
            if (!isset($level) || !is_numeric($level)) {
                echo '非法访问';
                die;
            }
            $where = ['id' => $id];
            $d = [
                'type' => $level,
                'alter_time' => time()
            ];
            $member = model('Member');
            $res = $member->alterMember($where, $d);
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = '修改成功';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '修改失败';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            //获取会员等级列表
            $member = model('Member');
            $member_level = $member->getMemberLevel([], ' id ,level_name', 100, 'id asc');
            $this->assign('member_level', $member_level);
            $this->assign('uid', $id);
            return View('mod_member_level');
        }
    }

    //新增会员等级
    public function addMemebrLevel()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $level_name = input('level_name');
            if (!isset($level_name)) {
                echo '非法访问';
                die;
            }
            if (empty($level_name) || mb_strlen($level_name, 'UTF-8') > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = '等级名称不能为空，并且不能超过20个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $rank = input('rank');
            if (!isset($rank)) {
                echo '非法访问';
                die;
            }
            if (!is_numeric($rank)) {
                $a['errorcode'] = 1;
                $a['msg'] = '等级值不能为空，并且只能是数字';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if (!isset($description)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($description, 'UTF-8') > 100) {
                $a['errorcode'] = 1;
                $a['msg'] = '等级说明不能超过100个字符串';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $level_img = input('level_img');
            $member = model('Member');
            $b = [
                'level_name' => $level_name,
                'rank' => $rank,
                'description' => $description,
                'level_img' => $level_img,
                'create_time' => time()
            ];
            $res = $member->addMemberLevel($b);
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = '新建成功';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '新建失败';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            return View('add_member_level');
        }
    }

    //获取等级列表
    public function getMemberLevelList()
    {
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $member = model('Member');
        $level_count = $member->geMemberLevelCount($where);
        $level_list = $member->getMemberLevel($where, ' * ', $limit, 'id ASC');
        foreach ($level_list as $key => $value) {
            $level_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if (!empty($value['alter_time'])) {
                $level_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
        }
        $arr = [
            'data' => $level_list,
            'count' => $level_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //删除会员等级
    public function delMemberLevel()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        $where = ['id' => $id];
        $member = model('Member');
        $res = $member->delMemberLevel($where);
        if ($res) {
            $a['errorcode'] = 0;
            $a['msg'] = '删除成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '删除失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //编辑会员等级
    public function alterMemberLevel()
    {
        if (Request::instance()->isPost()) {
            $id = input('id');
            if (!isset($id)) {
                echo '非法访问';
                die;
            }
            $level_name = input('level_name');
            if (!isset($level_name)) {
                echo '非法访问';
                die;
            }
            if (empty($level_name) || mb_strlen($level_name, 'UTF-8') > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = '等级名称不能为空，并且不能超过20个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $rank = input('rank');
            if (!isset($rank)) {
                echo '非法访问';
                die;
            }
            if (!is_numeric($rank)) {
                $a['errorcode'] = 1;
                $a['msg'] = '等级值不能为空，并且只能是数字';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if (!isset($description)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($description, 'UTF-8') > 100) {
                $a['errorcode'] = 1;
                $a['msg'] = '等级说明不能超过100个字符串';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $member = model('Member');
            $where = ['id' => $id];
            $d = [
                'level_name' => $level_name,
                'rank' => $rank,
                'description' => $description,
                'alter_time' => time()
            ];
            $res = $member->alterMemberLevel($where, $d);
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = '修改成功';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '修改失败';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $id = input('id');
            if (!isset($id)) {
                echo '非法访问';
                die;
            }
            $where = ['id' => $id];
            $member = model('Member');
            $member_level_info = $member->getMemberLevelInfo($where);
            $this->assign('level_info', $member_level_info);
            return View('alter_member_level');
        }
    }

    //展示会员详情
    public function getMemberLevel()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        $where = ['id' => $id];
        $member = model('Member');
        $member_level_info = $member->getMemberLevelInfo($where);
        $this->assign('level_info', $member_level_info);
        return View('get_member_level');
    }

    //修改会员信息
    public function alterMember()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        if (Request::instance()->isPost()) {
            //验证数据
            $nickname = input('nickname');
            if (!isset($nickname)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($nickname, 'UTF-8') > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的会员昵称不能超过20个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $face = input('face');
            if (!isset($face)) {
                echo '非法访问';
                die;
            }
            $type = input('type');
            if (!isset($type) || !is_numeric($type)) {
                echo '非法访问';
                die;
            }
            $realname = input('realname');
            if (!isset($realname)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($realname, 'UTF-8') > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的真实用户名不能超过20个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $email = input('email');
            if (!isset($email)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($email, 'UTF-8') > 50) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的邮箱地址不能超过50个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $email_rule = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
            if (!empty($email) && !preg_match($email_rule, $email)) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的邮箱地址格式不正确';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $phone = input('phone');
            if (!isset($phone)) {
                echo '非法访问';
                die;
            }
            $phone_rule = "/^(13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/";
            if (!empty($phone) && !preg_match($phone_rule, $phone)) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的手机号格式不正确';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $status = input('status');
            if (isset($status)) {
                $status = 2;
            } else {
                $status = 1;
            }
            $qq = input('QQ');
            if (!isset($qq)) {
                echo '非法访问';
                die;
            }
            //更新数据库内容
            $where = ['id' => $id];
            $d = [
                'nickname' => $nickname,
                'face' => $face,
                'realname' => $realname,
                'type' => $type,
                'phone' => $phone,
                'email' => $email,
                'qq' => $qq,
                'alter_time' => time(),
                'status' => $status
            ];
            $password = input('password');
            $verifypassword = input('verifypassword');
            if (!isset($password) || !isset($verifypassword)) {
                echo '非法访问';
                die;
            }
            if ($password != '' && $password == $verifypassword) {
                $password = sha1($password);
                $d['password'] = getUserPwd($password);
            }
            $member = model('Member');
            $res = $member->alterMember($where, $d);
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = '修改成功';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '修改失败';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $where = ['id' => $id];
            $member = model('Member');
            $member_info = $member->getMemberInfo($where);
            $this->assign('member_info', $member_info);
            $member_level = $member->getMemberLevel([], ' * ', 100, 'id ASC');
            $this->assign('member_level', $member_level);
            return View('alter_member');
        }
    }


    //修改会员头像方法
    public function alterMemberFace()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        if ($_FILES['file']['error'] == 0) {
            $member_face_img_path = './upload/face/';
            if (!file_exists($member_face_img_path)) {
                mkdir($member_face_img_path, 0777, true);
            }
            //允许上传头像文件的mime type类型数组
            $img_type = [
                'image/gif',
                'image/jpeg',
                'image/png'
            ];
            if (!in_array(strtolower($_FILES['file']['type']), $img_type)) {
                $a['errorcode'] = 1;
                $a['msg'] = "文件类型不在允许的范围内";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $member_face_img_type = str_replace('image/', '', $_FILES['file']['type']);
            $member_face_img_newfile = $member_face_img_path . getNewFileName() . '.' . $member_face_img_type;
            $member_face_img_url = ltrim($member_face_img_newfile, '.');
            if (move_uploaded_file($_FILES['file']['tmp_name'], $member_face_img_newfile)) {
                $where = ['id' => $id];
                $b = ['face' => $member_face_img_url];
                $member = model('Member');
                $res = $member->alterMember($where, $b);
                if ($res) {
                    $a['errorcode'] = 0;
                    $a['msg'] = "上传成功";
                    $a['url'] = $member_face_img_url;
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = "上传失败";
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = "上传失败";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '会员头像上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //修改会员等级图标方法
    public function alterMemberLevelImg()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        if ($_FILES['file']['error'] == 0) {
            $member_level_img_path = './upload/ico/';
            if (!file_exists($member_level_img_path)) {
                mkdir($member_level_img_path, 0777, true);
            }
            //允许上传头像文件的mime type类型数组
            $img_type = [
                'image/gif',
                'image/jpeg',
                'image/png'
            ];
            if (!in_array(strtolower($_FILES['file']['type']), $img_type)) {
                $a['errorcode'] = 1;
                $a['msg'] = "文件类型不在允许的范围内";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $member_level_img_type = str_replace('image/', '', $_FILES['file']['type']);
            $member_level_img_newfile = $member_level_img_path . getNewFileName() . '.' . $member_level_img_type;
            $member_level_img_url = ltrim($member_level_img_newfile, '.');
            if (move_uploaded_file($_FILES['file']['tmp_name'], $member_level_img_newfile)) {
                $where = ['id' => $id];
                $b = ['level_img' => $member_level_img_url, 'alter_time' => time()];
                $member = model('Member');
                $res = $member->alterMemberLevel($where, $b);
                if ($res) {
                    $a['errorcode'] = 0;
                    $a['msg'] = "上传成功";
                    $a['url'] = $member_level_img_url;
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                } else {
                    $a['errorcode'] = 1;
                    $a['msg'] = "上传失败";
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = "上传失败";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '会员头像上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //上传会员等级图标方法
    public function updateMemberLevelImg()
    {
        if ($_FILES['file']['error'] == 0) {
            $member_level_img_path = './upload/ico/';
            if (!file_exists($member_level_img_path)) {
                mkdir($member_level_img_path, 0777, true);
            }
            //允许上传头像文件的mime type类型数组
            $img_type = [
                'image/gif',
                'image/jpeg',
                'image/png'
            ];
            if (!in_array(strtolower($_FILES['file']['type']), $img_type)) {
                $a['errorcode'] = 1;
                $a['msg'] = "文件类型不在允许的范围内";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $member_level_img_type = str_replace('image/', '', $_FILES['file']['type']);
            $member_level_img_newfile = $member_level_img_path . getNewFileName() . '.' . $member_level_img_type;
            $member_level_img_url = ltrim($member_level_img_newfile, '.');
            if (move_uploaded_file($_FILES['file']['tmp_name'], $member_level_img_newfile)) {
                $a['errorcode'] = 0;
                $a['msg'] = "上传成功";
                $a['url'] = $member_level_img_url;
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = "上传失败";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '会员头像上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //删除会员方法
    public function delMember()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        $member = model('Member');
        $where = ['id' => $id];
        $res = $member->delMember($where);
        if ($res) {
            $a['errorcode'] = 0;
            $a['msg'] = '删除成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '删除失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //查看会员详细信息
    public function showMemberInfo()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        //获取会员信息
        $member = model('Member');
        $where = ['id' => $id];
        $member_info = $member->getMemberInfo($where);
        if ($member_info['status'] == 1) {
            $member_info['status'] = '禁用';
        } else {
            $member_info['status'] = '启用';
        }
        $member_info['create_time'] = date('Y-m-d H:i:s', $member_info['create_time']);
        $this->assign('member_info', $member_info);
        return View('show_member_info');
    }

    //显示会员积分等级列表
    public function showIntegralList(){
        return View('integral_show_list');
    }

    //获取会员积分等级列表数据
    public function getIntegralList(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $integral = model('Integral');
        $level_count = $integral->getIntegralCount($where);
        $level_list = $integral->getIntegralList($where, ' * ', $limit, 'id ASC');
        //获取会员等级列表
        foreach ($level_list as $key => $value) {
            $level_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if (!empty($value['alter_time'])) {
                $level_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
            $level_list[$key]['scope'] = $value['min_integral'] .' - ' .$value['max_integral'];
        }
        $arr = [
            'data' => $level_list,
            'count' => $level_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //新增会员积分等级
    public function addIntegral(){
        if(Request::instance()->isPost()){
            //验证数据
            $level_name = input('level_name');
            if(!isset($level_name)){
                echo '非法访问';
                die;
            }
            if($level_name == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分等级名称不能为空哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($level_name ,'UTF-8') >20){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分等级名称不能超过20个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $min_integral = input('min_integral');
            if(!isset($min_integral) || !is_numeric($min_integral)){
                echo '非法访问';
                die;
            }
            if($min_integral >1000000 || $min_integral <0){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分值只能在0-1000000之间哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $max_integral = input('max_integral');
            if(!isset($max_integral) || !is_numeric($max_integral)){
                echo '非法访问';
                die;
            }
            if($max_integral >1000000 || $max_integral <0){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分值只能在0-1000000之间哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $star_num = input('star_num');
            if(!isset($star_num) || !is_numeric($star_num)){
                echo '非法访问';
                die;
            }
            if(empty($star_num)){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的星星数量不能为空哦';
            }
            if($star_num<0 || $star_num>100){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的星星数量只能在0-100之间';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(!isset($description)){
                echo '非法访问';
                die;
            }
            if(mb_strlen($description ,'UTF-8') >100){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分等级说明不能超过100个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $b = [
                'level_name' => $level_name,
                'min_integral' => $min_integral,
                'max_integral' => $max_integral,
                'star_num' => $star_num,
                'create_time' => time(),
                'description' => $description
            ];
            $integral = model('Integral');
            $result = $integral->addIntegralInfo($b);
            if($result){
                $a['errorcode'] = 0;
                $a['msg'] = '新增积分等级成功';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a['errorcode'] = 1;
                $a['msg'] = '新增积分等级失败';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            return View('Integral_add_info');
        }
    }

    //编辑会员积分等级
    public function alterIntegralInfo(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        if(Request::instance()->isPost()){
            //验证数据
            $level_name = input('level_name');
            if(!isset($level_name)){
                echo '非法访问';
                die;
            }
            if($level_name == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分等级名称不能为空哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($level_name ,'UTF-8') >20){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分等级名称不能超过20个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $min_integral = input('min_integral');
            if(!isset($min_integral) || !is_numeric($min_integral)){
                echo '非法访问';
                die;
            }
            if($min_integral >1000000 || $min_integral <0){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分值只能在0-1000000之间哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $max_integral = input('max_integral');
            if(!isset($max_integral) || !is_numeric($max_integral)){
                echo '非法访问';
                die;
            }
            if($max_integral >1000000 || $max_integral <0){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分值只能在0-1000000之间哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $star_num = input('star_num');
            if(!isset($star_num) || !is_numeric($star_num)){
                echo '非法访问';
                die;
            }
            if(empty($star_num)){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的星星数量不能为空哦';
            }
            if($star_num<0 || $star_num>100){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的星星数量只能在0-100之间';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(!isset($description)){
                echo '非法访问';
                die;
            }
            if(mb_strlen($description ,'UTF-8') >100){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的积分等级说明不能超过100个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $b = [
                'level_name' => $level_name,
                'min_integral' => $min_integral,
                'max_integral' => $max_integral,
                'star_num' => $star_num,
                'alter_time' => time(),
                'description' => $description
            ];
            $integral = model('Integral');
            $result = $integral->alterIntegralInfo($where ,$b);
            if($result){
                $a['errorcode'] = 0;
                $a['msg'] = '修改积分等级成功';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a['errorcode'] = 1;
                $a['msg'] = '修改积分等级失败';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{

            $integral = model('Integral');
            $integral_info = $integral->getIntegralInfo($where);
            $this->assign('integral_info' ,$integral_info);
            $this->assign('id' ,$id);
            return View('Integral_alter_info');
        }
    }

    //删除会员积分等级
    public function delIntegralInfo(){
        $id = input('id');
        if(!isset($id) || !is_numeric($id)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        $integral = model('Integral');
        $result = $integral->delIntegral($where);
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

    //会员邮件管理
    public function showEmail()
    {
        return View('Email_show_list');
    }

    //获取会员邮件列表数据
    public function getMemberEmailList(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $email = model('Email');
        //设置会员表和会员邮件表表名
        $email_table_alias = 'e';
        $user_table_alias = 'u';
        $email->table_alias = $email_table_alias;
        $email->user_table_alias = $user_table_alias;
        //设置查询的表字段
        $field = " $email_table_alias.id,$email_table_alias.status,$email_table_alias.address,$email_table_alias.title,$email_table_alias.create_time,$user_table_alias.nickname";
        $email_list = $email->getEmailList($where, $field, $limit, 'id desc');
        $email_count = $email->getEmailCount($where);
        //获取会员等级列表
        foreach ($email_list as $key => $value) {
            $email_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $email_list[$key]['status'] = $this->user_email_status[$value['status']];
        }
        $arr = [
            'data' => $email_list,
            'count' => $email_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //删除会员邮件方法
    public function delMemberEmailInfo(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        $email = model('Email');
        $result = $email->delEmailInfo($where);
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

    //显示会员邮件详细内容
    public function showEmailInfo(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';
            die;
        }
        $email = model('Email');
        //设置会员表和会员邮件表表名
        $email_table_alias = 'e';
        $user_table_alias = 'u';
        $email->table_alias = $email_table_alias;
        $email->user_table_alias = $user_table_alias;
        $where = [ "$email_table_alias.id" => $id];
        $email_info = $email->getEmailInfo($where);
        $email_info['create_time'] = date('Y-m-d H:i:s' ,$email_info['create_time']);
        $email_info['status'] = $this->user_email_status[$email_info['status']];
        $this->assign('email_info' ,$email_info);
        return View('Email_show_info');
    }

    //显示会员短信列表
    public function showMemberSmsList(){
        return View('Sms_show_list');
    }

    //获取会员短信列表数据
    public function getMemberSmsList(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $Sms = model('Sms');
        //设置会员表和会员邮件表表名
        $Sms_table_alias = 's';
        $user_table_alias = 'u';
        $Sms->table_alias = $Sms_table_alias;
        $Sms->user_table_alias = $user_table_alias;
        //设置查询的表字段
        $field = " $Sms_table_alias.*,$user_table_alias.nickname ";
        $Sms_list = $Sms->getSmsList($where, $field, $limit, 'id desc');
        $Sms_count = $Sms->getSmsCount($where);
        //获取会员等级列表
        foreach ($Sms_list as $key => $value) {
            $Sms_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $Sms_list[$key]['status'] = $this->user_sms_status[$value['status']];
        }
        $arr = [
            'data' => $Sms_list,
            'count' => $Sms_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //显示会员短信详细信息
    public function getMemberSmsInfo(){
        return View('Sms_show_info');
    }
}
