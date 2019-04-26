<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/15
 * Time: 14:10
 * Description: 操作控制器
 */


namespace app\miniapp\controller;
use app\miniapp\model\Comment;
use app\miniapp\model\Down;
use app\miniapp\model\Like;
use SucaiZ\File;
use think\Collection;

class Operate extends Collection
{
    public function __construct()
    {
        parent::__construct();
    }

    //收藏操作
    public function like(){
        //验证数据
        $num = input('num');
        if(!isset($num)){
            $num = 0;
        }

        $aid = input('aid');
        if(!isset($aid)){
            echo '非法访问';die;
        }
        if(empty($aid)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的文档id不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        $uid = input('uid');
        if(!isset($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        if(empty($uid)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的用户id不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        $status = input('status');
        if(!isset($status)){
            echo '非法访问';die;
        }

        $type = input('type');
        if(empty($type) || !is_numeric($type)){
            echo '非法访问';die;
        }

        $channel = input('channel');
        if(empty($channel) || !is_numeric($channel)){
            echo '非法访问';die;
        }
        //执行操作
        $like = new Like();
        if($status){
            //组合数据添加到数据库
            $arr = [
                'article_id'=>$aid,
                'uid'=>$uid,
                'alone'=>$num,
                'create_time'=>time(),
                'type'=>$type,
                'channel'=>$channel,
                'class_id'=>2
            ];
            if($like->insertLike($arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'添加收藏成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'添加收藏失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            //删除收藏表数据
            $where = ['article_id'=>$aid,'uid'=>$uid,'alone'=>$num];
            if($like->delLike($where)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'取消收藏成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'取消收藏失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }
    }

    //获取收藏列表数据
    public function getLikeList(){
        //验证数据
        $aid = input('aid');
        if(!isset($aid) || !is_numeric($aid)){
            echo '非法访问';die;
        }
        if(empty($aid)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的文档id不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        $uid = input('uid');
        if(!isset($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        if(empty($uid)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的用户id不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
        $where = ['article_id'=>$aid ,'uid'=>$uid];
        $like = new Like();
        $list = $like->getLikeList($where ,' * ');
        //处理列表数据
        $list_arr = array_column($list ,'alone');
        $a = [
            'errorcode'=>0,
            'msg'=>'获取数据成功',
            'data'=>$list_arr
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //下载文件方法
    public function downFile(){
        //验证数据
        $file = input('file');
        if(!isset($file) || empty($file)){
            echo '非法访问';die;
        }
        if(empty($file)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的文件信息不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        $aid = input('aid');
        if(!isset($aid) || !is_numeric($aid)){
            echo '非法访问';die;
        }
        if(empty($aid)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的文档id不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        $uid = input('uid');
        if(!isset($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        if(empty($uid)){
            $a = [
                'errorcode'=>1,
                'msg'=>'输入的用户id不能为空'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }

        //判断是否已经下载过该文件
        $down = new Down();
        $num = $down->getDownCount(['uid'=>$uid,'article_id'=>$aid,'file_url'=>$file]);
        if($num == 0){
            //获取文档信息
            $article = new \app\miniapp\model\Article();
            $article_info = $article->getArticleInfo(['id'=>$aid] ,'column_id');
            //获取文件后缀名
            $ext = File::getRemoteFileExt($file);
            $size = File::getRemoteFileSize($file);
            //组合数据添加到数据库
            $arr = [
                'uid'=>$uid,
                'article_id'=>$aid,
                'file_type'=>$ext,
                'file_size'=>$size,
                'file_url'=>$file,
                'create_time'=>time(),
                'column_id'=>$article_info['column_id']
            ];
            if(!$down->insertDown($arr)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'下载文件失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }
        //返回文件信息
        $a = [
            'errorcode'=>0,
            'msg'=>'获取文件信息成功',
            'url'=>$file
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);
    }

    //下载图片信息方法
    public function downImg(){
        //验证数据
        $url = input('url');
        if(!isset($url) || empty($url)){
            echo '非法访问';die;
        }
        //获取文件后缀名
        $ext = File::getRemoteFileExt($url);
        //组合header内容
        $header_content = "Content-Type: image/$ext;text/html; charset=utf-8";
        header($header_content);
        echo file_get_contents($url);
    }
    //我的收藏
    public function myLike(){
        //验证数据
        $type = input('type');
        if(!isset($type) || empty($type) || !is_numeric($type)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';die;
        }
        $channel = input('channel');
        if(!isset($channel)  || !is_numeric($channel)){
            echo '非法访问';die;
        }
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }

        //组合查询文档列表条件
        if($type == 1){
            //获取栏子栏目列表
            $sonwhere = $this->getSonWhere($channel);
            $get_list_where = " ( $sonwhere ) " ." and type = 1 and uid = $uid ";
        }else if($type == 2){
            $get_list_where = ['type'=>2,'uid'=>$uid];
        }

        //获取文档或者评论id列表
        $like = new Like();
        $list_arr = $like->getLikeList($get_list_where ,' article_id,create_time ' ,15 ,' create_time asc ');

        //根据类型的不同，到不同的数据表中获取数据
        $article = new \app\miniapp\model\Article();
        $comment = new Comment();
        $list = [];
        if($type == 1){
            foreach($list_arr as $key => $value){
                $where = ['id'=>$value['article_id']];
                $info = $article->getArticleInfo($where ,' id,title,litpic,source,author ');
                $info['time'] = date('Y-m-d H:i:s' ,$value['create_time']);
                $list[] = $info;
            }
        }else{

        }

        if(!empty($like)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$list
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'到底了..',
                'data'=>[],
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }


    }

    //获取栏目的子栏目
    private function getSonColumn($column_id)
    {
        $column = new \app\miniapp\model\Column();
        //先查询出所有栏目
        $column_list = $column->getColumnList([], ' column_id as id,parent_id ');

        //获取所有子栏目
        $column_list = $this->getSonList($column_id, $column_list);

        return $column_list;
    }

    //根据子栏目组合成查询条件
    private function getSonWhere($column)
    {
        //获取栏目及子栏目
        $column_list = $this->getSonColumn($column);
        $column_list[] = $column;

        //获取文档数据
        //循环拼接查询条件
        $where = '';
        foreach ($column_list as $value) {
            $where .= " channel = $value or ";
        }

        //去掉多余的or
        return rtrim($where, 'or ');
    }

    //递归获取子集栏目列表
    public function getSonList($son_id ,$column_list){
        $son_arr = [];
        foreach($column_list as $value){
            if($value['parent_id'] == $son_id){
                $son_arr[] = $value['id'];
                $this->getSonList($value['id'] ,$column_list);
            }
        }
        return $son_arr;
    }

    //我的下载
    public function getMyDown(){
        //验证数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        $start = input('start');
        if(!isset($start) || !is_numeric($start)){
            echo '非法访问';die;
        }
        //获取下载列表
        $operate = new Down();
        $column = new \app\admin\model\Column();
        $column_list = $column->getColumnList(['parent_id'=>54] ,'id' ,1000);
        //组合查询条件
        $where = '';
        foreach($column_list as $key => $value){
            $where .= " column_id = $value[id] or ";
        }
        $where = rtrim($where ,'or ');
        $where = " ( $where ) and uid = $uid ";
        if(!empty($start)){
            $where .= " and article_id < $start ";
        }
        $down_list = $operate->getDownList($where ,' * ' ,15 ,' article_id desc');
        //循环处理列表
        foreach($down_list as $key => $value){
            $down_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
        }
        if(!empty($down_list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$down_list
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'已经到底啦...'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
}