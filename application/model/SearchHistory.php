<?php


namespace app\model;

use think\Db;

class SearchHistory extends Base
{
    protected $name = 'search_history';

    /**
     * @param string $keyword
     * @param int $uid
     * @return bool
     * Description 添加搜索记录方法
     */
    public static function addHistory($keyword = '',$uid = 0){
        Db::startTrans();
        $res = Search::getField(['keyword'=>$keyword],'id');
        if(!$res){
            $a = [
                'keyword'=>$keyword,
                'num'=>1,
                'create_time'=>time(),
                'alter_time'=>time()
            ];
            $res = Search::add($a);
            if(!$res){
                Db::rollback();
                return false;
            }
        }else{
            $res1 = Search::fieldinc(['id'=>$res],'num');
            Search::edit(['id'=>$res],['alter_time'=>time()]);
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