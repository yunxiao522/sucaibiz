<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/23
 * Time: 15:33
 * Description:
 */


namespace app\index\controller;


use think\Collection;
use think\Request;

class Original extends Collection
{
    public function fileUrl(){
        $file_url = Request::instance()->baseUrl();
        $file_url = str_replace('/uploads/allimg' ,'http://img.sucai.biz',$file_url);
        header('location:' .$file_url);
    }
}