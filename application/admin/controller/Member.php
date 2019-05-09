<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/18
 * Time: 10:31
 * Description: 会员管理
 */

namespace app\admin\controller;

use app\model\LogLogin;
use app\model\UserEmail;
use app\model\UserSms;
use app\model\User;
use app\model\UserLevel;
use app\model\UserIntegralLevel;
use SucaiZ\File;
use think\Request;
use think\Session;
use think\Validate;

class Member extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员账号列表数据
     */
    public function getMemberList()
    {
        $user_list = User::getList([], ' id,username,level,nickname,type,create_time,status,email,phone,experience,gold,qq ', ' id desc ');
        //获取会员等级列表
        $user_level_list = UserLevel::getAll('', 'id,level_name');
        $level_list = array();
        foreach ($user_level_list as $key => $value) {
            $level_list[$value['id']]['level_name'] = $value['level_name'];
        }
        foreach ($user_list['data'] as $key => $value) {
            $user_list['data'][$key]['type'] = $level_list[$value['type']]['level_name'];
            $user_list['data'][$key]['status'] = User::$user_status[$value['status']];
        }
        return self::ajaxOkdata($user_list, 'get data success');
    }

    /**
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员登录日志数据
     */
    public function getMemberLoginLog()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['uid' => $id, 'type' => 1];
        $list = LogLogin::getList($where, ' * ');
        return self::ajaxOkdata($list, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 显示会员账号登录日志页面
     */
    public function showMemberLoginLog()
    {
        $id = input('id');
        if (!isset($id)) {
            return self::ajaxError('非法访问');
        }
        $this->assign('uid', $id);
        return View('get_member_login_log');
    }

    /**
     * @return false|string|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 修改会员等级
     */
    public function modMemberLevel()
    {
        $id = input('id');
        if (!isset($id)) {
            return self::ajaxError('非法访问');
        }
        if (Request::instance()->isPost()) {
            $level = input('level');
            if (!isset($level) || !is_numeric($level)) {
                return self::ajaxError('非法访问');
            }
            $where = ['id' => $id];
            $d = [
                'type' => $level,
                'alter_time' => time()
            ];
            $res = User::edit($where, $d);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            //获取会员等级列表
            $member_level = UserLevel::getAll([], ' id ,level_name', 100, 'id asc');
            $this->assign('member_level', $member_level);
            $this->assign('uid', $id);
            return View('mod_member_level');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 新增会员等级
     */
    public function addMemebrLevel()
    {
        if (Request::instance()->isPost()) {
            $data = $this->checkLevelForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['create_time'] = time();
            $res = UserLevel::add($data);
            if ($res) {
                return self::ajaxOk('创建成功');
            } else {
                return self::ajaxError('创建失败');
            }
        } else {
            return View('add_member_level');
        }
    }

    /**
     * @return array|string
     * Description 验证会员等级表单数据
     */
    protected function checkLevelForm()
    {
        $data = [];
        $data['level_name'] = input('level_name');
        if (!Validate::is($data['level_name'], 'require')) {
            return '请输入等级名称';
        }
        if (!Validate::max($data['level_name'], 20)) {
            return '等级名称不能超过20个字符';
        }
        $data['rank'] = input('rank');
        if (!Validate::is($data['rank'], 'require')) {
            return '请输入等级';
        }
        if (!Validate::is($data['rank'], 'number')) {
            return '等级必须为数字';
        }
        $data['description'] = input('description');
        if (!Validate::is($data['description'], 'require')) {
            return '请输入等级描述';
        }
        if (!Validate::max($data['description'], 100)) {
            return '输入的描述不能超过100个字符';
        }
        $data['level_img'] = input('level_img');
        return $data;
    }

    /**
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员等级列表
     */
    public function getMemberLevelList()
    {
        $list = UserLevel::getList([], ' id,level_name,level_img,ranks,create_time,alter_time ', "ranks asc");
        return self::ajaxOkdata($list, 'get data success');
    }

    /**
     * @return false|string
     * Description 删除会员等级
     */
    public function delMemberLevel()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('参数错误');
        }
        $where = ['id' => $id];
        $res = UserLevel::del($where);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 编辑会员等级
     */
    public function alterMemberLevel()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('参数错误');
        }
        if (Request::instance()->isPost()) {
            $data = $this->checkLevelForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $where = ['id' => $id];
            $data['alter_time'] = time();
            $res = UserLevel::edit($where, $data);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $where = ['id' => $id];
            $member_level_info = UserLevel::getOne($where, '*');
            $this->assign('level_info', $member_level_info);
            return View('alter_member_level');
        }
    }

    /**
     * @return false|string|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员等级详细信息
     */
    public function getMemberLevel()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $member_level_info = UserLevel::getOne($where);
        $this->assign('level_info', $member_level_info);
        return View('get_member_level');
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改会员信息
     */
    public function alterMember()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        if (Request::instance()->isPost()) {
            $data = $this->checkUserInfoForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['alter_time'] = time();
            //更新数据库内容
            $where = ['id' => $id];
            $res = User::edit($where, $data);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $where = ['id' => $id];
            $member_info = User::getOne($where);
            $this->assign('member_info', $member_info);
            $member_level = UserLevel::getAll([], ' * ', 100, 'id ASC');
            $this->assign('member_level', $member_level);
            return View('alter_member');
        }
    }

    /**
     * @return array|string
     * Description 验证会员信息表单数据
     */
    public function checkUserInfoForm()
    {
        $data = [];
        $data['nickname'] = input('nickname');
        if (!Validate::is($data['nickname'], 'require')) {
            return '请输入昵称';
        }
        if (!Validate::max($data['nickname'], 20)) {
            return '输入的昵称不能超过20个字符';
        }
        $face = input('face');
        if (!empty($face)) {
            if (!Validate::max($face, 150)) {
                return '头像地址不能超过150个字符';
            }
            $data['face'] = $face;
        }
        $data['type'] = input('type');
        if (!Validate::is($data['type'], 'require')) {
            return '请选择用户类型';
        }
        $level_list = array_column($this->getUserLevelList(), 'id');
        if (!Validate::in($data['type'], $level_list)) {
            return '输入的用户类型不正确';
        }
        $data['realname'] = input('realname');
        if (!Validate::max($data['realname'], 20)) {
            return '输入的真实用户名不能超过20个字符';
        }
        $data['email'] = input('email');
        if (!Validate::max($data['email'], 50)) {
            return '输入的邮箱地址不能超过50个字符';
        }
        if (!Validate::is($data['email'], 'is_email')) {
            return '输入的邮箱地址格式不正确';
        }
        $data['phone'] = input('phone');
        if (!Validate::is($data['phone'], 'is_phone')) {
            return '输入的手机格式不正确';
        }
        $status = input('status');
        $data['status'] = empty($status) ? 1 : 2;
        $data['qq'] = input('QQ');
        if (!Validate::is($data['qq'], 'number')) {
            return '输入的qq号码格式不正确';
        }
        if (!Validate::max($data['qq'], 12)) {
            return '输入的qq号码不能超过12位';
        }
        $password = input('password');
        $verifypassword = input('verifypassword');
        if (!empty($password)) {
            if (!Validate::eq($password, $verifypassword)) {
                return '两次输入的密码不相等';
            }
            $data['password'] = getUserPwd($password);
        }
        return $data;
    }

    /**
     * @return false|string
     * Description 修改会员头像方法
     */
    public function alterMemberFace()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        if (empty($_FILES['file'])) {
            return self::ajaxError('上传失败');
        }
        //允许上传头像文件的mime type类型数组
        $img_type = [
            'image/gif',
            'image/jpeg',
            'image/png'
        ];
        if (!in_array(strtolower($_FILES['file']['type']), $img_type)) {
            return self::ajaxError('上传的文件类型不在允许的范围内');
        }
        //设置附件相关信息
        File::setArticleInfo(-1, "it's user face img");
        File::setUserInfo(2, Session::get('admin')['id']);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $res = User::edit(['id' => $id], ['face' => File::$url, 'alter_time' => time()]);
            if ($res) {
                return self::ajaxOkdata(['url' => File::$url], '上传成功');
            }
        }
        return self::ajaxError('上传失败');
    }

    /**
     * @return false|string
     * Description 修改会员等级图标方法
     */
    public function alterMemberLevelImg()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        //检查文件类型
        $img_type = [
            'image/gif',
            'image/jpeg',
            'image/png'
        ];
        if (!in_array(strtolower($_FILES['file']['type']), $img_type)) {
            return self::ajaxError('文件类型壁不在运行范围内');
        }
        //设置附件相关信息
        File::setArticleInfo(-1, "it's user level ico");
        File::setUserInfo(2, Session::get('admin')['id']);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $res = UserLevel::edit(['id' => $id], ['level_img' => File::$url, 'alter_time' => time()]);
            if ($res) {
                return self::ajaxOkdata(['url' => File::$url], '上传成功');
            }
        }
        return self::ajaxError('上传失败');
    }

    /**
     * @return false|string
     * Description 上传会员等级图标方法
     */
    public function updateMemberLevelImg()
    {
        //检查文件类型
        $img_type = [
            'image/gif',
            'image/jpeg',
            'image/png'
        ];
        if (!in_array(strtolower($_FILES['file']['type']), $img_type)) {
            return self::ajaxError('文件类型壁不在运行范围内');
        }
        //设置附件相关信息
        File::setArticleInfo(-1, "it's user level ico");
        File::setUserInfo(2, Session::get('admin')['id']);
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $a['url'] = File::$url;
            return self::ajaxOkdata(['url' => File::$url], '上传成功');
        }
        return self::ajaxError('上传失败');
    }

    /**
     * @return false|string
     * Description 删除会员方法
     */
    public function delMember()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('参数错误');
        }
        $where = ['id' => $id];
        $res = User::del($where);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 查看会员详细信息
     */
    public function showMemberInfo()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        //获取会员信息
        $where = ['id' => $id];
        $member_info = User::getOne($where);
        if ($member_info['status'] == 1) {
            $member_info['status'] = '禁用';
        } else {
            $member_info['status'] = '启用';
        }
        $this->assign('member_info', $member_info);
        return View('show_member_info');
    }

    /**
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员积分等级列表数据
     */
    public function getIntegralList()
    {
        $level_list = UserIntegralLevel::getList([], ' id,level_name,star_num,min_integral,max_integral ', 'id ASC');
        //获取会员等级列表
        foreach ($level_list['data'] as $key => $value) {
            $level_list['data'][$key]['scope'] = $value['min_integral'] . ' - ' . $value['max_integral'];
        }
        return $this->ajaxOkdata($level_list, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 新增会员积分等级
     */
    public function addIntegral()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $data = $this->checkUserIntegralForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['create_time'] = time();
            $result = UserIntegralLevel::add($data);
            if ($result) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            return View('Integral_add_info');
        }
    }

    /**
     * @return false|string|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 编辑会员积分等级
     */
    public function alterIntegralInfo()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            $data = $this->checkUserIntegralForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['alter_time'] = time();
            $result = UserIntegralLevel::edit($where, $data);
            if ($result) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $integral_info = UserIntegralLevel::getOne($where);
            $this->assign('integral_info', $integral_info);
            $this->assign('id', $id);
            return View('Integral_alter_info');
        }
    }

    /**
     * @return array|string
     * Description 会员积分等级表单数据验证
     */
    protected function checkUserIntegralForm()
    {
        $data = [];
        $data['level_name'] = input('level_name');
        if (!Validate::is($data['level_name'], 'require')) {
            return '请输入积分等级名称';
        }
        if (!Validate::max($data['level_name'], 20)) {
            return '输入的积分等级名称不能超过20个字符';
        }
        $data['min_integral'] = input('min_integral');
        if (!Validate::is($data['min_integral'], 'require')) {
            return '请输入最新积分值';
        }
        if (!Validate::is($data['min_integral'], 'number')) {
            return '积分值只能是数字';
        }
        if (Validate::gt($data['min_integral'], 1000000, '')) {
            return '积分值只能在0-1000000之间';
        }
        $data['max_integral'] = input('max_integral');
        if (!Validate::is($data['max_integral'], 'require')) {
            return '请输入最大积分值';
        }
        if (!Validate::is($data['max_integral'], 'number')) {
            return '积分值只能是数字';
        }
        if (Validate::gt($data['max_integral'], 1000000, '')) {
            return '积分值只能在0-1000000之间';
        }
        $data['star_num'] = input('star_num');
        if (!Validate::is($data['star_num'], 'require')) {
            return '请输入星星数量';
        }
        if (!Validate::is($data['star_num'], 'number')) {
            return '输入星星数量必须是数字';
        }
        if (Validate::gt($data['star_num'], 100, '')) {
            return '输入的星星数量在0-100之间';
        }
        $data['description'] = input('description');
        if (!Validate::max($data['description'], 100)) {
            return '输入的积分等级说明不能超过100个字符';
        }
        return $data;
    }

    /**
     * @return false|string
     * Description 删除会员积分等级
     */
    public function delIntegralInfo()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $result = UserIntegralLevel::del($where);
        if ($result) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员邮件列表数据
     */
    public function getMemberEmailList()
    {
        $list = UserEmail::getList([], 'id,address,uid,title,create_time,status', 'id desc');
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['status'] = UserEmail::$email_status[$value['status']];
            $list['data'][$key]['nickname'] = User::getField(['id'=>$value['uid']], 'nickname', 'id desc', true);
        }
        return self::ajaxOkdata($list, 'get data success');
    }

    /**
     * @return false|string
     * Description 删除会员邮件方法
     */
    public function delMemberEmailInfo()
    {
        $id = input('id');
        if (!isset($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $result = UserEmail::del($where);
        if ($result) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 显示会员邮件详细内容
     */
    public function showEmailInfo()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        //设置会员表和会员邮件表表名
        $where = ["id" => $id];
        $email_info = UserEmail::getOne($where);
        $email_info['status'] = UserEmail::$email_status[$email_info['status']];
        $email_info['nickname'] = User::getField(['id' => $email_info['uid']], 'nickname', 'id desc', true);
        $this->assign('email_info', $email_info);
        return View('Email_show_info');
    }

    /**
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员短信列表数据
     */
    public function getMemberSmsList()
    {
        $list = UserSms::getList([], 'id,phone,title,sms_code,create_time,status,uid', 'id desc');
        //获取会员等级列表
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['status'] = UserSms::$sms_status[$value['status']];
            if ($value['uid'] != 0)
                $list['data'][$key]['nickname'] = User::getField(['id' => $value['uid']], 'nickname', 'id desc', true);
        }
        return self::ajaxOkdata($list);
    }

    public function getUserLevelList()
    {
        return UserLevel::getAll([], 'id,level_name');
    }

}
