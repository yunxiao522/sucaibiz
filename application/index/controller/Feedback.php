<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/11 0011
 * Time: 17:27
 */

namespace app\index\controller;


use think\Request;

class Feedback extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string|\think\response\View
     * Description 意见反馈(frame)框架方法
     */
    public function frame(){
        if(Request::instance()->isPost()){
            $content = input('content');
            if(empty($content)){
                return $this->ajaxError('反馈的内容不能为空');
            }
            $res = Model('feedback')->add([
                'content'=>$content,
                'title'=>'意见反馈',
                'create_time'=>time(),
                'uid'=>$this->uid,
                'status'=>1
            ]);
            if($res){
                return $this->ajaxOk('反馈成功');
            }else{
                return $this->ajaxError('反馈失败');
            }
        }else{
            return view('/templates/feedbackin');
        }
    }

    public function index(){

    }
}