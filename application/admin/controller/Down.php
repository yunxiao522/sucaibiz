<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/24 0024
 * Time: 16:52
 */

namespace app\admin\controller;

use app\model\Down as Down_Model;

class Down extends Common
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return false|string
     * Description 获取热门下载数据
     */
    public function getHotDown(){
        $list = Down_Model::getList([],'*','end_time desc');
        return self::ajaxOkdata($list,'get data success','');
    }
}