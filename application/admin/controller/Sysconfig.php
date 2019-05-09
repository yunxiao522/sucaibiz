<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/26
 * Time: 17:33
 * Description：系统配置控制器
 */

namespace app\admin\controller;

use app\model\AdminUser;
use app\model\LogLogin;
use app\model\BackUp;
use app\model\SysconfigGroup;
use app\model\Sysconfig as Sysconfig_Model;
use app\model\SysconfigWater;
use app\model\Upload;
use app\model\Queue;
use app\model\Menu;
use app\model\MenuType;
use app\model\RbacModel;
use SucaiZ\config;
use SucaiZ\File;
use think\Request;
use think\Session;
use think\Db;
use think\View;

class Sysconfig extends Common
{
    private $redis;
    private $preserve_redis_key = 'preserve';

    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis();
    }

    /**
     * @return false|string
     * Description 获取系统配置的默认栏目命名规则和栏目列表默认命名规则
     */
    public function getnamerule()
    {
        $a['column_article_rule'] = config::get('cfg_column_article_name_rule');
        $a['column_list_rule'] = config::get('cfg_column_list_name_rule');
        return json_encode($a, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return false|string
     * Description 验证操作口令
     */
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

    /**
     * @return false|string
     * Description 获取管理员登录日志表json数据
     */
    public function getadminloginlogjson()
    {
        //获取用户登录日志
        $log_list = LogLogin::getList(['type' => 2], 'id,login_time,login_ip,uid,browser');
        foreach ($log_list['data'] as $key => $value) {
            $User_Info = AdminUser::getOne(['id' => $value['uid']], 'user_name,type,nick_name');
            $log_list['data'][$key]['nick_name'] = $User_Info['nick_name'];
            $log_list['data'][$key]['user_name'] = $User_Info['user_name'];
        }
        return self::ajaxOkdata($log_list, '', 'get data success');
    }

    /**
     * @return \think\response\View
     * Description 显示系统基本设置
     */
    public function showSysconfigBasic()
    {
        //获取参数分类
        $Sysconfig_Group = SysconfigGroup::getAll([], 'id,name', 'id asc');
        view::share('sysconfig_group', $Sysconfig_Group);
        //获取参数列表
        $Sysconfig_List = Sysconfig_Model::getAll([], 'id,description,value,name,type,data,class', 1000);
        //循环处理参数列表数据
        foreach ($Sysconfig_List as $key => $value) {
            if ($value['type'] == 2 || $value['type'] == 3 && !empty($value['data'])) {
                $Sysconfig_List[$key]['data'] = explode(',', explode(':', $value['data'])[1]);
            }
            if ($value['type'] == 3) {
                $Sysconfig_List[$key]['value'] = explode(',', $value['value']);
            }
        }
        view::share('sysconfig_list', $Sysconfig_List);
        return View('Sysconfig_show');
    }

    /**
     * @return false|string
     * Description 获取系统参数列表
     */
    public function getSysconfigList()
    {
        $where = [];
        $Sysconfig_List = Sysconfig_Model::getList($where, 'id,description,sort,name,type,class,create_time,alter_time', 'id asc');
        //循环处理列表数据
        foreach ($Sysconfig_List['data'] as $key => $value) {
            $Sysconfig_List['data'][$key]['type'] = Sysconfig_Model::$sysconfig_type[$value['type']]['value'];
            $Sysconfig_List['data'][$key]['class'] = SysconfigGroup::getField(['id' => $value['class']], 'name', 'id desc', true);
        }
        return self::ajaxOkdata($Sysconfig_List, 'get data success');
    }

    /**
     * @return false|string
     * Description 获取系统参数分组列表数据
     */
    public function getSysconfigGroup()
    {
        $where = [];
        $Sysconfig_Group_List = SysconfigGroup::getList($where, '*', 'id asc');
        return self::ajaxOkdata($Sysconfig_Group_List, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 新建系统参数分组
     */
    public function addSysconfigGroup()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $data = $this->checkSysconfigGroupForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['create_time'] = time();
            $res = SysconfigGroup::add($data);
            if ($res) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            return View('Sysconfig_add_group');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改系统分组信息
     */
    public function alterSysconfigGroup()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            //验证数据
            $data = $this->checkSysconfigGroupForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['alter_time'] = time();
            $res = SysconfigGroup::edit($where, $data);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            //获取分组详细信息
            $Group_Info = SysconfigGroup::getOne($where);
            view::share('group_info', $Group_Info);
            view::share('id', $id);
            return View('Sysconfig_alter_group');
        }
    }

    /**
     * @return array|string|true
     * Description 获取验证系统参数分组表单
     */
    protected function checkSysconfigGroupForm()
    {
        $rule = [
            'name' => 'require|max:20',
            'sort' => 'require|number',
        ];
        $msg = [
            'name.require' => '请输入分组名称',
            'name.max' => '分组名称不能超过20个字符',
            'sort.require' => '请输入排序',
            'sort.number' => '排序序号只能是数字'
        ];
        return $this->checkForm($rule, $msg, function ($input) {
            $data = [
                'name' => $input['name'],
                'sort' => $input['sort']
            ];
            return $data;
        });
    }

    /**
     * @return false|string
     * Description 删除系统参数分组
     */
    public function delSysconfigGroup()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        //先判断分组下面是否还有参数设置
        $Sysconfig_Num = Sysconfig_Model::getCount(['class' => $id], 'id');
        if (!$Sysconfig_Num != 0) {
            return self::ajaxError('该分组下还有参数设置');
        }
        $where = ['id' => $id];
        $res = SysconfigGroup::del($where);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 新增系统参数
     */
    public function addSysconfig()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $data = $this->checkSysconfigForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['create_time'] = time();
            $res = Sysconfig_Model::add($data);
            if ($res) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            $Group_List = SysconfigGroup::getAll([], ' id,name ');
            view::share('sysconfig_type', Sysconfig_Model::$sysconfig_type);
            view::share('group', $Group_List);
            return View('Sysconfig_add');
        }
    }

    /**
     * @return false|string
     * Description 删除系统参数方法
     */
    public function delSysconfigInfo()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $res = Sysconfig_Model::del($where);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改系统参数方法
     */
    public function alterSysconfig()
    {
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            //验证数据
            $data = $this->checkSysconfigForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['alter_time'] = time();
            $res = Sysconfig_Model::edit($where, $data);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $Sysconfig_Info = Sysconfig_Model::getOne($where, '*', 'id desc', true);
            $Group_List = SysconfigGroup::getAll([], ' id,name ');
            view::share('group', $Group_List);
            view::share('sysconfig_type', Sysconfig_Model::$sysconfig_type);
            view::share('sysconfig_info', $Sysconfig_Info);
            view::share('id', $id);
            return View('Sysconfig_alter_info');
        }
    }

    /**
     * @return array|string|true
     * Description 验证获取系统参数表单数据
     */
    protected function checkSysconfigForm()
    {
        $rule = [
            'description' => 'require|max:50',
            'type' => 'require|number',
            'sort' => 'require|number',
            'value' => 'require|max:200',
            'name' => 'require|max:100',
            'class' => 'require|number'
        ];
        $msg = [
            'description.require' => '请输入参数描述',
            'description.max' => '参数描述不能超过50个字符',
            'type.require' => '请选择参数类型',
            'type.number' => '参数类型的值只能是数字',
            'sort.require' => '请输入排序数字哦',
            'sort.number' => '排序只能是数字哦',
            'value.require' => '请输入参数值',
            'value.max' => '参数值不能超过200个字符哦',
            'name.require' => '请输入变量名',
            'name.max' => '变量名长度不能超过100个字符',
            'class.require' => '请选择参数分组',
            'class.number' => '参数分组只能是数字'
        ];
        return $this->checkForm($rule, $msg, function ($input) {
            $data = [
                'description' => $input['description'],
                'type' => $input['type'],
                'sort' => $input['sort'],
                'value' => $input['value'],
                'name' => $input['name'],
                'class' => $input['class'],
                'data' => $input['data']
            ];
            return $data;
        });
    }

    /**
     * @return false|string
     * Description 修改系统参数值方法
     */
    public function alterSysconfigValue()
    {
        $class = input('class');
        if (empty($class) || !is_numeric($class)) {
            return self::ajaxError('非法访问');
        }
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
                    } else {
                        if (!isset($data[$e])) {
                            $data[$e] = implode(',', $c[0]);
                        }
                    }
                }
            }
        }
        unset($data['class']);
        Db::startTrans();
        foreach ($data as $key => $value) {
            $where = [
                'class' => $class,
                'name' => $key
            ];
            $d = [
                'value' => $value,
                'alter_time' => time()
            ];
            $res = Sysconfig_Model::edit($where, $d, true);
            if (!$res) {
                Db::rollback();
                return self::ajaxError('修改失败');
            }
        }
        Db::commit();
        return self::ajaxOk('修改成功');
    }

    /**
     * @return false|string|\think\response\View
     * Description 水印设置
     */
    public function water()
    {
        if (Request::instance()->isPost()) {
            $where = ['id' => 1];
            $rule = [
                'type'=>'require|number',
                'width'=>'require|number',
                'height'=>'require|number',
                'font_value'=>'require|max:20',
                'font_size'=>'require|number',
                'color'=>'require|max:7',
                'place'=>'require|number',
                'x'=>'require|number|>:0|<:200',
                'y'=>'require|number|>:0|<:200'
            ];
            $msg = [
                'type.require'=>'请选择水印类型',
                'type.number'=>'水印类型值只能是数值',
                'width.require'=>'请输入水印宽度',
                'width.number'=>'水印宽度只能是数字',
                'height.require'=>'请输入水印高度',
                'height.number'=>'水印高度只能是数字',
                'font_value.require'=>'请输入水印文字',
                'font_value.max'=>'水印文字不能超过20个字符',
                'font_size.require'=>'请输入水印文字大小',
                'font_size.number'=>'水印文字的大小只能是数字',
                'color.require'=>'请输入水印文字颜色',
                'color.max'=>'水印文字颜色不能超过7个字符',
                'place.require'=>'请输入水印位置',
                'place.number'=>'水印位置值只能是数字',
                'x.require'=>'请输入水印水平距离',
                'x.number'=>'水印水平只能是数字',
                'x.lt'=>'水印水平距离不能小于0',
                'x.gt'=>'水印水平距离不能大于200',
                'y.require'=>'请输入水印垂直距离',
                'y.number'=>'水印垂直距离只能是数字',
                'y.lt'=>'水印垂直距离不能小于0',
                'y.gt'=>'水印垂直距离不能大于200'
            ];
            $data = $this->checkForm($rule, $msg, function($input){
                $data = [
                    'type'=>$input['type'],
                    'width'=>$input['width'],
                    'height'=>$input['height'],
                    'font_value'=>$input['font_value'],
                    'font_size'=>$input['font_size'],
                    'color'=>$input['color'],
                    'place'=>$input['place'],
                    'x'=>$input['x'],
                    'y'=>$input['y']
                ];
                return $data;
            });
            if(is_string($data)){
                return self::ajaxError($data);
            }
            $status = input('status');
            if (isset($status)) {
                $status = 1;
            } else {
                $status = 2;
            }
            $data['alter_time'] = time();
            $data['status'] = $status;
            $res = SysconfigWater::edit($where, $data);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $water_info = SysconfigWater::getOne([], '*', 'id desc');
            view::share('water_info', $water_info);
            return View('Sysconfig_water_show');
        }
    }

    /**
     * @return false|string
     * Description 上传水印文件
     */
    public function updateWater()
    {
        $file_info = $_FILES['file'];
        $id = input('id');
        $type = input('type');
        if (!isset($id) || !isset($type)) {
            return self::ajaxError('非法访问');
        }
        if ($file_info['error'] != 0) {
            return self::ajaxError('上传失败');
        }
        if ($type == 'img') {
            if ($file_info['type'] == 'image/png') {
                $file_dir = './upload/water/img';
            } else {
                return self::ajaxError('只能上传png格式的文件哦');
            }
        } else if ($type == 'font') {
            if ($file_info['type'] == 'application/octet-stream') {
                $file_dir = './upload/water/font';
            } else {
                return self::ajaxError('只能上传字体文件哦');
            }
        }
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        File::setArticleInfo(-1, "it's menu ico");
        $res = File::uploadFile($_FILES['file'], '', '', true);
        if(!$res){
            return self::ajaxError('上传失败');
        }
        $url = File::$url;
        $where = ['id' => 1];
        if ($type == 'img') {
            $data['water_img'] = $url;
        } else if ($type == 'font') {
            $data['font_family'] = $url;
        }
        $res = SysconfigWater::edit($where, $data);
        if ($res) {
            return self::ajaxOkdata(['url'=>$url], '上传成功');
        } else {
            return self::ajaxError('上传失败');
        }
    }

    /**
     * @return false|string
     * Description 获取备份列表数据
     */
    public function getBackUpList()
    {
        $where = [];
        $BackUp_List = BackUp::getList($where, '*', 'id desc');
        return self::ajaxOkdata($BackUp_List, 'get data success');
    }

    /**
     * @return false|string
     * Description 删除数据库备份方法
     */
    public function delBackUp()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $is_oss = input('is_oss');
        if (!isset($is_oss) || !is_numeric($is_oss)) {
            return self::ajaxError('非法访问');
        }
        //组合条件
        $where = ['id' => $id];
        //首先根据id取出对应备份存储的位置
        $backup_info = BackUp::getOne($where, 'file_path,status');
        $backup_path = $backup_info['file_path'];
        //v2.0版本,先删除数据库中数据,再执行删除文件操作,防止文件提前被删除,数据库删除出现问题,导致无法恢复。
        //开启事务
        Db::startTrans();
        //如果创建未完成则直接删除数据库备份日志,提示删除成功
        if ($backup_info['status'] == 1) {
            Db::commit();
            return self::ajaxOk('删除成功');
        }
        //删除备份日志中数据
        $res = BackUp::del($where);
        if (!$res) {
            return self::ajaxError('删除失败');
        }
        //删除附件表中数据
        $res = Upload::del(['realurl' => $backup_path]);
        if (!$res) {
            return self::ajaxError('删除失败');
        }
        //判断备份文件储存位置
        if ($is_oss == 1) {
            //删除文峰文件和数据库信息
            if (file_exists($backup_path)) {
                if (!unlink($backup_path)) {
                    Db::rollback();
                    return self::ajaxError('删除失败');
                }
            }
        } else if ($is_oss == 2) {
            //从数据中分离出bucket和object
            $info = explode(':', $backup_path);
            $bucket = $info[0];
            //组合数据，获取object
            $str = $bucket . ':';
            $object = str_replace($str, '', $backup_path);
            //设置阿里云的bucket和object
            File::setOssInfo($bucket, $object);
            //调用阿里云Oss删除方法
            if (!File::delOssFile()) {
                Db::rollback();
                return self::ajaxError('删除失败');
            }
        }
        Db::commit();
        return self::ajaxOk('删除成功');
    }

    /**
     * @return false|string
     * Description 新建数据库备份
     */
    public function createBackup()
    {
        $d = [
            'function' => 'backup',
            'user_id' => Session::get('admin')['id'],
            'user_type' => 2
        ];
        task('backup', $d);
        return self::ajaxOk('已添加到队列，稍后刷新查看执行结果');
    }

    //回滚数据库数据
    public function rollBack()
    {

    }

    //网站维护
    public function preserve()
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

    /**
     * @return false|string
     * Description 获取消息队列列表数据
     */
    public function getQueueList()
    {
        $where = [];
        $Queue_List = Queue::getList($where, ' * ', 'id DESC');
        return self::ajaxOkdata($Queue_List, 'get data success');
    }

    //获取菜单分类树数据
    public function getMenuTreeJson()
    {
        //获取菜单分类列表数据
        $Menu_Type = MenuType::getAll([], ' id,name ', 100, ' id asc ');
        return self::ajaxReturn($Menu_Type);
    }

    /**
     * @return false|string
     * Description 获取菜单列表
     */
    public function getMenuList()
    {
        //验证数据
        $class = input('class');
        if (!isset($class) || !is_numeric($class)) {
            return self::ajaxError('非法访问');
        }
        //构建查询条件
        $where = ['class' => $class];
        $Menu_List = Menu::getList($where, 'id,name,ico,create_time,alter_time,url,parent_id', 'id desc');
        return self::ajaxOkdata($Menu_List, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 添加菜单分类
     */
    public function addMenuClass()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $result = $this->validate(input(), ['name' => 'require|max:20'], ['name.require' => '请输入分类名称', 'name.max' => '分类名称不能超过20个字符']);
            if (true !== $result) {
                return self::ajaxError($result);
            }
            //组合数据添加到数据库
            $arr = [
                'name' => input('name'),
                'create_time' => time()
            ];
            $result = MenuType::add($arr);
            if ($result) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            return View('Sysconfig_add_menu_class');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 添加菜单方法
     */
    public function addMenu()
    {
        $parent_id = input('parent_id');
        if (isset($parent_id) && !is_numeric($parent_id)) {
            return self::ajaxError('非法访问');
        } else if (!empty($parent_id) && is_numeric($parent_id)) {
            $parent_id = $parent_id;
        } else {
            $parent_id = 0;
        }
        if (Request::instance()->isPost()) {
            $data = $this->checkMenuForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['create_time'] = time();
            $res = Menu::add($data);
            if ($res) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            //获取菜单分类列表分配到页面
            $Menu_Class_List = MenuType::getAll([], ' id,name ', 100, ' id asc ');
            View::share('class_list', $Menu_Class_List);
            View::share('parent_id', $parent_id);
            //获取模块列表
            $Model_List = RbacModel::getAll([], ' id,name ', 1000, ' id asc ');
            View::share('model_list', $Model_List);
            return View('Sysconfig_add_menu');
        }
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改菜单信息方法
     */
    public function alterMenuInfo()
    {
        //验证数据
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        //构造查询条件
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            $data = $this->checkMenuForm();
            if (is_string($data)) {
                return self::ajaxError($data);
            }
            $data['alter_time'] = time();
            $res = Menu::edit($where, $data);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            //获取菜单分类列表分配到页面
            $menu_class_list = MenuType::getAll([], ' id,name ', 100, ' id asc ');
            View::share('class_list', $menu_class_list);
            //查询菜单详细信息
            $menu_info = Menu::getOne($where);
            View::share('info', $menu_info);
            //获取模块列表
            $Model_List = RbacModel::getAll([], ' id,name ', 1000, ' id asc ');
            View::share('model_list', $Model_List);
            return View('Sysconfig_alter_menu');
        }
    }

    /**
     * @return array|string|true
     * Desciption 验证菜单表单
     */
    protected function checkMenuForm()
    {
        $rule = [
            'name' => 'require|max:15',
            'ico' => 'require|max:100',
            'url' => 'max:50',
            'model_id' => 'require|number',
            'class' => 'require|number',
            'parent_id' => 'number'
        ];
        $msg = [
            'name.require' => '请输入菜单名称',
            'name.max' => '输入的菜单名称不能超过15个字符',
            'ico.require' => '请上传菜单图标',
            'ico.max' => '菜单图标的链接地址不能超过100个字符',
            'url.max' => '输入跳转链接不能超过50个字符',
            'model_id.require' => '请选择模块',
            'model_id.number' => '模块id必须为数字',
            'class.require' => '请选择菜单分类',
            'class.number' => '菜单分类必须是数字',
            'parent.number' => '请选择父级菜单'
        ];
        return $this->checkForm($rule, $msg, function ($input) {
            $data = [
                'class' => $input['class'],
                'name' => $input['name'],
                'ico' => $input['ico'],
                'url' => $input['url'],
                'model_id' => $input['model_id'],
                'parent_id' => $input['parent_id']
            ];
            return $data;
        });
    }

    /**
     * @return false|string
     * Description 获取某个分类下的父级菜单
     */
    public function getParentMenu()
    {
        //验证数据
        $class = input('class');
        if (empty($class) || !is_numeric($class)) {
            return self::ajaxError('非法访问');
        }
        //构建查询条件
        $where = ['class' => $class, 'parent_id' => 0];
        $Menu_List = Menu::getAll($where, ' id,name ', 100, ' id asc ');
        return self::ajaxOkdata($Menu_List, 'get data success');
    }

    /**
     * @return false|string
     * Description 删除菜单方法
     */
    public function delMenu()
    {
        //验证数据
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        //组合条件
        $where = ['id' => $id];
        $res = Menu::del($where);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * Description 上传菜单图标方法
     */
    public function uploadIce()
    {
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        File::setArticleInfo(-1, "it's menu ico");
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $url = File::$url;
            return $this->ajaxOkdata([
                'url' => $url,
            ], '上传成功');
        } else {
            return $this->ajaxErrordata([]);
        }
    }

}

