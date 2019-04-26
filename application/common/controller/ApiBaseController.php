<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/6 0006
 * Time: 15:37
 */

namespace app\common\controller;


use think\Request;

class ApiBaseController extends BaseController
{
    public $uid;//用户id
    public $crype_key = 'www.sucai.biz';
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    /**
     * @return false|string
     * Description 验证用户是否存在
     */
    public function hasUser(){
        $token = input('uid');
        //组合查询条件
        $where = [
            'token'=>$token
        ];
        $this->uid = Model('user')->getField($where,'id');
        if(empty($this->uid)){
            return $this->ajaxError('用户不存在');
        }
    }
}