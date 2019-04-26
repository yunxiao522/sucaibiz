<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/11
 * Time: 16:09
 * Description：附件管理
 */
namespace app\admin\controller;

use SucaiZ\file;
use think\Session;

class Upload extends Common{
    private $file_mime_arr;
    public function __construct()
    {
        parent::__construct();
        $this->file_mime_arr = getFileMimeArray();

    }

    //显示附件列表
    public function show(){
        return View();
    }

    //获取附件表json数据
    public function getuploadlistjson(){
        $limit=(input('page') - 1)*input('limit') .',' .input('limit');
        $upload = model('Upload');
        $upload_list = $upload->getUploadList([] , ' * ' ,$limit);
        foreach($upload_list as $key=>$value){
            $upload_list[$key]['filesize'] = tosize($value['filesize']);
            $upload_list[$key]['create_time'] = date('Y-m-d H:i:s' , $value['create_time']);
        }
        $upload_count = $upload->getUploadCount([]);
        $arr = [
            'data'=>$upload_list,
            'count'=>$upload_count,
            'code'=>0
        ];
        return json_encode($arr , JSON_UNESCAPED_UNICODE);
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