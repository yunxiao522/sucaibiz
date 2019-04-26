<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/6
 * Time: 21:56
 * Description: 日志管理
 */

namespace app\admin\controller;


class Log extends Common
{
    private $visit_log_type = [1=>'文档',2=>'列表',3=>'首页',4=>'其它'];
    public function __construct()
    {
        parent::__construct();
    }

    //显示登录日志
    public function showLoginLog(){
        return View('Log_show_list');
    }

    //获取会员登录日志列表数据
    public function getLogLogin(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = ['l.type'=>1];
        $log = model('Log');
        $login_log_list = $log->getLoginLog($where ,' l.*,u.username,u.nickname ' ,$limit);
        $login_log_count = $log->getLoginLogCount(['type'=>1]);
        foreach($login_log_list as $key => $value){
            $login_log_list[$key]['login_time'] = date('Y-m-d H:i:s' ,$value['login_time']);
        }
        $arr = [
            'data' => $login_log_list,
            'count' => $login_log_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //获取操作日志列表
    public function getOperate(){
        $class = input('class');
        if(!isset($class)){
            echo '非法访问';
            die;
        }
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = ['class'=>$class];
        $Log = model('Log');
        $log_list = $Log->getOperateList($where ,' o.*,u.id,u.user_name,u.nick_name ' ,$limit);
        $log_count = $Log->getOperateCount($where);
        foreach ($log_list as $key => $value) {
            $log_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        $arr = [
            'data' => $log_list,
            'count' => $log_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return \think\response\View
     * Description 浏览记录
     */
    public function showVisit(){
        return View('Log_show_visit');
    }

    /**
     * @return false|string
     * Description 获取访问记录数据
     */
    public function getVisit(){
        //加入筛选条件
        $keyword = input('keyword');
        $where = [];
        if(!empty($keyword)){
            $keyword_arr = explode(',',$keyword);
            foreach($keyword_arr as $value){
                $value_arr = explode(':',$value);
                switch ($value_arr[0]){
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
        $list = Model('Logvisit')->getList($where,'*');
        //循环列表数据
        foreach($list['data'] as $key => $value){
            if(empty($value['column_id'])){
                $list['data'][$key]['column_title'] = '无';
            }else{
                $list['data'][$key]['column_title'] = Model('column')->getField(['id'=>$value['column_id']],'type_name');
            }
            if(empty($value['article_id'])){
                $list['data'][$key]['article_title'] = '无';
            }else{
                $list['data'][$key]['article_title'] = Model('article')->getField(['id'=>$value['article_id']],'title');
            }
            if($value['user_id'] == 0){
                $list['data'][$key]['nickname'] = '游客';
            }else{
                $list['data'][$key]['nickname'] = Model('user')->getField(['id'=>$value['user_id']],'nickname');
            }
            $list['data'][$key]['type'] = $this->visit_log_type[$value['type']];
            if(empty($value['device'])){
                $list['data'][$key]['device'] = '未知设备';
            }
            $list['data'][$key]['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
        }
        return $this->ajaxOkdata($list,'获取数据成功');
    }
}