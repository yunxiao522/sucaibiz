<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/7 0007
 * Time: 13:54
 */

namespace app\common\model;


use think\Model;
use think\Db;

class Base extends Model
{
    public $table = '';//表名
    public $limit = 20;//查询一次获取的条数
    public $operate_table = 'log_operate';//操作记录表名
    public $level_log = 1;//操作日志等级
    public $user_type;//操作人类型
    public $user_id;//操作人id
    public function __construct($data = [])
    {
        parent::__construct($data);
        parent::initialize();
    }

    //获取单条数据
    public function getOne($where = [] , $field = ' * ' ,$order = 'id desc'){
        $res = Db::name($this->table)->where($where)->field($field)->order($order)->find();
        //处理返回的数据
        foreach($this->except as $value){
            if(isset($res['$value'])) unset($res[$value]);
        }
        return $res;
    }

    //获取列表数据
    public function getList($where = [] , $field = ' * ' ,$order = ' id desc '){
        $page = input('page') ? input('page') : 1;
        $limit = input('limit') ? input('limit') : $this->limit;
        $limits = $limit = ($page - 1) * $limit . ',' . $limit;
        $res = Db::name($this->table)->where($where)->field($field)->limit($limits)->order($order)->select();
        $count = Db::name($this->table)->where($where)->count();
        $arr = [
            'data'=>$res,
            'count'=>$count,
            'code'=>0,
            'current_page'=>$page,
            'max_page'=>ceil($count/$this->limit)
        ];
        return $arr;
    }

    //获取全部数据
    public function getAll($where = [],$field = '*',$limit = 1000 ,$order = ' id desc '){
        $res = Db::name($this->table)->where($where)->field($field)->limit($limit)->order($order)->select();
        return $res;
    }

    //新增一条数据
    public function add($data){
        $res = Db::name($this->table)->insertGetId($data);
        return $res;
    }

    /**
     * @param array $where 修改条件
     * @param array $data 修改的额数据
     * @param bool $log 是否开启操作日志记录
     * @param int $uid 操作人id
     * @return int|string 修改结果
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * Description 修改方法
     */
    public function edit($where = [] ,$data = [] ,$log = false){
        $res = Db::name($this->table)->where($where)->update($data);
        if($res && $log){
            $this->insertOperateLog(1,json_encode($where,true),$this->table,json_encode($data,true));
        }
        return $res;
    }

    /**
     * @param $where 更新条件
     * @param $data 更新数据
     * @return bool 更新结果
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * Description 批量修改方法
     */
    public function editAll($where ,$data){
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

    //删除数据
    public function del($where = [] ,$log = false){
        $data = Db::name($this->table)->where($where)->field('*')->find();
        $res = Db::name($this->table)->where($where)->delete();
        if($res && $log){
            $this->insertOperateLog(2,json_encode($where,true),$this->table,json_encode($data,true));
        }
        return $res;
    }

    //查询某一列的值
    public function getField($where = [],$field = '',$order = 'id desc'){
        $res = Db::name($this->table)->where($where)->field($field)->order($order)->limit(1)->find();
        if(!empty($res) && isset($res[$field])){
            return $res[$field];
        }else{
            return '';
        }
    }

    //减少某一个项的值
    /**
     * @param array $where
     * @param string $field
     * @param int $num
     * @return int|true
     * @throws \think\Exception
     */
    public function fielddec($where = [],$field = '',$num = 1){
        $res = Db::name($this->table)->where($where)->setDec($field,$num);
        return $res;
    }

    //增加某一项的值
    /**
     * @param array $where
     * @param string $field
     * @param int $num
     * @return int|true
     * @throws \think\Exception
     */
    public function fieldinc($where = [],$field = '',$num = 1){
        $res = Db::name($this->table)->where($where)->setInc($field,$num);
        return $res;
    }

    /**
     * @param array $where
     * @param string $field
     * @return int|string
     * Description 获取总条数
     */
    public function getCount($where = [],$field = 'id'){
        $res = Db::name($this->table)->where($where)->count($field);
        return $res;
    }

    /**
     * @param int $type 操作类型
     * @param string $class 操作的数据表
     * @param string $content 操作内容
     */
    private function insertOperateLog($type = 1,$where = '',$class = '',$content = ''){
        $data = [
            'type'=>$type,
            'class'=>$class,
            'uid'=>$this->user_id,
            'content'=>$content,
            'level'=>$this->level_log,
            'user_type'=>$this->user_type,
            'create_time'=>time(),
            'where'=>$where
        ];
        Db::name($this->operate_table)->insert($data);
    }

    public static function __callStatic($method, $args)
    {
        return parent::__callStatic($method, $args); // TODO: Change the autogenerated stub
    }
}