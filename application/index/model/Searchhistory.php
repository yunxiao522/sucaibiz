<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/9 0009
 * Time: 14:31
 */

namespace app\index\model;


use think\Db;

class Searchhistory extends Common
{
    public $table = 'search_history';
    public function __construct()
    {
        parent::__construct();
    }

    //添加搜索记录方法
    public function addHistory($keyword = '',$uid = 0){
        Db::startTrans();
        $res = Model('search')->getField(['keyword'=>$keyword],'id');
        if(!$res){
            $a = [
                'keyword'=>$keyword,
                'num'=>1,
                'create_time'=>time(),
                'alter_time'=>time()
            ];
            $res = Model('search')->add($a);
            if(!$res){
                Db::rollback();
                return false;
            }
        }else{
            $res1 = Model('search')->fieldinc(['id'=>$res],'num');
            Model('search')->edit(['id'=>$res],['alter_time'=>time()]);
            if(!$res1){
                Db::rollback();
                return false;
            }
        }
        $sid = $res;
        $res = self::getField(['uid'=>$uid,'sid'=>$res],'id');
        if(!$res){
            $b = [
                'uid'=>$uid,
                'sid'=>$sid,
                'keyword'=>$keyword,
                'create_time'=>time(),
                'alter_time'=>0
            ];
            $res = self::add($b);
        }
        if($res){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }
}