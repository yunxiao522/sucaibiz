<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/12
 * Time: 20:16
 * Description：栏目管理
 */

namespace app\admin\controller;

use app\model\ColumnType;
use app\model\Column as Column_Model;
use app\model\UserLevel;
use think\Session;
use think\View;
use think\Request;
use SucaiZ\File;

class Column extends Common
{
    //储存column模型
    private $column;
    //验证规则
    protected $rule = [
        'type_name' => 'require|max:30|min:1',
        'channel_type' => 'require',
        'sort_rank' => 'require|number',
        'like' => 'require|array',
        'keywords' => 'require|max:100',
        'type_dir' => 'require|max:100',
        'defaultname' => 'require|max:20',
        'default_index' => 'require|max:50',
        'templist' => 'require|max:60',
        'temparticle' => 'require|max:60',
        'namerule' => 'require|max:60',
        'listrule' => 'require|max:60',
        'modename' => 'require|max:60',
        'description' => 'max:200',
        'cover_url' => 'max:100'
    ];
    //验证规则信息
    protected $msg = [
        'type_name.require' => '缺少参数',
        'type_name.max' => '栏目名称不能超过30个字符',
        'type_name.min' => '栏目名称不能为空',
        'channel_type.require' => '缺少参数',
        'sort_rank.number' => '栏目顺序只能为数字',
        'sort_rank.require' => '缺少参数',
        'like.array' => '参数错误',
        'keywords.require' => '参数错误',
        'keywords.max' => '关键词不能超过100个字符',
        'type_dir.require' => '参数错误',
        'type_dir.max' => '关键词不能超过100个字符',
        'defaultname.require' => '缺少参数',
        'defaultname.max' => '首页名称不能超过20个字符',
        'default_index.require' => '缺少参数',
        'default_index.max' => '模板封面不能超过60个字符',
        'templist.require' => '缺少参数',
        'templist.max' => '列表封面不能超过60个字符',
        'temparticle.require' => '缺少参数',
        'temparticle.max' => '文章封面不能超过60个字符',
        'namerule.require' => '缺少参数',
        'namerule.max' => '文章命名规则不能超过60个字符',
        'listrule.require' => '缺少参数',
        'listrule.max' => '列表命名规范不能超过60个字符',
        'modename.require' => '缺少参数',
        'modename.max' => '模板名称不能超过60个字符',
        'description.max' => '栏目介绍不能超过200个字符',
        'cover_url.require' => '请上传栏目封面',
        'cover_url.max' => '封面地址不能超过100个字符'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \think\response\View
     * Description 显示添加栏目页面
     */
    public function addColumn()
    {
        if (Request::instance()->isGet()) {
            $parent_id = input('parent_id');
            if (empty($parent_id) || !is_numeric($parent_id)) {
                $parent_id = 0;
            }
            view::share('parent_id', $parent_id);
            //获取文档类型数据
            $Channel_Data = ColumnType::getAll(['enable' => 1], 'typename,id', 20, 'id desc');
            view::share('channeltype_data', $Channel_Data);
            //获取所属栏目最新序号
            $Sort_Num = Column_Model::getCount(['parent_id' => $parent_id], 'id');
            $this->assign('sort_num', $Sort_Num);
            //获取用户级别数据
            $User_Level_List = UserLevel::getAll([], ' id,level_name ');
            view::share('member_level', $User_Level_List);
            //获取所有栏目数据
            $Column_List = Column_Model::getAll([],'id,type_name');
            view::share('column_list', $Column_List);
            return View();
        } else {
            $result = $this->validate(input(), $this->rule, $this->msg);
            if (true !== $result) {
                return self::ajaxError($result);
            }
            //验证数据
            $parent_id = input('parent_id');
            if (!empty($parent_id) && !is_numeric($parent_id)) {
                return self::ajaxError('输入的父级栏目参数有误');
            }
            $data = [];
            $data['type_name'] = input('type_name');
            $data['channel_type'] = input('channel_type');
            $data['parent_id'] = empty($parent_id) ? 0 : $parent_id;
            $data['sort_rank'] = input('sort_rank');
            $data['type_dir'] = input('type_dir');
            $data['defaultname'] = input('defaultname');
            $data['default_index'] = input('default_index');
            $data['templist'] = input('templist');
            $data['temparticle'] = input('temparticle');
            $data['namerule'] = input('namerule');
            $data['listrule'] = input('listrule');
            $data['modename'] = input('modename');
            $data['description'] = input('description');
            $data['cover_img'] = input('cover_url');
            $data['create_time'] = time();
            $issend = input('issend');
            $data['issend'] = empty($issend) ? 2 : 1;
            $like = input()['like'];
            if (!empty($like) && is_array($like)) {
                $corank = implode(',', array_keys($like));
            } else {
                $corank = '';
            }
            $data['corank'] = $corank;
            $data['keywords'] = input('keywords');
            $res = Column_Model::add($data);
            if ($res) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        }
    }

    /**
     * @return false|string
     * Description 获取栏目列表方法
     */
    public function getColumnListTojson()
    {
        $parent_id = input('parent_id');
        if (isset($parent_id) && !is_numeric($parent_id)) {
            return self::ajaxError('非法访问');
        }
        if (empty($parent_id)) {
            $parent_id = 0;
        }
        if (!is_numeric(input('page')) || !is_numeric(input('limit'))) {
            return self::ajaxError('非法访问');
        }
        $where = [
            'parent_id' => $parent_id
        ];
        $Column_List = Column_Model::getList($where, 'id,sort_rank,type_name,defaultname,type_dir', 'id desc');
        foreach ($Column_List['data'] as $key => $value) {
            $Column_List['data'][$key]['index'] = $value['type_dir'] . '/' . $value['defaultname'];
        }
        return self::ajaxOkdata($Column_List, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改类目信息
     */
    public function alterColumn()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法请求');
        }
        //组合条件
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            //验证数据
            $result = $this->validate(input(), $this->rule, $this->msg);
            if (true !== $result) {
                return self::ajaxError($result);
            }
            //组合数据
            $data['parent_id'] = input('parent_id');
            $is_send = input('issend');
            $data['issend'] = empty($is_send) ? 1 : 2;
            $data['sort_rank'] = input('sort_rank');
            $data['type_name'] = input('type_name');
            $data['channel_type'] = input('channel_type');
            $data['corank'] = implode(',', array_keys(input()['like']));
            $data['type_dir'] = input('type_dir');
            $data['defaultname'] = input('defaultname');
            $data['default_index'] = input('default_index');
            $data['templist'] = input('templist');
            $data['temparticle'] = input('temparticle');
            $data['namerule'] = input('namerule');
            $data['listrule'] = input('listrule');
            $data['modename'] = input('modename');
            $data['description'] = input('description');
            $data['keywords'] = input('keywords');
            $data['cover_img'] = input('cover_url');
            $data['alter_time'] = time();
            $resulte = Column_Model::edit($where, $data);
            if ($resulte) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $column_info = Column_Model::getOne($where);
            $column_list = Column_Model::getAll([], ' type_name,id ');
            foreach ($column_list as $key => $value) {
                if ($value['id'] == $id) {
                    unset($column_list[$key]);
                }
            }
            $member_level = explode(',', $column_info['corank']);
            $channel_type_data = ColumnType::getAll(['enable' => 1], ' typename,id ');
            $member_level_list = UserLevel::getAll([], ' id,level_name ');

            View::share('column_info', $column_info);
            View::share('column_list', $column_list);
            View::share('corank', $member_level);
            View::share('channeltype_data', $channel_type_data);
            View::share('member_level', $member_level_list);
            return View();
        }
    }

    /**
     * @return false|string
     * Description 删除栏目方法
     */
    public function delcolumn()
    {
        $id = input('id');
        if (!isset($id) || !is_numeric($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $result = Column_Model::del($where);
        if ($result) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }

    /**
     * @return false|string
     * Description 修改栏目排序编号方法
     */
    public function altercolumnsortrank()
    {
        $id = input('id');
        $sort_num = input('sort_num');
        if (empty($id) || empty($sort_num) || !is_numeric($id) || !is_numeric($sort_num)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $arr = [
            'sort_rank' => $sort_num,
            'alter_time' => time()
        ];
        $result = Column_Model::edit($where, $arr);
        if ($result) {
            return self::ajaxOk('修改成功');
        } else {
            return self::ajaxError('修改失败');
        }
    }

    /**
     * @return false|string
     * Description 获取栏目列表树形数据
     */
    public function getcolumnlisttree()
    {
        $Column_List = Column_Model::getAll([], 'id,type_name as name,parent_id');
        $res = [];
        $tree = [];
        //整理数组
        foreach ($Column_List as $key => $value) {
            $res[$value['id']] = $value->toArray();
            $res[$value['id']]['children'] = [];
        }
        //查询子孙
        foreach ($res as $key => $value) {
            if ($value['parent_id'] != 0) {
                $res[$value['parent_id']]['children'][] = $value;
            }
        }
        //去除杂质
        foreach ($res as $key => $value) {
            if ($value['parent_id'] == 0) {
                $tree[] = $value;
            }
        }
        return json_encode($tree, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return \think\response\View
     * Description 显示栏目内容方法
     */
    public function getcolumninfo()
    {
        $id = input('id');
        view::share('id', $id);
        return View();
    }

    /**
     * @return false|string
     * Description 上传栏目封面方法
     */
    public function uploadColumnCoverImg()
    {
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        //设置文档信息
        File::setArticleInfo(-1, "this's column cover img");
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $data['url'] = File::$url;
            $data['id'] = File::$upload_id;
            return self::ajaxOkdata($data, '上传成功');
        } else {
            return self::ajaxError('上传失败');
        }
    }
}