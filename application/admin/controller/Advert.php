<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/9
 * Time: 12:40
 * Description: 广告管理
 */

namespace app\admin\controller;

use think\Request;
use app\model\Advert as advert_model;

class Advert extends Common
{
    protected $rule = [
        'ad_name'=>'require|max:20',
        'width'=>'require|number|>:0',
        'height'=>'require|number|>:0',
        'class'=>'require|max:20',
        'palcename'=>'require|max:30',
        'content'=>'require'
    ];

    protected $msg = [
        'ad_name.require'=>'输入的广告名称不能为空',
        'ad_name.max'=>'输入的广告名称不能超过20个字符',
        'width.require'=>'输入得到广告宽度不能为空',
        'width.number'=>'输入的广告宽度必须为数字',
        'width.gt'=>'输入的广告宽度不能小于0',
        'height.require'=>'输入的广告高度不能为空',
        'height.number'=>'输入的广告高度必须为数字',
        'height.gt'=>'输入的广告高度不能小于0',
        'class.require'=>'输入的光改分组不能为空',
        'class.max'=>'输入的光改分组名称不能超过20个字符',
        'content.require'=>'输入的广告代码不能为空'
    ];
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取广告列表数据
     */
    public function getAdvertList()
    {
        $list = advert_model::getList([], ' * ', 'id desc');
        //处理列表数据
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['status'] = advert_model::$advert_status[$value['status']];
        }
        return self::ajaxOkdata($list, 'get data success');
    }

    /**
     * @return false|string|\think\response\View
     * Description 新建广告
     */
    public function add()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            $result = $this->validate(input(), $this->rule, $this->msg);
            if(true !== $result){
                return self::ajaxError($result);
            }
            $data['ad_name'] = input('ad_name');
            $data['width'] = input('width');
            $data['height'] = input('height');
            $data['class'] = input('class');
            $status = input('status');
            if (!empty($status)) {
                $data['status'] = 1;
            } else {
                $data['status'] = 2;
            }
            $data['content'] = input('content');
            $data['palcename'] = input('palcename');
            $data['create_time'] = time();
            $res = advert_model::add($data);
            if ($res) {
                return self::ajaxOk('添加成功');
            } else {
                return self::ajaxError('添加失败');
            }
        } else {
            return View('advert_add');
        }
    }

    /**
     * @return false|string|\think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 修改广告
     */
    public function alter()
    {
        $id = input('id');
        if (!isset($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        if (Request::instance()->isPost()) {
            //验证数据
            $result = $this->validate(input(),$this->rule,$this->msg);
            if(true !== $result){
                return self::ajaxError($result);
            }
            $data['ad_name'] = input('ad_name');
            $data['width'] = input('width');
            $data['height'] = input('height');
            $data['class'] = input('class');
            $status = input('status');
            if (isset($status)) {
                $data['status'] = 1;
            } else {
                $data['status'] = 2;
            }
            $data['content'] = input('content');
            $data['palcename'] = input('palcename');
            $data['alter_time'] = time();
            $result = advert_model::edit($where, $data);
            if ($result) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            $info = advert_model::getOne($where, '*');
            $this->assign('info', $info);
            return View('advert_alter');
        }
    }

    /**
     * @return false|string
     * Description 删除广告
     */
    public function del()
    {
        $id = input('id');
        if (!isset($id)) {
            return self::ajaxError('非法访问');
        }
        $where = ['id' => $id];
        $res = advert_model::del($where);
        if ($res) {
            return self::ajaxOk('删除成功');
        } else {
            return self::ajaxError('删除失败');
        }
    }
}