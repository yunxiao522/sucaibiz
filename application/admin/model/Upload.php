<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/11
 * Time: 16:10
 * Description：附件表数据库模型
 */
namespace app\admin\model;
use app\common\model\Base;
use think\Db;
class Upload extends Base{
    public $table = 'upload';
    public function __construct()
    {
        parent::__construct();
    }

    //获取附件列表
    public function getUploadList($where = [] , $field = ' * ' , $limit = 100 , $order = 'id desc'){
        $res = Db::name($this->table )->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取附件表总数
    public function getUploadCount($where = []){
        $res = Db::name($this->table)->where($where)->count('id');
        return $res;
    }

    //添加附件信息
    public function createUploadInfo($arr = []){
        if(empty($arr)){
            return false;
        }
        $res = Db::name($this->table)->insert($arr);
        if($res === false){
            return false;
        }else{
            //获取新添加数据的id
            $last_id = Db::name($this->table)->getLastInsID('id');
            return $last_id;
        }
    }

    //更新附件表信息方法
    public function updateUpload($where=[],$arr=[]){
        if(empty($where) || empty($arr)){
            return false;
        }
        $res = Db::name($this->table)->where($where)->update($arr);
        if($res === false){
            return false;
        }else{
            return true;
        }
    }

    //获取附件的大小
    public function getUploadSize($where = []){
        return Db::name($this->table)->where($where)->sum('filesize');
    }
}