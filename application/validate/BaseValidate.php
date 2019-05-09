<?php

namespace app\validate;

use think\Validate;

class BaseValidate extends Validate
{
    /**
     * @param string $callback 回调函数,可以对获取的数据进一步处理,最后将数据return即可
     * @param string $scene 使用验证的场景,为空则获取全部验证字段值
     * @return array 返回所需的表单数据
     * Description 获取表单数据
     */
    public function getData($scene = '', $callback = '')
    {
        if(!is_null($scene) && isset($this->scene[$scene])){
            $fields = $this->scene[$scene];
        }else{
            $fields = array_keys($this->rule);
        }
        $data = [];
        foreach ($fields as $value){
            $data[$value] = input($value);
        }
        if(!empty($callback)){
            return $callback($data, input());
        }else{
            return $data;
        }
    }

    /**
     * @param $data 验证字段的值
     * @param $field 要验证表的字段名称
     * @return bool 不唯一则返回false
     * Description 验证字段是否唯一
     */
    protected function uniquer($data, $field){
        $id = input('id');
        $where = [
            $field => $data
        ];
        if(!empty($id)){
            $where['id'] = ['neq', $id];
        }
        return $this->getCount($where) != 0 ? false : true;
    }

    /**
     * @param $data 验证字段的值
     * @param $params 传递的参数,每个参数使用","隔开
     * @return bool 不存在返回false
     * Description 验证字段是否存在于莫个表的字段中
     */
    protected function inScope($data, $params){
        $params = explode(',', $params);
        $arr = call_user_func_array(['app\model\\'. $params[0],'getAll'],[[],$params[1]]);
        return in_array($data,array_column($arr, $params[1]));
    }

    /**
     * @param $method 方法名称
     * @param $params 参数
     * @return mixed 执行结果
     * Description  魔术方法,可以将本类中相关的数据库获取数据方法映射到数据库Model上
     */
    public function __call($method, $params)
    {
        $way = substr($method, 0, 3);
        if(strtolower($way) == 'get'){
            $class_name = basename(str_replace('\\', '/', get_class($this)));
            return call_user_func_array(['app\model\\'. $class_name,$method],$params);
        }
    }
}