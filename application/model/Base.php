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
use think\Loader;
use think\Model;

class Base extends Model implements Log
{
    // 完整表名
    protected $table;
    // 不包含表前缀表名
    protected $name;
    // 排除字段
    protected $except;
    // 创建时间字段
    protected $createTime = 'create_time';
    // 更新时间字段
    protected $updateTime = 'alter_time';
    // 自动更新时间戳
    protected $autoWriteTimestamp = 'true';
    // 时间字段取出后的默认时间格式
    protected $dateFormat;
    // 数据库表字段
    protected static $fields;
    // 每次查询的数据条数
    protected static $limit = 20;
    // 存储的cache实例
    protected static $cache;
    // 是否记录到log内
    protected static $is_log = false;


    // 操作之前的数据
    protected $log_data = [];
    // 日志类型,1->修改 2->删除
    public static $log_level = 1;
    // 日志用户类型 1->前台 2->后台
    public static $log_user_type = 2;
    // 日志用户id
    public static $log_user_id = -1;
    // update和delete结果
    protected static $result;

    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    protected static function init()
    {
        //注册更新事件执行之前和执行之后回调的日志操作事件
        self::beforeUpdate(function (&$model){
            if($model::$is_log){
                $model->log_data = $model::where($model->getWhere())->select();
            }
        });
        self::afterUpdate(function (&$model){
            if($model::$is_log && $model->result !== false){
                foreach($model->log_data as $value){
                    $model->log($model, 1, $value);
                }
            }
        });
        //注册删除事件执行之前和执行之后回调的日志操作事件
        self::beforeDelete(function (&$model){
            if($model::$is_log){
                $model->log_data = $model::where($model->getWhere())->select();
            }
        });
        self::afterDelete(function (&$model){
            if($model::$is_log && $model->result !== false){
                foreach($model->log_data as $value){
                    $model->log($model, 2, $value);
                }
            }
        });
        parent::init();
    }

    /**
     * @param $where
     * @param string $field
     * @param string $order
     * @return array
     * Description 获取一条数据
     */
    public static function getOne($where, $field = '*', $order = 'id desc', $cache = false)
    {
        $order = self::getOrder($order);
        return self::where($where)->field($field)->order($order)->cached('getOne', $cache, 100);
    }

    /**
     * @param $where
     * @param string $field
     * @param string $order
     * @return mixed|string
     * Description 获取某一列数据
     */
    public static function getField($where, $field = '', $order = 'id desc', $cache = false, $ttl = 100)
    {
        if (!self::checkExistField($field)) {
            return '';
        }
        $order = self::getOrder($order);
        $res = self::where($where)->field($field)->order($order)->cached('getField', $cache, $ttl);
        return $res[$field];
    }

    /**
     * @param array $where
     * @param string $field
     * @return int|string
     * Description 获取总条件
     */
    public static function getCount($where = [], $field = 'id')
    {
        if (!self::checkExistField($field)) {
            $field = (new static)->getPk();
        }
        return self::where($where)->count($field);
    }

    /**
     * @param array $where
     * @param string $field
     * @param string $order
     * @return array
     * Description 获取列表数据
     */
    public static function getList($where = [], $field = ' * ', $order = ' id desc ')
    {
        $page = input('page') ? input('page') : 1;
        $limit = input('limit') ? input('limit') : self::$limit;
        $limits = $limit = ($page - 1) * $limit . ',' . $limit;
        $order = self::getOrder($order);
        $res = self::where($where)->field($field)->limit($limits)->order($order)->select();
        $count = self::getCount($where);
        $arr = [
            'data' => $res,
            'count' => $count,
            'code' => 0,
            'current_page' => $page,
            'max_page' => ceil($count / self::$limit)
        ];
        return $arr;
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $limit
     * @param string $order
     * @return array
     * Description 获取全部数据
     */
    public static function getAll($where = [], $field = '*', $limit = 1000, $order = 'id desc')
    {
        return self::where($where)->field($field)->limit($limit)->order($order)->select();
    }

    /**
     * @param $data
     * @return int|string
     * Description 新增一条数据
     */
    public static function add($data)
    {
        $pk = (new static)->getPk();
        if (empty($pk)) {
            return self::insert($data);
        } else {
            return self::insertGetId($data);
        }
    }

    /**
     * @param $data
     * @return int|string
     * Description 新增多条数据
     */
    public static function addAll($data)
    {
        Db::startTrans();
        $Success_Length = self::insertAll($data);
        if($Success_Length != count($data)){
            Db::rollback();
            return false;
        }
        Db::commit();
        return true;
    }

    /**
     * @param array $where
     * @param array $data
     * @param bool $log
     * @return Base
     * Description 修改数据方法
     */
    public static function edit($where = [], $data = [], $cache = false, $log = false)
    {
        self::$is_log = $log;
        return self::update($data, $where);
    }

    /**
     * @param array $where
     * @param bool $log
     * @return int
     * Description 删除数据
     */
    public static function del($where = [], $cache = false, $log = false)
    {
        self::$is_log = $log;
        $model = new static();
        $model->isUpdate(false, $where);
        return $model->delete();
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $num
     * @return int|Model|true
     * @throws \think\Exception
     * Description 减少某一列的值
     */
    public static function fieldDec($where = [], $field = '', $num = 1)
    {
        return self::where($where)->setDec($field, $num);
    }

    /**
     * @param array $where
     * @param string $field
     * @param int $num
     * @return int|Model|true
     * @throws \think\Exception
     * Description 增加某一列的值
     */
    public static function fieldInc($where = [], $field = '', $num = 1)
    {
        return self::where($where)->setInc($field, $num);
    }

    /**
     * @param array $where
     * @param string $field
     * @return float|int
     * Description 根据条件获取某一列数据总和
     */
    public static function getSum($where = [], $field = '')
    {
        if (empty($field)) {
            return 0;
        }
        return self::where($where)->sum($field);
    }

    /**
     * @param $order
     * @return string
     * Description 获取默认排序规则
     */
    private static function getOrder($order)
    {
        $order = trim($order, ' ');
        $order_column = explode(' ', $order);
        if (!self::checkExistField($order_column[0])) {
            $pk = (new static)->getPk();
            if (!empty($pk)) {
                return (new static)->getPk() . ' ' . $order_column[1];
            } else {
                $table_filed = self::getTableFields();
                return $table_filed[0] . ' ' . $order_column[1];
            }
        }
        return $order;
    }

    /**
     * @param $model
     * @param $type
     * @param $data
     * Description 添加日志方法
     */
    public function log($model, $type, $data)
    {
        unset($model->data['result']);
        $pk = $model->getPk();
        if(!isset($data[$pk])){
            return ;
        }
        $log_data = [
            'type'=>$type,
            'class'=>$model->name,
            'uid'=>self::$log_user_id,
            'content'=>json_encode($model->data, JSON_UNESCAPED_UNICODE),
            'create_time'=>time(),
            'level'=>self::$log_level,
            'user_type'=>self::$log_user_type,
            'where'=>json_encode([$pk => $data[$pk]]),
            'data'=>json_encode($data, JSON_UNESCAPED_UNICODE)
        ];
        task('writeLog', $log_data);
    }

    /**
     * 重构Model的保存当前数据对象方法
     * @access public
     * @param array  $data     数据
     * @param array  $where    更新条件
     * @param string $sequence 自增序列名
     * @return integer|false
     */
    public function save($data = [], $where = [], $sequence = null)
    {
        if (is_string($data)) {
            $sequence = $data;
            $data     = [];
        }

        if (!empty($data)) {
            // 数据自动验证
            if (!$this->validateData($data)) {
                return false;
            }
            // 数据对象赋值
            foreach ($data as $key => $value) {
                $this->setAttr($key, $value, $data);
            }
            if (!empty($where)) {
                $this->isUpdate    = true;
                $this->updateWhere = $where;
            }
        }

        // 自动关联写入
        if (!empty($this->relationWrite)) {
            $relation = [];
            foreach ($this->relationWrite as $key => $name) {
                if (is_array($name)) {
                    if (key($name) === 0) {
                        $relation[$key] = [];
                        foreach ($name as $val) {
                            if (isset($this->data[$val])) {
                                $relation[$key][$val] = $this->data[$val];
                                unset($this->data[$val]);
                            }
                        }
                    } else {
                        $relation[$key] = $name;
                    }
                } elseif (isset($this->relation[$name])) {
                    $relation[$name] = $this->relation[$name];
                } elseif (isset($this->data[$name])) {
                    $relation[$name] = $this->data[$name];
                    unset($this->data[$name]);
                }
            }
        }

        // 数据自动完成
        $this->autoCompleteData($this->auto);

        // 事件回调
        if (false === $this->trigger('before_write', $this)) {
            return false;
        }
        $pk = $this->getPk();
        if ($this->isUpdate) {
            // 自动更新
            $this->autoCompleteData($this->update);

            // 事件回调
            if (false === $this->trigger('before_update', $this)) {
                return false;
            }

            // 获取有更新的数据
            $data = $this->getChangedData();

            if (empty($data) || (count($data) == 1 && is_string($pk) && isset($data[$pk]))) {
                // 关联更新
                if (isset($relation)) {
                    $this->autoRelationUpdate($relation);
                }
                return 0;
            } elseif ($this->autoWriteTimestamp && $this->updateTime && !isset($data[$this->updateTime])) {
                // 自动写入更新时间
                $data[$this->updateTime]       = $this->autoWriteTimestamp($this->updateTime);
                $this->data[$this->updateTime] = $data[$this->updateTime];
            }

            if (empty($where) && !empty($this->updateWhere)) {
                $where = $this->updateWhere;
            }

            // 保留主键数据
            foreach ($this->data as $key => $val) {
                if ($this->isPk($key)) {
                    $data[$key] = $val;
                }
            }

            if (is_string($pk) && isset($data[$pk])) {
                if (!isset($where[$pk])) {
                    unset($where);
                    $where[$pk] = $data[$pk];
                }
                unset($data[$pk]);
            }

            // 检测字段
            $allowFields = $this->checkAllowField(array_merge($this->auto, $this->update));

            // 模型更新
            if (!empty($allowFields)) {
                $result = $this->getQuery()->where($where)->strict(false)->field($allowFields)->update($data);
            } else {
                $result = $this->getQuery()->where($where)->update($data);
            }
            // 重构方法中加入将执行结果赋值给类的成员属性,以便在回调中根据是否执行成功来做下一步操作
            $this->result = $result;
            // 关联更新
            if (isset($relation)) {
                $this->autoRelationUpdate($relation);
            }

            // 更新回调
            $this->trigger('after_update', $this);

        } else {
            // 自动写入
            $this->autoCompleteData($this->insert);

            // 自动写入创建时间和更新时间
            if ($this->autoWriteTimestamp) {
                if ($this->createTime && !isset($this->data[$this->createTime])) {
                    $this->data[$this->createTime] = $this->autoWriteTimestamp($this->createTime);
                }
                if ($this->updateTime && !isset($this->data[$this->updateTime])) {
                    $this->data[$this->updateTime] = $this->autoWriteTimestamp($this->updateTime);
                }
            }

            if (false === $this->trigger('before_insert', $this)) {
                return false;
            }

            // 检测字段
            $allowFields = $this->checkAllowField(array_merge($this->auto, $this->insert));
            if (!empty($allowFields)) {
                $result = $this->getQuery()->strict(false)->field($allowFields)->insert($this->data);
            } else {
                $result = $this->getQuery()->insert($this->data);
            }

            // 获取自动增长主键
            if ($result && is_string($pk) && (!isset($this->data[$pk]) || '' == $this->data[$pk])) {
                $insertId = $this->getQuery()->getLastInsID($sequence);
                if ($insertId) {
                    $this->data[$pk] = $insertId;
                }
            }

            // 关联写入
            if (isset($relation)) {
                foreach ($relation as $name => $val) {
                    $method = Loader::parseName($name, 1, false);
                    $this->$method()->save($val);
                }
            }

            // 标记为更新
            $this->isUpdate = true;

            // 新增回调
            $this->trigger('after_insert', $this);
        }
        // 写入回调
        $this->trigger('after_write', $this);

        // 重新记录原始数据
        $this->origin = $this->data;

        return $result;
    }

    /**
     * 删除当前的记录
     * @access public
     * @return integer
     */
    public function delete()
    {
        if (false === $this->trigger('before_delete', $this)) {
            return false;
        }

        // 删除条件
        $where = $this->getWhere();

        // 删除当前模型数据
        $result = $this->getQuery()->where($where)->delete();

        // 关联删除
        if (!empty($this->relationWrite)) {
            foreach ($this->relationWrite as $key => $name) {
                $name  = is_numeric($key) ? $name : $key;
                $model = $this->getAttr($name);
                if ($model instanceof Model) {
                    $model->delete();
                }
            }
        }

        // 重构方法中加入将执行结果赋值给类的成员属性,以便在回调中根据是否执行成功来做下一步操作
        $this->result = $result;

        $this->trigger('after_delete', $this);
        // 清空原始数据
        $this->origin = [];

        return $result;
    }

    /**
     * @param $name
     * Description 设置数据表表名
     */
    public function setName($name){
        $this->name = $name;
    }

    /**
     * @param $field
     * @return bool
     * Description 判断数据表中是否存在莫个字段
     */
    private static function checkExistField($field)
    {
        if (array_search($field, self::getTableFields()) === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function __callStatic($method, $args)
    {
        return parent::__callStatic($method, $args); // TODO: Change the autogenerated stub
    }
}