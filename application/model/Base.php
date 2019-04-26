<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/7 0007
 * Time: 13:54
 * Description 基础Model
 */

namespace app\model;

use think\Db;

class Base
{
    //数据库表名
    public static $table = '';
    //查询排除的字段
    public static $except =[];
    //查询一次获取的条数
    public static $limit = 20;
    //操作日志表表名
    public static $operate_table = 'log_operate';
    //操作日志等级
    public static $level_log;
    //操作人类型
    public static $user_type;
    //操作人id
    public static $user_id;

    /**
     * @param array $where
     * @param string $field
     * @param string $order
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取单条数据
     */
    public static function getOne($where = [] , $field = ' * ' ,$order = 'id desc'){
        $res = Db::name(self::$table)->where($where)->field($field)->order($order)->find();
        //处理返回的数据
        foreach(self::$except as $value){
            if(isset($res['$value'])) unset($res[$value]);
        }
        return $res;
    }

    /**
     * @param array $where
     * @param string $field
     * @param string $order
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取列表数据
     */
    public static function getList($where = [] , $field = ' * ' ,$order = ' id desc '){
        $page = input('page') ? input('page') : 1;
        $limit = input('limit') ? input('limit') : self::$limit;
        $limits = $limit = ($page - 1) * $limit . ',' . $limit;
        $res = Db::name(self::$table)->where($where)->field($field)->limit($limits)->order($order)->select();
        $count = Db::name(self::$table)->where($where)->count();
        $arr = [
            'data'=>$res,
            'count'=>$count,
            'code'=>0,
            'current_page'=>$page,
            'max_page'=>ceil($count/self::$limit)
        ];
        return $arr;
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 获取全部数据
     */
    public static function getAll($where = [],$field = '*',$limit = 1000 ,$order = ' id desc '){
        $res = Db::name(self::$table)->where($where)->field($field)->limit($limit)->order($order)->select();
        return $res;
    }

    /**
     * @param $data
     * @return int|string
     * Description 新增一条数据
     */
    public static function add($data){
        $res = Db::name(self::$table)->insertGetId($data);
        return $res;
    }

    /**
     * @param array $where
     * @param array $data
     * @param bool $log
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * Description 修改数据
     */
    public static function edit($where = [] ,$data = [] ,$log = false){
        $res = Db::name(self::$table)->where($where)->update($data);
        if($res && $log){
            self::insertOperateLog(1,json_encode($where,true),self::$table,json_encode($data,true));
        }
        return $res;
    }

    /**
     * @param $where
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * Description 修改多条数据
     */
    public static function editAll($where ,$data){
        if(!$where || !$data){
            return false;
        }
        if(count($where) != count($data)){
            return false;
        }
        //开启数据库事务
        Db::startTrans();
        foreach($where as $key => $value){
            $res = self::edit($value,$data);
            if(!$res){
                Db::rollback();
                return false;
            }
        }
        Db::commit();
        return true;
    }

    /**
     * @param array $where
     * @param bool $log
     * @return int
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     * Description 删除数据
     */
    public static function del($where = [] ,$log = false){
        $data = Db::name(self::$table)->where($where)->field('*')->find();
        $res = Db::name(self::$table)->where($where)->delete();
        if($res && $log){
            self::insertOperateLog(1,json_encode($where,true),self::$table,json_encode($data,true));
        }
        return $res;
    }

    /**
     * @param array $where
     * @param string $field
     * @param string $order
     * @return mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Description 查询某一列的值
     */
    public static function getField($where = [],$field = '',$order = 'id desc'){
        $res = Db::name(self::$table)->where($where)->field($field)->order($order)->limit(1)->find();
        if(!empty($res) && isset($res[$field])){
            return $res[$field];
        }else{
            return '';
        }
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $num
     * @return int|true
     * @throws \think\Exception
     * Description 减少某一个项的值
     */
    public static function dec($where = [],$field = '',$num = 1){
        $res = Db::name(self::$table)->where($where)->setDec($field,$num);
        return $res;
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $num
     * @return int|true
     * @throws \think\Exception
     * Description 增加某一项的值
     */
    public static function fieldinc($where = [],$field = '',$num = 1){
        $res = Db::name(self::$table)->where($where)->setInc($field,$num);
        return $res;
    }

    /**
     * @param array $where
     * @param string $field
     * @return int|string
     * @throws \think\Exception
     * Description 获取总条数
     */
    public static function getCount($where = [],$field = 'id'){
        $res = Db::name(self::$table)->where($where)->count($field);
        return $res;
    }

    /**
     * @param int $type
     * @param string $where
     * @param string $class
     * @param string $content
     * Description 插入操作日志
     */
    private static function insertOperateLog($type = 1,$where = '',$class = '',$content = ''){
        $data = [
            'type'=>$type,
            'class'=>$class,
            'uid'=>self::$user_id,
            'content'=>$content,
            'level'=>self::$level_log,
            'user_type'=>self::$user_type,
            'create_time'=>time(),
            'where'=>$where
        ];
        Db::name(self::$operate_table)->insert($data);
    }
}