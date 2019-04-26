<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/24
 * Time: 12:55
 * Description: 小程序管理
 */


namespace app\admin\controller;


use SucaiZ\File;
use think\Db;
use think\Model;
use think\Request;
use think\Session;
use think\View;

class Miniapp extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    //小程序管理页面
    public function manage()
    {
        return View('show_manage');
    }

    //同步数据
    private function syncDate($type, $column_id = 0, $delta = false)
    {
        if ($type == 'column') {
            $column = new \app\admin\model\Column();
            $miniapp = new \app\admin\model\Miniapp();
            $column_list = $column->getColumnList(['parent_id' => $column_id], ' id,type_name ', 1000);
            //清空原有数据
            $miniapp->delColumnInfo(['parent_id' => $column_id]);
            $arr = [];
            //循环处理数据
            foreach ($column_list as $key => $value) {
                $arr[] = [
                    'parent_id' => $column_id,
                    'column_id' => $value['id'],
                    'type_name' => $value['type_name'],
                    't_status' => 1,
                    'create_time' => time()
                ];
            }
            return $miniapp->addMoreColumnInfo($arr);
        } else if ($type == 'tag') {
            //获取子栏目列表
            $column_list = Model('column')->getAll([], 'id,parent_id', 1000);
            $column_son_list = self::getSonList($column_id, $column_list);
            array_push($column_son_list, $column_id);
            //循环栏目tag列表数据,判断是否已经同步过数据,只同步未同步过的数据
            $tag_list = Model('tag')->getAll(['column_id' => ['in', $column_son_list]], 'id,tag_name,column_id');
            Db::startTrans();
            foreach ($tag_list as $value) {
                $count = Model('MobileTag')->getCount(['tag_id' => $value['id']]);
                if (empty($count)) {
                    $res = Model('MobileTag')->add([
                        'tag_id' => $value['id'],
                        'name' => $value['tag_name'],
                        'litpic' => '',
                        'create_time' => time(),
                        'column_id' => $value['column_id'],
                        'status' => 2
                    ]);
                    if (!$res) {
                        Db::rollback();
                        return false;
                    }
                }
            }
            Db::commit();
            return true;
        }
    }

    //同步分类数据
    public function syncColumn()
    {
        //验证数据
        $column = input('column');
        if (!isset($column) || empty($column) || !is_numeric($column)) {
            echo '非法访问';
            die;
        }
        //调用同步数据方法
        $res = $this->syncDate('column', $column);
        if ($res) {
            $a = [
                'errorcode' => 0,
                'msg' => '同步数据成功'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 1,
                'msg' => '同步数据失败'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * @return false|string
     * Description 同步分类tag数据
     */
    public function syncTag()
    {
        $column = input('column');
        if (empty($column) || !is_numeric($column)) {
            return self::ajaxError('同步失败');
        }
        $res = $this->syncDate('tag', $column);
        if ($res) {
            return self::ajaxOk('同步成功');
        } else {
            return self::ajaxError('同步失败');
        }
    }

    //获取小程序栏目列表数据
    public function getMiniappColumnList()
    {
        //验证数据
        $column = input('column');
        if (!isset($column) || empty($column) || !is_numeric($column)) {
            echo '非法访问';
            die;
        }
        //获取列表数据
        $miniapp = new \app\admin\model\Miniapp();
        $column_list = $miniapp->getColumnList(['parent_id' => $column]);
        $column_count = $miniapp->getColumnSum(['parent_id' => $column]);
        //循环处理列表数据
        foreach ($column_list as $key => $value) {
            $column_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if ($value['t_status'] == 1) {
                $a = '推荐';
            } else {
                $a = '不推荐';
            }
            $column_list[$key]['t_status'] = $a;
        }
        //组合返回数据
        $arr = [
            'data' => $column_list,
            'count' => $column_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //修改小程序栏目推荐状态
    public function alterRecommendStatus()
    {
        //验证数据
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        //组合查询条件
        $where = ['id' => $id];
        $miniapp = new \app\admin\model\Miniapp();
        //查询原有状态
        $column_info = $miniapp->getColumnInfo($where, 't_status');
        //组合更新后数据
        if ($column_info['t_status'] == 1) {
            $arr['t_status'] = 2;
        } else {
            $arr['t_status'] = 1;
        }
        //更新栏目数据
        if ($miniapp->alterColumnInfo($where, $arr)) {
            $a = [
                'errorcode' => 0,
                'msg' => '修改成功'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 1,
                'msg' => '修改失败'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }

    }

    //删除小程序栏目方法
    public function delColumn()
    {
        //验证数据
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        //组合条件
        $where = ['id' => $id];
        $miniapp = new \app\admin\model\Miniapp();
        if ($miniapp->delColumnInfo($where)) {
            $a = [
                'errorcode' => 0,
                '删除成功'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 1,
                '删除失败'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //获取手机栏目树形数据
    public function getColumnTreeJson()
    {
        $column = new \app\admin\model\Column();
        $column_list = $column->getColumnList(['parent_id' => 54], ' id,type_name ');
        //循环处理数据
        $arr = [];
        foreach ($column_list as $key => $value) {
            $a = [
                'id' => $value['id'],
                'name' => $value['type_name'],
                'parent_id' => 0,
                'children' => []
            ];
            $arr[] = $a;
        }
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //查询手机栏目tag列表数据
    public function getTagList()
    {
        //验证数据
        $column = input('column');
        if (!isset($column) || empty($column) || !is_numeric($column)) {
            echo '非法访问';
            die;
        }
        //组合查询条件
        $where = ['column_id' => $column];
        $miniapp = new \app\admin\model\Miniapp();
        $tag_list = $miniapp->getTagList($where, ' * ', 1000, 'status asc,id asc');
        $tag_count = $miniapp->getTagCount($where);
        $column = new \app\admin\model\Column();
        //循环处理列表数据
        foreach ($tag_list as $key => $value) {
            $tag_list[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            if ($value['alter_time'] != 0) {
                $tag_list[$key]['alter_time'] = date('Y-m-d H:i:s', $value['alter_time']);
            }
            if ($value['status'] == 2) {
                $a = '禁用';
            } else {
                $a = '启用';
            }
            $tag_list[$key]['status'] = $a;
            $column_info = $column->getColumnInfo(['id' => $value['column_id']], ' type_name ');
            $tag_list[$key]['column'] = $column_info['type_name'];
        }
        //组合返回数据
        $arr = [
            'data' => $tag_list,
            'count' => $tag_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //修改手机栏目tag状态
    public function alterTagStatus()
    {
        //验证数据
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        //构造查询条件
        $where = ['id' => $id];
        //查询原有状态
        $miniapp = new \app\admin\model\Miniapp();
        $tag_info = $miniapp->getTagInfo($where, ' status ');
        if ($tag_info['status'] == 2) {
            $arr['status'] = 1;
        } else {
            $arr['status'] = 2;
        }
        $arr['alter_time'] = time();
        $res = $miniapp->updateTagInfo($where, $arr);
        if ($res) {
            $a = [
                'errorcode' => 0,
                'msg' => '修改成功'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a = [
                'errorcode' => 1,
                'msg' => '修改失败'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }

    //显示手机栏目tag详细信息
    public function showTagInfo()
    {
        //验证数据
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        //构造查询条件
        $where = ['id' => $id];
        $miniapp = new \app\admin\model\Miniapp();
        $column = new \app\admin\model\Column();
        $tag = new \app\admin\model\Tag();
        //查询信息
        $tag_info = $miniapp->getTagInfo($where);
        $column_info = $column->getColumnInfo(['id' => $tag_info['column_id']], ' type_name ');
        $tag_info['column'] = $column_info['type_name'];
        $tag_info['info'] = $tag->getTagInfo(['id' => $tag_info['tag_id']], ' * ');
        $tag_info['create_time'] = date('Y-m-d H:i:s', $tag_info['create_time']);
        if ($tag_info['status'] == 1) {
            $tag_info['status'] = '启用';
        } else {
            $tag_info['status'] = '禁用';
        }
        View::share('info', $tag_info);
        return View('miniapp_show_tag_info');
    }

    //修改上级栏目tag封面图片方法
    public function alterTagLitpic()
    {
        //验证数据
        $id = input('id');
        if (!isset($id) || empty($id) || !is_numeric($id)) {
            echo '非法访问';
            die;
        }
        $miniapp = new \app\admin\model\Miniapp();
        if (Request::instance()->isPost()) {
            //设置用户信息
            File::setUserInfo(2, Session::get('admin')['id']);
            File::setArticleInfo(-1, "this's mobile tag cover img");
            if (File::uploadFile($_FILES['file'], '', '', true)) {
                //更新数据库内数据
                if ($miniapp->updateTagInfo(['id' => $id], ['litpic' => File::$url])) {
                    $a['url'] = File::$url;
                    $a['id'] = File::$upload_id;
                    $a['errorcode'] = 0;
                    $a['msg'] = '上传成功';
                    return json_encode($a, JSON_UNESCAPED_UNICODE);
                }
            }
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            //查询详细信息
            $tag_info = $miniapp->getTagInfo(['id' => $id], ' litpic ');
            View::share('litpic', $tag_info['litpic']);
            View::share('id', $id);
            return View('miniapp_alter_tag_litpic');
        }
    }

    //查看tag相关文档
    public function getTagArticle()
    {
        //验证数据
        $id = input('id');
        if (empty($id) || !is_numeric($id)) {
            return self::ajaxError('参数错误');
        }
        if (Request::instance()->isPost()) {
            $tag_id = Model('MobileTag')->getField(['id' => $id], 'tag_id');
            $article_list = Model('TagList')->getList(['tag_id' => $tag_id], 'article_id');
            $article_list = array_column($article_list['data'], 'article_id');
            //计算文档总数
            $count = Model('TagList')->getCount(['tag_id' => $tag_id], 'id');
            $arr = [];
            //循环文档列表
            foreach ($article_list as $key => $value) {
                $article_info = Model('Article')->getOne(['id' => $value, 'is_delete' => 1,'is_audit'=>1], 'id,title,create_time,click,column_id');
                if(empty($article_info)){
                    break;
                }
                $article_info['create_time'] = date('Y-m-d H:i:s', $article_info['create_time']);
                $column_name = Model('Column')->getField(['id' => $article_info['column_id']], 'type_name');
                $article_info['column'] = $column_name;
                $arr[] = $article_info;
            }
            //组合返回的数据
            $arr = [
                'data' => $arr,
                'count' => $count,
                'code' => 0
            ];
            return json_encode($arr, JSON_UNESCAPED_UNICODE);
        } else {
            View::share('id', $id);
            return View('miniapp_show_tag_article');
        }
    }
}