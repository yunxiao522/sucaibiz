<?php

namespace app\admin\controller;

use app\model\ArticleHot;
use app\model\Click;
use app\model\AdminUser;
use app\model\Menu;
use app\model\Article;
use app\model\User;
use app\model\Upload;
use app\model\Comment;
use app\model\Queue;
use think\Request;
use think\Session;
use think\View;

class Index extends Common
{
    public function index()
    {
        return View();
    }

    public function account()
    {
        if (Request::instance()->isPost()) {
            //验证数据
            if (input('username') == '') {
                $a['errorcode'] = 1;
                $a['msg'] = "输入的用户名不能为空";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (input('nickname') == '') {
                $a['errorcode'] = 1;
                $a['msg'] = "输入的昵称不能为空";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (strlen(input('username')) > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = "输入的用户名长度不能超过20个字符";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (strlen(input('nickname')) > 20) {
                $a['errorcode'] = 1;
                $a['msg'] = "输入的昵称不能超过20个字符";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            if (strlen(input('realname')) > 10) {
                $a['errorcode'] = 1;
                $a['msg'] = "输入的真实姓名不能超过10个字符";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $arr = [
                'user_name' => input('username'),
                'real_name' => input('realname'),
                'nick_name' => input('nickname')
            ];
            $admin_id = Session::get('admin')['id'];
            $res = AdminUser::edit(['id' => $admin_id], $arr);
            if ($res) {
                return self::ajaxOk('修改成功');
            } else {
                return self::ajaxError('修改失败');
            }
        } else {
            //根据用户id获取用户信息
            $admin_id = Session::get('admin')['id'];
            $Admin_Info = AdminUser::getOne(['id' => $admin_id]);
            $this->assign('admin_info', $Admin_Info);
            return View();
        }
    }

    //修改用户密码方法
    public function editpassword()
    {
        //验证数据
        if (input('password') == '') {
            $a['errorcode'] = 1;
            $a['msg'] = "输入的密码不能为空";
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        if (input('verify_password') == '') {
            $a['errorcode'] = 1;
            $a['msg'] = "输入的验证码不能为空";
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        if (input('password') != input('verify_password')) {
            $a['errorcode'] = 1;
            $a['msg'] = "两次输入的密码不相同";
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
        $admin_id = Session::get('admin')['id'];
        $arr = [
            'user_password' => getAdminPassword(input('password'))
        ];
        $res = AdminUser::edit(['id' => $admin_id], $arr);
        if ($res) {
            return self::ajaxOk('修改成功');
        } else {
            return self::ajaxError('修改失败');
        }
    }

    //上传用户头像方法
    public function uploadface()
    {
        if ($_FILES['file']['error'] == 0) {
            $admin_face_img_path = './upload/face/';
            if (!file_exists($admin_face_img_path)) {
                mkdir($admin_face_img_path, 0777, true);
            }
            //允许上传头像文件的mime type类型数组
            $img_type = [
                'image/gif',
                'image/jpeg',
                'image/png'
            ];
            if (!in_array(strtolower($_FILES['file']['type']), $img_type)) {
                return self::ajaxError('文件类型不在允许范围内');
            }
            $admin_face_img_type = str_replace('image/', '', $_FILES['file']['type']);
            $admin_face_img_newfile = $admin_face_img_path . getNewFileName() . '.' . $admin_face_img_type;
            $admin_face_img_url = ltrim($admin_face_img_newfile, '.');
            if (move_uploaded_file($_FILES['file']['tmp_name'], $admin_face_img_newfile)) {
                $admin_id = Session::get('admin')['id'];
                $res = AdminUser::edit(['id' => $admin_id], ['face' => $admin_face_img_url]);
                if ($res) {
                    $a['errorcode'] = 0;
                    $a['msg'] = "上传成功";
                    $a['face_url'] = $admin_face_img_url;
                    return json($a);
                }
            }

        }
        return self::ajaxError('上传失败');
    }

    /**
     * @return false|string
     * Description 获取菜单数据方法
     */
    public function getMenuInfo()
    {
        //验证数据
        $class = input('class');
        if (!isset($class) || empty($class) || !is_numeric($class)) {
            return self::ajaxError('非法访问');
        }
        //组合查询条件
        $where = ['class' => $class];
        //获取菜单列表
        $Menu_List = Menu::getAll($where, ' id,ico,name,url,parent_id ', 1000, ' id asc ');
        $data = [];
        //查询一级菜单
        foreach ($Menu_List as $key => $value) {
            if ($value['parent_id'] == 0) {
                $data[] = [
                    'text' => $value['name'],
                    'icon' => $value['ico'],
                    'href' => $value['url'],
                    'id' => $value['id']
                ];
            }
        }
        //查询二级菜单
        foreach ($data as $key => $value) {
            $subset = [];
            foreach ($Menu_List as $k => $v) {
                if ($value['id'] == $v['parent_id']) {
                    $subset[] = [
                        'text' => $v['name'],
                        'icon' => $v['ico'],
                        'href' => $v['url']
                    ];
                }
            }
            unset($data[$key]['id']);
            if (!empty($subset)) {
                $data[$key]['subset'] = $subset;
                unset($data[$key]['href']);
            }
            unset($subset);
        }
        return self::ajaxOkdata($data, 'get data success');
    }

    //后台欢迎页面
    public function welcome()
    {
        //获取文档总数
        $Article_Count = Article::getCount(['is_delete' => 1]);
        $info['article_count'] = $Article_Count;
        //获取会员总数
        $info['member_count'] = User::getCount([]);
        //获取附件总数
        $info['upload_count'] = Upload::getCount([]);
        //获取评论总数
        $info['comment_count'] = Comment::getCount([]);
        //获取未执行队列列表
        $info['queue_count'] = Queue::getCount(['status' => 1]);
        //获取附总共大小
        $info['upload_size'] = round(Upload::getSum(["oss_bucket" => ['neq', '']], 'filesize') / (1024 * 1024 * 1024), 2);
        //获取前七天发布文档数据
        $week = [];
        $article_sum = [];
        $upload_sum = [];
        $day = date('Y-m-d', strtotime("+1 day"));
        for ($i = 1; $i <= 7; $i++) {
            $week[] = '"' . date('m-d', strtotime("$day -$i day")) . '"';
            $start = strtotime(date('Y-m-d H:i:s', strtotime("$day -$i day")));
            $end = $start + 86400;
            //组合查询条件
            $where = [
                'create_time' => [[
                    'GT',
                    $start
                ], [
                    'LT',
                    $end
                ], 'AND'],
                'is_delete' => 1,
                'is_audit' => 1,
                'draft' => 2
            ];
            $article_sum[] = '"' . Article::getCount($where) . '"';
            unset($where);
            $where = [
                "create_time" => ['<', $end],
                "oss_bucket" => ['neq', '']
            ];
            $upload_sum[] = round(Upload::getSum($where, 'filesize') / (1024 * 1024 * 1024), 2);
            unset($where);
        }
        $info['article_week'] = implode(',', $week);
        $info['article_sum'] = implode(',', $article_sum);
        $info['upload_sum'] = implode(',', $upload_sum);
        //获取最新发布的文档
        $info['article_list'] = Article::getAll(['is_delete' => 1], ' id,title,create_time ', 10, ' id desc ');
        //获取最新发表的评论
        $Comment_List = Comment::getAll([], ' * ', 10, ' id desc ');
        foreach ($Comment_List as $key => $value) {
            $Comment_List[$key]['content'] = cut_str($value['content'], 80);
            $Comment_List[$key]['nickname'] = User::getField(['id' => $value['uid']], 'nickname');
        }
        $info['comment_list'] = $Comment_List;
        //获取前一个月日期
        View::share('info', $info);
        return View('welcome');
    }

    /**
     * @return false|string
     * Description 获取点击数据
     */
    public function getclick()
    {
        $Click_List = Click::getAll([], '*', 30);
        $Click_List_Click_Arr = array_column($Click_List, 'click');
        $Click_List_Day_Arr = array_column($Click_List, 'day');
        $click_info = [
            'click' => $Click_List_Click_Arr,
            'day' => $Click_List_Day_Arr
        ];
        return $this->ajaxOkdata($click_info, 'get data success');
    }

    //获取上传文件个数数据
    public function getUpload()
    {
        $mnuoth = [];
        $upload_monuth_sum = [];
        for ($i = 1; $i <= 30; $i++) {
            $mnuoth[] = date('Y-m-d', strtotime(date('Y-m-d')) - ($i * 86400));
//            $start = strtotime(date('Y-m-d H:i:s', strtotime("-$i day")));
            $start = strtotime(date('Y-m-d' ,strtotime("+1 day"))) - $i * 86400;
            $end = $start + 86400;
            //组合查询条件
            $where = " create_time > $start and create_time < $end  ";
            $upload_monuth_sum[] = Upload::getCount($where);
        }
        $data = [
            'mounth' => $mnuoth,
            'mounth_sum' => $upload_monuth_sum
        ];
        return $this->ajaxOkdata($data);
    }

    /**
     * @return false|string
     * Description 获取点击分布数据
     */
    public function clickDistribute()
    {
        $type = input('type');
        //获取年份数据
        $select = ArticleHot::getAll(['type' => 1], 'id,time', 1000, 'time desc');
        $data['select'] = $select;
        //获取年份点击分布
        $data['year'] = ArticleHot::getAll(['type' => 1], 'time as name,click as value');
        if (empty($type) || !is_numeric($type)) {
            $type = ArticleHot::getField([], 'id', 'time desc');
        }
        $data['month'] = ArticleHot::getAll(['parent_id' => $type], 'time as name,click as value');
        return self::ajaxOkdata($data, '', 'get data success');
    }
}
