<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/26
 * Time: 17:33
 * Description：系统配置控制器
 */

namespace app\admin\controller;

use app\admin\model\Menu;
use SucaiZ\Cache\Mysql;
use SucaiZ\File;
use think\Request;
use think\Session;
use think\Db;
use think\View;

class Sysconfig extends Common
{
    private $redis;
    private $sysconfig_type = [
        '1' => ['k' => 1, 'type' => 'test', 'value' => '文本框'],
        '2' => ['k' => 2, 'type' => 'radio', 'value' => '单选框'],
        '3' => ['k' => 3, 'type' => 'checkbox', 'value' => '多选框'],
        '4' => ['k' => 4, 'type' => 'select', 'value' => '下拉框'],
        '5' => ['k' => 5, 'type' => 'textarea', 'value' => '文本框'],
    ];
    private $preserve_redis_key = 'preserve';

    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
    }

    //获取系统配置的默认栏目命名规则和栏目列表默认命名规则
    public function getnamerule()
    {
        $sysconfig = model('Sysconfig');
        $sysconfig_arr = $sysconfig->getSysconfig();
        $a['column_article_rule'] = $sysconfig_arr['cfg_column_article_name_rule'];
        $a['column_list_rule'] = $sysconfig_arr['cfg_column_list_name_rule'];
        return json_encode($a, JSON_UNESCAPED_UNICODE);
    }

    //验证操作口令
    public function getverifnum()
    {
        $verify_num = rand(10000, 99999);
        $verify_key = 'admin_' . Session::get('admin')['id'] . $_SERVER['REMOTE_ADDR'];
        $verify_key_num = $this->redis->get($verify_key);
        if (!$verify_key_num) {
            $this->redis->set($verify_key, $verify_num, 300);
        }
        $a['verify_num'] = $this->redis->get($verify_key);
        return json_encode($a, JSON_UNESCAPED_UNICODE);
    }

    //获取管理员登录日志表json数据
    public function getadminloginlogjson()
    {
        //获取管理员列表
        $user = model('User');
        $user_list = $user->getUserList([], ' id,user_name,type,nick_name ');
        $user_list = array_column($user_list, null, 'id');
        //获取管理员等级表
        $user_level = $user->getUserLevelList();
        $user_level = array_column($user_level, null, 'id');
        //获取用户登录日志
        $log_list = model('LogLogin')->getList(['type'=>2]);
        foreach ($log_list['data'] as $key => $value) {
            $log_list['data'][$key]['nick_name'] = $user_list[$value['uid']]['nick_name'];
            $log_list['data'][$key]['user_name'] = $user_list[$value['uid']]['user_name'];
            $log_list['data'][$key]['type'] = $user_level[$user_list[$value['uid']]['type']]['name'];
            $log_list['data'][$key]['login_time'] = date('Y-m-d H:i:s', $value['login_time']);
        }
        return self::ajaxOkdata($log_list,'','get data success');

    }

    //显示系统基本设置
    public function showSysconfigBasic()
    {
        //获取参数分类
        $Sysconfig = model('Sysconfig');
        $sysconfig_group = $Sysconfig->getSysconfigGroup();
        $this->assign('sysconfig_group', $sysconfig_group);
        $Sysconfig_list = $Sysconfig->getSysconfigList();
        foreach ($Sysconfig_list as $key => $value) {
            if ($value['type'] == 2 || $value['type'] == 3 && !empty($value['data'])) {
                $Sysconfig_list[$key]['data'] = explode(',', explode(':', $value['data'])[1]);
            }
            if ($value['type'] == 3) {
                $Sysconfig_list[$key]['value'] = explode(',', $value['value']);
            }
        }
        $this->assign('sysconfig_list', $Sysconfig_list);
        return View('Sysconfig_show');
    }

    //系统参数列表
    public function showSysconfigList()
    {
        return View('Sysconfig_show_list');
    }

    //获取系统参数列表
    public function getSysconfigList()
    {
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $Sysconfig = model('Sysconfig');
        $sysconfig_count = $Sysconfig->getSysconfigCount($where);
        $sysconfig_list = $Sysconfig->getSysconfigList($where, ' * ', $limit, 'id ASC');
        //获取系统参数分类列表
        $sysconfig_group = $Sysconfig->getSysconfigGroup();
        $sysconfig_group_arr = [];
        foreach ($sysconfig_group as $key => $value) {
            $sysconfig_group_arr[$value['id']] = $value;
        }
        foreach ($sysconfig_list as $key => $value) {
            $sysconfig_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if (!empty($value['alter_time'])) {
                $sysconfig_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
            $sysconfig_list[$key]['type'] = $this->sysconfig_type[$value['type']]['value'];
            $sysconfig_list[$key]['class'] = $sysconfig_group_arr[$value['class']]['name'];
        }
        $arr = [
            'data' => $sysconfig_list,
            'count' => $sysconfig_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //显示系统参数分组
    public function showSysconfigGroup()
    {
        return View('Sysconfig_show_group');
    }

    //显示系统参数分组列表
    public function getSysconfigGroup()
    {
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $Sysconfig = model('Sysconfig');
        $group_count = $Sysconfig->getSysconfigGroupCpunt($where);
        $group_list = $Sysconfig->getSysconfigGroup($where, ' * ', $limit, 'id ASC');
        foreach ($group_list as $key => $value) {
            $group_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if (!empty($value['alter_time'])) {
                $group_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
        }
        $arr = [
            'data' => $group_list,
            'count' => $group_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //新建系统分组
    public function addSysconfigGroup()
    {
        if (Request::instance()->isPost()) {
            $name = input('name');
            if (!isset($name)) {
                echo '非法访问';
                die;
            }
            if ($name == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的分组名不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else if (mb_strlen($name, 'UTF-8') > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的分组名不能超过20个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $sort = input('sort');
            if (!isset($sort) || !is_numeric($sort)) {
                echo '非法访问';
                die;
            }
            if ($sort > 127) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序不能超过127';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $b = [
                'name' => $name,
                'sort' => $sort,
                'create_time' => time()
            ];
            $sysconfig = model('Sysconfig');
            $res = $sysconfig->addSysconfigGroup($b);
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = '添加成功';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '添加失败';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            return View('Sysconfig_add_group');
        }
    }

    //修改系统分组
    public function alterSysconfigGroup()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            $name = input('name');
            if (!isset($name)) {
                echo '非法访问';
                die;
            }
            if ($name == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的分组名不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else if (mb_strlen($name, 'UTF-8') > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的分组名不能超过20个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $sort = input('sort');
            if (!isset($sort) || !is_numeric($sort)) {
                echo '非法访问';
                die;
            }
            if ($sort > 127) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序不能超过127';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $b = [
                'name' => $name,
                'sort' => $sort,
                'alter_time' => time()
            ];
            $sysconfig = model('Sysconfig');
            $res = $sysconfig->alterSysconfigGroup($where, $b);
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
            //获取分组信息
            $sysconfig = model('Sysconfig');
            $group_info = $sysconfig->getSysconfigGroupInfo($where);
            $this->assign('group_info', $group_info);
            $this->assign('id', $id);
            return View('Sysconfig_alter_group');
        }
    }

    //删除系统参数分组
    public function delSysconfigGroup()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        $where = ['id' => $id];
        $sysconfig = model('Sysconfig');
        $res = $sysconfig->delSysconfigGroup($where);
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

    //新增系统参数
    public function addSysconfig()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $description = input('description');
            if (!isset($description)) {
                echo '非法访问';
                die;
            }
            if ($description == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '参数说明不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($description, 'UTF-8') > 50) {
                $a['errorcode'] = 1;
                $a['msg'] = '参数说明不能超过50个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $type = input('type');
            if (!isset($type) || empty($type) || !is_numeric($type)) {
                echo '非法访问';
                die;
            }
            $sort = input('sort');
            if (!isset($sort)) {
                echo '非法访问';
                die;
            }
            if (!is_numeric($sort)) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序只能为数字哦';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if ($sort == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if ($sort > 127) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序不能超过127';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $value = input('value');
            if (!isset($value)) {
                echo '非法访问';
                die;
            }
            if ($value == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '参数值不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($value, 'UTF-8') > 200) {
                $a['errorcode'] = 1;
                $a['msg'] = '参数值不能超过200个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $name = input('name');
            if (!isset($name)) {
                echo '非法访问';
                die;
            }
            if ($name == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '变量名不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($name, 'UTF-8') > 100) {
                $a['errorcode'] = 1;
                $a['msg'] = '变量名不能超过100个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $data = input('data');
            if (!isset($data)) {
                echo '非法访问';
                die;
            }
            $class = input('class');
            if (!isset($class) || !is_numeric($class)) {
                echo '非法访问';
                die;
            }
            $b = [
                'description' => $description,
                'value' => $value,
                'name' => $name,
                'type' => $type,
                'data' => $data,
                'sort' => $sort,
                'class' => $class,
                'create_time' => time()
            ];
            $sysconfig = model('Sysconfig');
            $res = $sysconfig->addSysconfig($b);
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = '新增成功';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '新增失败';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }

        } else {
            $this->assign('sysconfig_type', $this->sysconfig_type);
            $sysconfig = model('Sysconfig');
            $group = $sysconfig->getSysconfigGroup([], ' id,name ');
            $this->assign('group', $group);
            return View('Sysconfig_add');
        }
    }

    //删除系统参数方法
    public function delSysconfigInfo()
    {
        $id = input('id');
        if (!isset($id)) {
            echo '非法访问';
            die;
        }
        $where = ['id' => $id];
        $sysconfig = model('Sysconfig');
        $res = $sysconfig->delSysconfig($where);
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

    //修改系统参数方法
    public function alterSysconfig()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            //验证数据
            $description = input('description');
            if (!isset($description)) {
                echo '非法访问';
                die;
            }
            if ($description == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '参数说明不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($description, 'UTF-8') > 50) {
                $a['errorcode'] = 1;
                $a['msg'] = '参数说明不能超过50个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $type = input('type');
            if (!isset($type) || empty($type) || !is_numeric($type)) {
                echo '非法访问';
                die;
            }
            $sort = input('sort');
            if (!isset($sort)) {
                echo '非法访问';
                die;
            }
            if (!is_numeric($sort)) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序只能为数字哦';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if ($sort == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if ($sort > 127) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的排序不能超过127';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $value = input('value');
            if (!isset($value)) {
                echo '非法访问';
                die;
            }
            if ($value == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '参数值不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($value, 'UTF-8') > 200) {
                $a['errorcode'] = 1;
                $a['msg'] = '参数值不能超过200个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $name = input('name');
            if (!isset($name)) {
                echo '非法访问';
                die;
            }
            if ($name == '') {
                $a['errorcode'] = 1;
                $a['msg'] = '变量名不能为空';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (mb_strlen($name, 'UTF-8') > 100) {
                $a['errorcode'] = 1;
                $a['msg'] = '变量名不能超过100个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $data = input('data');
            if (!isset($data)) {
                echo '非法访问';
                die;
            }
            $class = input('class');
            if (!isset($class) || !is_numeric($class)) {
                echo '非法访问';
                die;
            }
            $b = [
                'description' => $description,
                'value' => $value,
                'name' => $name,
                'type' => $type,
                'data' => $data,
                'sort' => $sort,
                'class' => $class,
                'alter_time' => time()
            ];
            $sysconfig = model('Sysconfig');
            $res = $sysconfig->alterSysconfigInfo($where, $b);
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
            $this->assign('sysconfig_type', $this->sysconfig_type);
            $sysconfig = model('Sysconfig');
            $group = $sysconfig->getSysconfigGroup([], ' id,name ');
            $this->assign('group', $group);
            $sysconfig_info = $sysconfig->getSysconfigInfo($where);
            $this->assign('sysconfig_info', $sysconfig_info);
            $this->assign('id', $id);
            return View('Sysconfig_alter_info');
        }
    }

    //修改系统参数值方法
    public function alterSysconfigValue()
    {
        $class = input('class');
        $data = input();
        //处理前台提交的数据
        foreach ($data as $key => $value) {
            //判断前台提交的数据
            $c = checkstr($key, '$');
            if ($c) {
                $e = $c[0];
                $c[0] = [];
                array_push($c[0], $c[1]);
                unset($data[$key]);
                foreach ($data as $k => $v) {
                    $d = checkstr($k, '$');
                    if ($d) {
                        if ($d[0] == $e) {
                            array_push($c[0], $d[1]);
                            unset($data[$k]);
                            $data[$e] = implode(',', $c[0]);
                        }

                    }else {
                        if(!isset($data[$e])){
                            $data[$e] = implode(',', $c[0]);
                        }
                    }
                }
            }
        }
        if (!isset($class) || !is_numeric($class)) {
            echo '非法访问';
            die;
        }
        unset($data['class']);
        $sysconfig = model('Sysconfig');
        $error = [];
        foreach ($data as $key => $value) {
            $where = [
                'class' => $class,
                'name' => $key
            ];
            $d = [
                'value' => $value,
                'alter_time' => time()
            ];
            $res = Mysql::update(Model('sysconfig')->table,'name',$key,$d);
            if (!$res) {
                $error[] = 2;
            }
            unset($where);
        }
        if (in_array(2, $error)) {
            $a['errorcode'] = 1;
            $a['msg'] = '修改失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 0;
            $a['msg'] = '修改成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //水印设置
    public function water()
    {
        if (Request::instance()->isPost()) {
            $where = ['id' => 1];
            $status = input('status');
            if (isset($status)) {
                $status = 1;
            } else {
                $status = 2;
            }
            $type = input('type');
            if (!isset($type) || !is_numeric($type)) {
                echo '非法访问';
                die;
            }
            $width = input('width');
            if (!isset($width) || !is_numeric($width)) {
                echo '非法访问';
                die;
            }
            if ($width < 0 || $width > 200) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的宽度只能在0-200之间';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $height = input('height');
            if (!isset($height) || !is_numeric($height)) {
                echo '非法访问';
                die;
            }
            if ($height < 0 || $height > 200) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的高度只能在0-200之间';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $font_value = input('font_value');
            if (!isset($font_value)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($font_value, 'UTF-8') > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的水印文字不能超过20个字符';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $font_size = input('font_size');
            if (!isset($font_size) || !is_numeric($font_size)) {
                echo '非法访问';
                die;
            }
            if ($font_size < 0 || $font_size > 50) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的字体大小只能在0-50之间';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $color = input('color');
            if (!isset($color)) {
                echo '非法访问';
                die;
            }
            if (mb_strlen($color, 'UTF-8') > 7) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的水印文字颜色不能超过7个字符';
            }
            $place = input('place');
            if (!isset($place) || !is_numeric($place)) {
                echo '非法访问';
                die;
            }
            if ($place < 0 || $place > 5) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的水印位置只能介于0-5的整数';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $x = input('x');
            if (!isset($x) || !is_numeric($x)) {
                echo '非法访问';
                die;
            }
            if ($x < 0 || $x > 200) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的宽度只能在0-200之间';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $y = input('y');
            if (!isset($y) || !is_numeric($y)) {
                echo '非法访问';
                die;
            }
            if ($y < 0 || $y > 200) {
                $a['errorcode'] = 1;
                $a['msg'] = '输入的高度只能在0-200之间';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $b = [
                'place' => $place,
                'type' => $type,
                'status' => $status,
                'font_value' => $font_value,
                'font_size' => $font_size,
                'color' => $color,
                'width' => $width,
                'height' => $height,
                'alter_time' => time(),
                'x' => $x,
                'y' => $y
            ];
            $sysconfig = model('Sysconfig');
            $res = $sysconfig->alterWaterInfo($where, $b);
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
            $sysconfig_water = model('Sysconfig');
            $water_info = $sysconfig_water->getWaterInfo();
            $this->assign('water_info', $water_info);
            return View('Sysconfig_water_show');
        }
    }

//上传水印文件
    public
    function updateWater()
    {
        $file_info = $_FILES['file'];
        $id = input('id');
        $type = input('type');
        if (!isset($id) || !isset($type)) {
            echo '非法访问';
            die;
        }

        if ($file_info['error'] != 0) {
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        if ($type == 'img') {
            if ($file_info['type'] == 'image/png') {
                $file_dir = './upload/water/img';
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '只能上传png格式的文件哦';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else if ($type == 'font') {
            if ($file_info['type'] == 'application/octet-stream') {
                $file_dir = './upload/water/font';
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '只能上传字体文件哦';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        }
        $ext = pathinfo($file_info['name'])['extension'];
        $new_file = UploadOneFile($file_info['tmp_name'], $file_dir, $ext);
        if ($new_file) {
            $sysconfig = model('Sysconfig');
            $where = ['id' => 1];
            if ($type == 'img') {
                $data['water_img'] = $new_file;
            } else if ($type == 'font') {
                $data['font_family'] = $new_file;
            }
            $res = $sysconfig->alterWaterInfo($where, $data);
            if ($res) {
                $a['errorcode'] = 0;
                $a['msg'] = '上传成功';
                $a['url'] = $new_file;
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = '上传失败';
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

//获取备份列表数据
    public
    function getBackUpList()
    {
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $Backup = model('Backup');
        $backup_list = $Backup->getBackUpList($where, ' * ', $limit, 'id desc');
        $backup_count = $Backup->getBackUpCount($where);
        foreach ($backup_list as $key => $value) {
            $backup_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if ($value['status'] == 1) {
                $backup_list[$key]['status'] = '创建备份';
            } else if ($value['status'] == 2) {
                $backup_list[$key]['status'] = '完成备份';
            }
            if (!empty($value['roll_time'])) {
                $backup_list[$key]['roll_time'] = date('Y-m-d H:i:s', $value['roll_time']);
            }
        }
        $arr = [
            'data' => $backup_list,
            'count' => $backup_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

//删除数据库备份方法
    public
    function delBackUp()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        $is_oss = input('is_oss');
        if (!isset($is_oss) || !is_numeric($is_oss)) {
            echo '非法访问';
            die;
        }

        //组合条件
        $where = ['id' => $id];

        //实例化backup数据库类
        $backup = model('Backup');

        //首先根据id取出对应备份存储的位置
        $backup_info = $backup->getBackUpInfo($where, 'file_path');
        $backup_path = $backup_info['file_path'];

        //判断备份文件储存位置
        if ($is_oss == 1) {

            //删除文峰文件和数据库信息
            if (file_exists($backup_path)) {
                if (!unlink($backup_path)) {
                    $a['errorcode'] = 1;
                    $a['msg'] = '删除失败';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            }
            $res = $backup->delBackUp($where);
        } else if ($is_oss == 2) {

            //从数据中分离出bucket和object
            $info = explode(':', $backup_path);
            $bucket = $info[0];

            //组合数据，获取object
            $str = $bucket . ':';
            $object = str_replace($str, '', $backup_path);

            //设置阿里云的bucket和object
            \SucaiZ\File::setOssInfo($bucket, $object);

            //调用阿里云Oss删除方法
            if (\SucaiZ\File::delOssFile()) {

                //删除数据库内数据
                $res = $backup->delBackUp($where);
            } else {
                $res = false;
            }

        }

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

//新建数据库备份
    public
    function createBackup()
    {
        $d = [
            'function' => 'backup',
            'user_id' => Session::get('admin')['id'],
            'user_type' => 2
        ];

        task('backup', $d);
        $a['errorcode'] = 0;
        $a['msg'] = '已添加到队列，稍后刷新查看执行结果';
        return json_encode($a, JSON_UNESCAPED_UNICODE);
    }

//回滚数据库数据
    public
    function rollBack()
    {

    }

//网站维护
    public
    function preserve()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $time = input('time');
            if (!isset($time)) {
                echo '非法访问';
                die;
            }
            $date = strtotime($time);
            $new = time();
            $ttl = $date - $new;
            $preserve_status_key = $this->preserve_redis_key . '_status';
            $preserve_info_key = $this->preserve_redis_key . '_info';
            $redis = getRedis();
            $redis->set($preserve_status_key, 1, $ttl);
            $redis->set($preserve_info_key, ['ttl' => $ttl], $ttl);
            $a['errorcode'] = 0;
            $a['msg'] = '设置成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);

        } else {
            return View('Sysconfig_preserve');
        }

    }

    //获取消息队列列表
    public function getQueueList(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $queue = model('Queue');
        $queue_count = $queue->getQueueCount($where);
        $queue_list = $queue->getQueueList($where, ' * ', $limit, 'id DESC');
        foreach ($queue_list as $key => $value) {

            //处理队列创建时间
            $queue_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);

            //处理出队时间
            if(!empty($value['out_time'])){
                $queue_list[$key]['out_time'] = date('Y-m-d H:i:s', $value['out_time']);
            }

            //处理队列类型
            if($value['queue_type'] == 1){
                $queue_list[$key]['queue_type'] = '基础队列';
            }else if($value['queue_type'] == 2){
                $queue_list[$key]['queue_type'] = '执行队列';
            }

            //处理队列状态
            if($value['status'] == 1){
                $queue_list[$key]['status'] = '创建队列';
            }else if($value['status'] == 2){
                $queue_list[$key]['status'] = '执行成功';
            }else if($value['status'] == 3){
                $queue_list[$key]['status'] = '执行失败';
            }
        }
        $arr = [
            'data' => $queue_list,
            'count' => $queue_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }
    //菜单管理
    public function menuManage(){
        return View('Sysconfig_show_menu');
    }
    //获取菜单分类树数据
    public function getMenuTreeJson(){
        //获取菜单分类列表数据
        $menu = new Menu();
        $menu_list = $menu->getClassList([] ,' id,name ' ,100 ,' id asc ');
        //循环处理数据
        $arr = [];
        foreach($menu_list as $key => $value){
            $a = [
                'id'=>$value['id'],
                'name'=>$value['name'],
                'parent_id'=>0,
                'children'=>[]
            ];
            $arr[] = $a;
        }
        return json_encode($menu_list ,JSON_UNESCAPED_UNICODE);
    }
    //获取菜单列表
    public function getMenuList(){
        //验证数据
        $class = input('class');
        if(!isset($class) || !is_numeric($class)){
            echo '非法访问';die;
        }
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        //构建查询条件
        $where = ['class'=>$class];
        $menu = new Menu();
        $menu_list = $menu->getMenuList($where ,' * ' ,$limit ,' id asc ');
        $menu_cont = $menu->getMenuCount($where);
        //循环处理列表数据
        foreach($menu_list as $key => $value){
            $menu_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
            $menu_list[$key]['alter_time'] = date('Y-m-d H:i:s' ,$value['alter_time']);
        }
        $arr = [
            'data' => $menu_list,
            'count' => $menu_cont,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }
    //添加系统分类
    public function addMenuClass(){
        if(Request::instance()->isPost()){
            //验证数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';die;
            }
            if(empty($name)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的分类名称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($name ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的分类名称不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据添加到数据库
            $arr = [
                'name'=>$name,
                'create_time'=>time()
            ];
            $menu = new Menu();
            if($menu->addClass($arr)){
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
            return View('Sysconfig_add_menu_class');
        }
    }
    //添加菜单方法
    public function addMenu(){
        $menu = new Menu();
        $parent_id = input('parent_id');
        if(isset($parent_id) && !is_numeric($parent_id)){
            echo '非法访问';die;
        }else if(isset($parent_id) && is_numeric($parent_id)){
            $parent_id = $parent_id;
        }else{
            $parent_id = 0;
        }
        if(Request::instance()->isPost()){
            //验证数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';die;
            }
            if(empty($name)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单名称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($name ,'UTF-8') >15){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单名称不能超过15个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $ico = input('ico');
            if(!isset($ico)){
                echo '非法访问';die;
            }
            if(empty($ico)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单图标不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($ico ,'UTF-8') >100){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单图标不能超过100个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $url = input('url');
            if(!isset($url)){
                echo '非法访问';die;
            }
            if(mb_strlen($url ,'UTF-8') >50){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的跳转链接不能超过50个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $model_id = input('model_id');
            if(!isset($model_id) || !is_numeric($model_id)){
                echo '非法访问';die;
            }

            $class = input('class');
            if(!isset($class) || !is_numeric($class)){
                echo '非法访问';die;
            }

            //组合数据添加到数据库
            $arr = [
                'parent_id'=>$parent_id,
                'name'=>$name,
                'ico'=>$ico,
                'url'=>$url,
                'model_id'=>$model_id,
                'class'=>$class,
                'create_time'=>time()
            ];

            if($menu->addMenu($arr)){
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
            //获取菜单分类列表分配到页面
            $menu_class_list = $menu->getClassList([] ,' id,name ' ,100 ,' id asc ');
            View::share('class_list' ,$menu_class_list);
            View::share('parent_id' ,$parent_id);
            //获取模块列表
            $rbac = new \app\admin\model\Rbac();
            $model_list = $rbac->getModelList([] ,' id,name ' ,1000 ,' id asc ');
            View::share('model_list' ,$model_list);
            return View('Sysconfig_add_menu');
        }
    }

    //修改菜单信息方法
    public function alterMenuInfo(){
        //验证数据
        $id = input('id');
        if(!isset($id) || empty($id) || !is_numeric($id)){
            echo '非法访问';die;
        }
        //构造查询条件
        $where = ['id'=>$id];
        $menu = new Menu();
        if(Request::instance()->isPost()){
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';die;
            }
            if(empty($name)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单名称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($name ,'UTF-8') >15){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单名称不能超过15个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $parent_id = input('parent_id');
            if(!isset($parent_id) || !is_numeric($parent_id)){
                echo '非法访问';die;
            }
            $ico = input('ico');
            if(!isset($ico)){
                echo '非法访问';die;
            }
            if(empty($ico)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单图标不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($ico ,'UTF-8') >100){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的菜单图标不能超过100个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $url = input('url');
            if(!isset($url)){
                echo '非法访问';die;
            }
            if(mb_strlen($url ,'UTF-8') >50){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的跳转链接不能超过50个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $model_id = input('model_id');
            if(!isset($model_id) || !is_numeric($model_id)){
                echo '非法访问';die;
            }

            $class = input('class');
            if(!isset($class) || !is_numeric($class)){
                echo '非法访问';die;
            }

            //组合数据添加到数据库
            $arr = [
                'parent_id'=>$parent_id,
                'name'=>$name,
                'ico'=>$ico,
                'url'=>$url,
                'model_id'=>$model_id,
                'class'=>$class,
                'alter_time'=>time()
            ];

            if($menu->alterMenuInfo($where ,$arr)){
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
            //获取菜单分类列表分配到页面
            $menu_class_list = $menu->getClassList([] ,' id,name ' ,100 ,' id asc ');
            View::share('class_list' ,$menu_class_list);
            //查询菜单详细信息
            $menu_info = $menu->getMenuInfo($where);
            View::share('info' ,$menu_info);
            //获取模块列表
            $rbac = new \app\admin\model\Rbac();
            $model_list = $rbac->getModelList([] ,' id,name ' ,1000 ,' id asc ');
            View::share('model_list' ,$model_list);
            return View('Sysconfig_alter_menu');
        }
    }

    //获取分类下的父级菜单
    public function getParentMenu(){
        //验证数据
        $class = input('class');
        if(!isset($class) || !is_numeric($class)){
            echo '非法访问';die;
        }
        //构建查询条件
        $where = ['class'=>$class,'parent_id'=>0];
        //查询菜单数据
        $menu = new Menu();
        $menu_list = $menu->getMenuList($where ,' id,name ' ,100 ,' id asc ');
        if(!empty($menu_list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$menu_list
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'获取数据失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //删除菜单方法
    public function delMenu(){
        //验证数据
        $id = input('id');
        if(!isset($id) || empty($id) || !is_numeric($id)){
            echo '非法访问';die;
        }
        //组合条件
        $where = ['id'=>$id];
        $menu = new Menu();
        $res = $menu->delMenuInfo($where);
        if($res){
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

    //上传菜单图标方法
    public function uploadIce(){
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        File::setArticleInfo(0,'后台菜单图标');
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $url = File::$url;
            return $this->ajaxOkdata([
                'url'=>$url,
            ],'上传成功');
        } else {
            return $this->ajaxErrordata([]);
        }
    }

}

