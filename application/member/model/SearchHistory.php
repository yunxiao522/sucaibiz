<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/9 0009
 * Time: 14:31
 */

namespace app\member\model;


use app\common\model\Base;
use think\Db;
use think\Model;

class SearchHistory extends Base
{
    public $table = 'search_history';
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    //添加搜索记录方法
    public function addHistory($keyword = '',$uid = 0){
        Db::startTrans();
        $res = Model('search')->getField(['keyword'=>$keyword],'id');
        if($res){
            $a = [
                'keyword'=>$keyword,
                'num'=>0,
                'create_time'=>time(),
                'alter_time'=>0
            ];
            $res = Model('search')->add($a);
            if(!$res){
                Db::rollback();
                return false;
            }
        }else{
            $res1 = Model('search')->fieldinc(['id'=>$res],'num');
            if(!$res1){
                Db::rollback();
                return false;
            }
        }
        $b = [
            'uid'=>$uid,
            'sid'=>$res,
            'keyword'=>$keyword,
            'create_time'=>time(),
            'alter_time'=>0
        ];
        $res = self::add($b);
        if($res){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }
}