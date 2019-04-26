<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2018/8/6
 * Time: 20:34
 */

namespace app\member\controller;
use app\member\model\Fens;
use think\Request;

class Options extends Common
{
    public function __construct()
    {
        parent::__construct();
    }
    //关注操作
    public function attenOptions(){
        if(Request::instance()->isPost()){
            //验证数据
            $uid = input('uid');
            if(!isset($uid) || empty($uid) || !is_numeric($uid)){
                echo '非法访问';die;
            }
            $fensid = input('fensid');
            if(!isset($fensid) || empty($fensid) || !is_numeric($fensid)){
                echo '非法访问';die;
            }
            //查询是否已经存在该用户和粉丝对应关系
            //组合查询条件
            $where = ['uid'=>$fensid ,'fens_id'=>$uid];
            $fens = new Fens();
            $count = $fens->getCount($where ,'fens_id');
            if($count == 0){
                //组合数据添加对应关系数据
                $arr = [
                    'uid'=>$fensid,
                    'fens_id'=>$uid,
                    'create_time'=>time()
                ];
                $res = $fens->addFensInfo($arr);
                if($res){
                    $a = [
                        'errorcode'=>0,
                        'msg'=>'关注成功',
                        'status'=>true
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'添加关注失败'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
            }else{
                //删除对应关系数据
                $res = $fens->delFensInfo($where);
                if($res){
                    $a = [
                        'errorcode'=>0,
                        'msg'=>'取消关注成功',
                        'status'=>false
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }else{
                    $a = [
                        'errorcode'=>1,
                        'msg'=>'取消关注失败'
                    ];
                    return json_encode($a ,JSON_UNESCAPED_UNICODE);
                }
            }
        }

    }
    //获关注状态
    public function getFensStatus(){
        //验证前台数据
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }
        //组合数据，查询粉丝和用户对应关系条数
        $where = [
            'uid'=>$uid,
            'fens_id'=>$this->member_info['id'],
        ];
        $fens = new Fens();
        $count = $fens->getCount($where);
        if($count == 0){
            $a = [
                'errorcode'=>0,
                'msg'=>'获取数据成功',
                'status'=>false
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>0,
                'msg'=>'获取状态成功',
                'status'=>true
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }
    //获取标签列表方法
    public function getUserTagList(){
        //验证数据

    }
    //添加用户标签方法
    public function addUserTag(){
        if(Request::instance()->isPost()){
            //验证前台数据
            $tag = input('tag');
            if(!isset($tag)){
                echo '非法访问';die;
            }
            if(empty($tag)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的标签内容不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($tag ,JSON_UNESCAPED_UNICODE) >30){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的标签内容不能超过30个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            //组合数据添加到数据库
            $arr = [
                'name'=>$tag,
                'create_time'=>time(),
                'user_id'=>$this->member_info['id']
            ];
            $user = new \app\member\model\User();
            $res = $user->addUserTagInfo($arr);
            if($res){
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
            return View('templates/options_addusertag');
        }
    }
}