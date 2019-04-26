<?php

namespace app\admin\controller;

use app\admin\model\Click;
use app\admin\model\Menu;
use think\Model;
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
            $user = model('user');
            $arr = [
                'user_name' => input('username'),
                'real_name' => input('realname'),
                'nick_name' => input('nickname')
            ];
            $admin_id = Session::get('admin')['id'];
            if ($user->editUserInfo(['id' => $admin_id], $arr)) {
                $a['errorcode'] = 0;
                $a['msg'] = "修改成功";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a['errorcode'] = 1;
                $a['msg'] = "修改失败";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        } else {
            //根据用户id获取用户信息
            $admin_id = Session::get('admin')['id'];
            $admin_info = model('user')->getUserInfoOne(['id' => $admin_id]);
            $this->assign('admin_info', $admin_info);
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
        $user = model('user');
        if ($user->editUserInfo(['id' => $admin_id], $arr)) {
            $a['errorcode'] = 0;
            $a['msg'] = "修改成功";
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = "修改失败";
            return json_encode($a, JSON_UNESCAPED_UNICODE);
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
                $a['errorcode'] = 1;
                $a['msg'] = "文件类型不在允许的范围内";
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
            $admin_face_img_type = str_replace('image/', '', $_FILES['file']['type']);
            $admin_face_img_newfile = $admin_face_img_path . getNewFileName() . '.' . $admin_face_img_type;
            $admin_face_img_url = ltrim($admin_face_img_newfile, '.');
            if (move_uploaded_file($_FILES['file']['tmp_name'], $admin_face_img_newfile)) {
                $admin_id = Session::get('admin')['id'];
                $user = model('user');
                if ($user->editUserInfo(['id' => $admin_id], ['face' => $admin_face_img_url])) {
                    $a['errorcode'] = 0;
                    $a['msg'] = "上传成功";
                    $a['face_url'] = $admin_face_img_url;
                    return json($a);
                }
            }
            $a['errorcode'] = 1;
            $a['msg'] = "上传失败";
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = "用户头像上传失败";
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //获取菜单数据方法
    public function getMenuInfo()
    {
        //验证数据
        $class = input('class');
        if (!isset($class) || empty($class) || !is_numeric($class)) {
            echo '非法访问';
            die;
        }
        //组合查询条件
        $where = ['class' => $class];
        //获取菜单列表
        $menu = new Menu();
        $menu_list = $menu->getMenuList($where, ' id,ico,name,url,parent_id ', 1000, ' id asc ');
        $data = [];
        foreach ($menu_list as $key => $value) {
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
            foreach ($menu_list as $k => $v) {
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
        return json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
    }

    //后台欢迎页面
    public function welcome()
    {
        //获取文档总数
        $article = new \app\admin\model\Article();
        $article_count = $article->getArticleCount(['is_delete' => 1]);
        $info['article_count'] = $article_count;
        //获取会员总数
        $member = new \app\admin\model\Member();
        $info['member_count'] = $member->getMemberCount([]);
        //获取附件总数
        $upload = new \app\admin\model\Upload();
        $info['upload_count'] = $upload->getUploadCount([]);
        //获取评论总数
        $comment = new \app\admin\model\Comment();
        $info['comment_count'] = $comment->getCommentCount([]);
        //获取未执行队列列表
        $queue = new \app\admin\model\Queue();
        $info['queue_count'] = $queue->getQueueCount(['status' => 1]);
        //获取附总共大小
        $info['upload_size'] = round($upload->getUploadSize(["oss_bucket"=>['neq','']]) / (1024 * 1024 * 1024), 2);
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
            $article_sum[] = '"' . $article->getArticleCount($where) . '"';
            unset($where);
            $where = [
                "create_time" => ['<', $end],
                "oss_bucket"=>['neq','']
            ];
            $upload_sum[] = round($upload->getUploadSize($where) / (1024 * 1024 * 1024), 2);
            unset($where);
        }
        $info['article_week'] = implode(',', $week);
        $info['article_sum'] = implode(',', $article_sum);
        $info['upload_sum'] = implode(',', $upload_sum);
        //获取最新发布的文档
        $article_list = $article->getArticleList(['is_delete' => 1], ' id,title,create_time ', 10, ' id desc ');
        //循环处理列表数据
        foreach ($article_list as $key => $value) {
            $article_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        $info['article_list'] = $article_list;
        //获取最新发表的评论
        $comment = new \app\admin\model\Comment();
        $comment_list = $comment->getCommentList([], ' * ', 10, ' c.id desc ');
        foreach ($comment_list as $key => $value) {
            $comment_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $comment_list[$key]['content'] = cut_str($value['content'], 80);
        }
        $info['comment_list'] = $comment_list;
        //获取前一个月日期

        View::share('info', $info);
        return View('welcome');
    }

    //获取点击数据
    public function getclick()
    {
        $click_list = Model('click')->getAll([], '*', 30);
        $click_list_click_arr = array_column($click_list, 'click');
        $click_list_day_arr = array_column($click_list, 'day');
        $click_info = [
            'click' => $click_list_click_arr,
            'day' => $click_list_day_arr
        ];
        return $this->ajaxOkdata($click_info);
    }

    //获取上传文件个数数据
    public function getUpload()
    {
        $mnuoth = [];
        $upload_monuth_sum = [];
        for ($i = 1; $i <= 30; $i++) {
            $mnuoth[] = date('Y-m-d', strtotime(date('Y-m-d')) - ($i * 86400));
            $start = strtotime(date('Y-m-d H:i:s', strtotime("-$i day")));
            $end = $start + 86400;
            //组合查询条件
            $where = " create_time > $start and create_time < $end  ";
            $upload_monuth_sum[] = Model('upload')->getCount($where);

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
        $select = Model('ArticleHot')->getAll(['type' => 1], 'id,time', 1000, 'time desc');
        $data['select'] = $select;
        //获取年份点击分布
        $data['year'] = Model('ArticleHot')->getAll(['type' => 1], 'time as name,click as value');
        if (empty($type) || !is_numeric($type)) {
            $type = Model('ArticleHot')->getField([], 'id', 'time desc');
        }
        $data['month'] = Model('ArticleHot')->getAll(['parent_id' => $type], 'time as name,click as value');
        return self::ajaxOkdata($data, '', 'get data success');
    }
}
