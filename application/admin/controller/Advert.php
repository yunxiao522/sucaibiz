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

class Advert extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    //显示广告列表
    public function show(){
        return View('advert_show_list');
    }

    //获取广告列表数据
    public function getAdvertList(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $where = [];
        $advert = new \app\admin\model\Advert();
        $list = $advert->getAdvertList($where ,' * ' ,$limit);
        //处理列表数据
        foreach($list as $key => $value){
            $list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
            if(!empty($value['alter_time'])){
                $list[$key]['alter_time'] = date('Y-m-d H:i;s' ,$value['alter_time']);
            }
            if($value['status'] == 1){
                $list[$key]['status'] = '启用';
            }else{
                $list[$key]['status'] = '禁用';
            }
        }
        $count = $advert->getCount($where);
        $arr = [
            'data' => $list,
            'count' => $count,
            'code' => 0
        ];
        return json_encode($arr ,JSON_UNESCAPED_UNICODE);
    }

    //新建广告
    public function add(){
        if(Request::instance()->isPost()){
            //验证数据
            $ad_name = input('ad_name');
            if(!isset($ad_name)){
                echo '非法访问';die;
            }
            if($ad_name == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告名称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($ad_name ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告名称不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $width = input('width');
            if(!isset($width)){
                echo '非法访问';die;
            }
            if(!is_numeric($width)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告宽度必须是数字'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if($width <0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告宽度不能小于0'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $height = input('height');
            if(!isset($height)){
                echo '非法访问';die;
            }
            if(!is_numeric($height)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告高度必须是数字'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if($height <0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告高度不能小于0'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $class = input('class');
            if(!isset($class)){
                echo '非法访问';die;
            }
            if($class == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告分组名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($class ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告分组名不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $status = input('status');
            if(isset($status)){
                $status = 1;
            }else{
                $status = 2;
            }
            $content = input('content');
            if(!isset($content)){
                echo '非法访问';die;
            }
            if($content == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告代码不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $palcename = input('palcename');
            if(!isset($palcename)){
                echo '非法访问';die;
            }
            if(mb_strlen($palcename ,'UTF-8') >30){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告说明不能超过30个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }

            //组合数据添加到数据库
            $arr = [
                'ad_name'=>$ad_name,
                'width'=>$width,
                'height'=>$height,
                'create_time'=>time(),
                'content'=>$content,
                'class'=>$class,
                'status'=>$status,
                'palcename'=>$palcename
            ];
            $advert = new \app\admin\model\Advert();
            if($advert->add($arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'添加成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'添加失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            return View('advert_add');
        }
    }
    //修改广告
    public function alter(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $where = ['id'=>$id];
        $advert = new \app\admin\model\Advert();
        if(Request::instance()->isPost()){
            //验证数据
            $ad_name = input('ad_name');
            if(!isset($ad_name)){
                echo '非法访问';die;
            }
            if($ad_name == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告名称不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($ad_name ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告名称不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $width = input('width');
            if(!isset($width)){
                echo '非法访问';die;
            }
            if(!is_numeric($width)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告宽度必须是数字'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if($width <0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告宽度不能小于0'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $height = input('height');
            if(!isset($height)){
                echo '非法访问';die;
            }
            if(!is_numeric($height)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告高度必须是数字'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if($height <0){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告高度不能小于0'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $class = input('class');
            if(!isset($class)){
                echo '非法访问';die;
            }
            if($class == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告分组名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($class ,'UTF-8') >20){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告分组名不能超过20个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $status = input('status');
            if(isset($status)){
                $status = 1;
            }else{
                $status = 2;
            }
            $content = input('content');
            if(!isset($content)){
                echo '非法访问';die;
            }
            if($content == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告代码不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $palcename = input('palcename');
            if(!isset($palcename)){
                echo '非法访问';die;
            }
            if(mb_strlen($palcename ,'UTF-8') >30){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的广告说明不能超过30个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }

            //组合数据添加到数据库
            $arr = [
                'ad_name'=>$ad_name,
                'width'=>$width,
                'height'=>$height,
                'alter_time'=>time(),
                'content'=>$content,
                'class'=>$class,
                'status'=>$status,
                'palcename'=>$palcename
            ];
            $advert = new \app\admin\model\Advert();
            if($advert->alterAdvert($where ,$arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $info = $advert->getAdvert($where);
            $this->assign('info' ,$info);
            return View('advert_alter');
        }
    }
    //删除广告
    public function del(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $where = ['id'=>$id];
        $advert = new \app\admin\model\Advert();
        if($advert->delAdvert($where)){
            $a = [
                'errorcode'=>0,
                'msg'=>'删除成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'删除失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
}