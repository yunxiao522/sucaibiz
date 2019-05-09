<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/6
 * Time: 21:56
 * Description: 日志管理
 */

namespace app\admin\controller;

use app\model\LogLogin;
use app\model\LogOperate;
use app\model\LogVisit;
use app\model\User;
use app\model\Column;
use app\model\Article;

class Log extends Common
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取会员登录日志列表数据
     */
    public function getLogLogin()
    {
        $where = ['type' => 1];
        $list = LogLogin::getList($where, '*', 'id desc');
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['nickname'] = User::getField(['id' => $value['uid']], 'nickname', 'id desc', true);
        }
        return self::ajaxOkdata($list, 'get data success');
    }

    /**
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取操作日志数据
     */
    public function getOperate()
    {
        $class = input('class');
        if (!isset($class)) {
            return self::ajaxError('非法访问');
        }
        $where = ['class' => $class];
        $list = LogOperate::getList($where, '*', 'id desc');
        foreach ($list['data'] as $k => $v){
            $list['data'][$k]['nickname'] = User::getField(['id'=>$v['uid']], 'nickname', 'id desc', true);
        }
        return self::ajaxOkdata($list, 'get data success');
    }

    /**
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取访问记录数据
     */
    public function getVisit()
    {
        //加入筛选条件
        $keyword = input('keyword');
        $where = [];
        if (!empty($keyword)) {
            $keyword_arr = explode(',', $keyword);
            foreach ($keyword_arr as $value) {
                $value_arr = explode(':', $value);
                switch ($value_arr[0]) {
                    case 'uid':
                        $where['user_id'] = $value_arr[1];
                        break;
                    case 'aid':
                        $where['article_id'] = $value_arr[1];
                        break;
                    case 'cid':
                        $where['column_id'] = $value_arr[1];
                        break;
                    case 'type':
                        $where['type'] = $value_arr[1];
                        break;
                    case 'device':
                        $where['device'] = $value_arr[1];
                        break;
                    case 'stime':
                        $where['create_time'][] = [
                            '<',
                            strtotime($value_arr[1])
                        ];
                        break;
                    case 'etime':
                        $where['create_time'][] = [
                            '>',
                            strtotime($value_arr[1])
                        ];
                        break;
                    case 'ip':
                        $where['ip'] = $value_arr[1];
                        break;
                }
            }
        }
        $list = LogVisit::getList($where, '*', 'id desc');
        //循环列表数据
        foreach ($list['data'] as $key => $value) {
            if (empty($value['column_id'])) {
                $list['data'][$key]['column_title'] = '无';
            } else {
                $list['data'][$key]['column_title'] = Column::getField(['id' => $value['column_id']], 'type_name', 'id desc', true);
            }
            if (empty($value['article_id'])) {
                $list['data'][$key]['article_title'] = '无';
            } else {
                $list['data'][$key]['article_title'] = Article::getField(['id' => $value['article_id']], 'title', 'id desc', true);
            }
            if ($value['user_id'] == 0) {
                $list['data'][$key]['nickname'] = '游客';
            } else {
                $list['data'][$key]['nickname'] = User::getField(['id' => $value['user_id']], 'nickname', 'id desc', true);
            }
            $list['data'][$key]['type'] = LogVisit::$visit_type[$value['type']];
            if (empty($value['device'])) {
                $list['data'][$key]['device'] = '未知设备';
            }
        }
        return $this->ajaxOkdata($list, '获取数据成功');
    }

    public function getOperateLog(){

    }
}