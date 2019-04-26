<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/15
 * Time: 10:05
 * Description: 文档控制器
 */


namespace app\miniapp\controller;


use think\Collection;

class Article extends Collection
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取文档内容
    public function getArticleInfo()
    {
        //验证数据
        $aid = input('aid');
        if (!isset($aid) || empty($aid) || !is_numeric($aid)) {
            echo '非法访问';
            die;
        }
        $channel = input('channel');
        if (!isset($channel) || empty($channel) || !is_numeric($channel)) {
            echo '非法访问';
            die;
        }
        //获取文档内容
        $article = new \app\miniapp\model\Article();
        $article_info = $article->getArticleInfoAll(['a.id' => $aid], $channel);
        //根据不同的文档类型，处理文档数据
        if ($channel == 2) {
            //匹配src图片路径方法
            $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
            preg_match_all($src_rule, $article_info['imgurls'], $match);
            //组合返回的数据
            $data = [
                'list' => $match[3],
                'num' => $article_info['imgnum'],
                'comment_num' =>$article_info['comment_num'],
            ];
            if (empty($match[3])) {
                $a = [
                    'errorcode' => 1,
                    'msg' => '请求数据失败'
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            } else {
                $a = [
                    'errorcode' => 0,
                    'msg' => '请求数据成功',
                    'data' => $data
                ];
                return json_encode($a, JSON_UNESCAPED_UNICODE);
            }
        }else if($channel == 1){
            if(!empty($article_info)){
                //处理数据
                $article_info['pubdate'] = date('Y-m-d H:i:s' ,$article_info['pubdate']);
                $a = [
                    'errorcode'=>0,
                    'msg'=>'获取数据成功',
                    'data'=>$article_info
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'获取数据失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }

        }
    }

    //获取文档列表
    public function getArticleList()
    {
        //验证数据
        $column = input('column');
        if (!isset($column) || !is_numeric($column)) {
            echo '非法访问';
            die;
        }
        if (empty($column)) {
            $a = [
                'errorcode' => 1,
                'msg' => '输入的栏目id不能为空'
            ];
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }

        $son = input('son');
        if (!isset($son) || empty($son)) {
            echo '非法访问';
            die;
        }

        $start = input('start');
        if (!isset($start) || !is_numeric($start)) {
            echo '非法访问';
            die;
        }

        //判断查询类型
        if($son == true){
            $where = $this->getSonWhere($column);
        }


        //获取查询顶部导航条件
        $where1 = " ( $where ) and arcatt like '%s%'  and is_delete = 1 ";


        //根据开始文档信息组合查询条件
        if($start != 0){
            $where = " ( $where ) " ." and id < $start ";
        }else{
            $where = " ( $where ) ";
        }

        $where .= " and is_delete = 1 and is_audit = 1 ";

        //处理最后的查询条件
        $where = ltrim($where ,' and');

        //查询文档列表
        $article = new \app\miniapp\model\Article();
        $article_list = $article->getArticleList($where ,' id,title,litpic,pubdate,click,column_id ' ,15 ,' id desc ');

        $column = new \app\miniapp\model\Column();
        //处理文档列表
        foreach($article_list as $key => $value){
            $article_list[$key]['pubdate'] = date('Y-m-d H:i:s' ,$value['pubdate']);
            $article_list[$key]['title'] = cut_str($value['title'] ,10);
            $column_info = $column->getColumnInfo(['id'=>$value['column_id']]);
            if(isset($column_info['type_name'])){
                $article_list[$key]['column'] = $column_info['type_name'];
            }else{
                $article_list[$key]['column'] = '';
            }

        }
        //查询滚动图像
        $roll_list = $article->getArticleList($where1 ,' id,title,roll_img,pubdate,column_id ' ,5 ,' id desc ');
        if(!empty($article_list)){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$article_list,
                'roll'=>$roll_list
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'已经到底了...'
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
            $where .= " column_id = $value or ";
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

    //获取文档基础信息
    public function getInfo(){
        //验证数据
        $aid = input('aid');
        if(!isset($aid) || empty($aid) || !is_numeric($aid)){
            echo '非法访问';die;
        }
        //组合查询条件
        $where = [
            'id'=>$aid,
            'is_delete'=>1
        ];
        $article = new \app\miniapp\model\Article();
        $info = $article->getArticleInfo($where);
        if(empty($info)){
            $a = [
                'errorcode'=>1,
                'msg'=>'文章不存在'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'data'=>$info
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //获取文档类型
    public function getArticleType(){
        //验证数据
        $aid = input('aid');
        if(!isset($aid) || empty($aid) || !is_numeric($aid)){
            echo '非法访问';die;
        }

        //查询文档的归属栏目
        $article = new \app\miniapp\model\Article();
        $article_info = $article->getArticleInfo(['id'=>$aid] ,' column_id ');

        //判断文档栏目所属类型
        //递归向上查询父级栏目,知道查询到最顶级
        $top_column = $this->getParentColumn($article_info['column_id']);
        if($top_column == 54){
            $type = 1;
        }else if($top_column == 24){
            $type = 2;
        }else if($top_column == 1){
            $type = 3;
        }else{
            $type = 4;
        }
        $a = [
            'errorcode'=>0,
            'msg'=>'获取数据成功',
            'data'=>['type'=>$type,'column'=>$article_info['column_id']]
        ];
        return json_encode($a ,JSON_UNESCAPED_UNICODE);

    }

    //递归查询父级栏目，直至最顶级
    private function getParentColumn($cid){
        static $column_id;
        $column = new \app\miniapp\model\Column();
        $column_info = $column->getColumnInfo(['id'=>$cid] ,' id,parent_id ');
        if($column_info['parent_id'] != 0){
            $this->getParentColumn($column_info['parent_id']);
        }else{
            $column_id = $column_info['id'];
        }
        return $column_id;
    }
}