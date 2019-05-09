<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/11
 * Time: 16:09
 * Description：附件管理
 */
namespace app\admin\controller;

use app\model\Upload as Upload_Model;
use SucaiZ\file;
use think\Session;

class Upload extends Common{
    private $file_mime_arr;
    public function __construct()
    {
        parent::__construct();
        $this->file_mime_arr = getFileMimeArray();

    }

    /**
     * @return false|string
     * Description 获取附件表数据
     */
    public function getuploadlistjson(){
        $Upload_List = Upload_Model::getList([], '*','id desc');
        foreach($Upload_List['data'] as $key=>$value){
            $Upload_List['data'][$key]['filesize'] = tosize($value['filesize']);
        }
        return self::ajaxOkdata($Upload_List, 'get data success');
    }

    /**
     * @return false|string
     * Description 上传文件方法
     */
    public function uploadFile()
    {
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        File::setArticleInfo(0,'自定义文件上传');
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            return $this->ajaxOkdata(['url'=>File::$url],'上传成功');
        } else {
            return $this->ajaxError('上传失败');
        }
    }
}