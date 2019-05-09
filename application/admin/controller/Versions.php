<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/14 0014
 * Time: 14:45
 * Description: 版本管理
 */

namespace app\admin\controller;

use app\model\Versions as Versions_Model;
use app\validate\Version as Version_Validate;
use think\Request;
use think\View;


class Versions extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * Description 获取版本列表数据
     */
    public function getVersions()
    {
        $list = Versions_Model::getList([], '*', 'id desc');
        return $this->ajaxOkdata($list);
    }

    /**
     * @return \think\response\View
     * Description 修改版本信息
     */
    public function edit()
    {
        $id = input('id');
        if (!$id) {
            return $this->ajaxError('非法访问');
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            $validate = new Version_Validate();
            if (!$validate->check(input())) {
                return self::ajaxError($validate->getError());
            }
            $data = $validate->getData('', function ($data, $input) {
                if (isset($input['is_height'])) {
                    $data['is_height'] = 2;
                } else {
                    $data['is_height'] = 1;
                }
                return $data;
            });
            $res = Versions_Model::edit($where, $data);
            if ($res) {
                return $this->ajaxOk('修改成功');
            } else {
                return $this->ajaxError('修改失败');
            }
        } else {
            $version_info = Versions_Model::getOne($where, '*');
            View::share('version_info', $version_info);
            return View('');
        }
    }

    /**
     * @return false|string
     * Description 添加版本信息
     */
    public function add()
    {
        $validate = new Version_Validate();
        if (!$validate->check(input())) {
            return self::ajaxError($validate->getError());
        }
        $data = $validate->getData('', function ($data, $input) {
            if (isset($input['is_height'])) {
                $data['is_height'] = 2;
            } else {
                $data['is_height'] = 1;
            }
            $data['create_time'] = time();
            return $data;
        });
        $res = Versions_Model::add($data);
        if ($res) {
            return $this->ajaxOk('添加成功');
        } else {
            return $this->ajaxError('添加失败');
        }
    }

    /**
     * @return false|string
     * Description 删除版本信息
     */
    public function del()
    {
        $id = input('id');
        if (!$id) {
            return $this->ajaxError('参数错误');
        }
        $res = Versions_Model::del(['id' => $id]);
        if ($res) {
            return $this->ajaxOk('删除成功');
        } else {
            return $this->ajaxError('删除失败');
        }
    }
}