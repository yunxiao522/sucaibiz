<?php


namespace app\model;

class Tag extends Base
{
    protected $name = 'tag';

    protected $updateTime = '';

    /**
     * @param array $where
     * @param string $field
     * @param int $num
     * @return int|\think\Model|true
     * Description 重构base减少某一列值方法。当tag标签引用数小于等于1时,则删除该tag标签
     */
    public static function fieldDec($where = [], $field = '', $num = 1)
    {
        if($field == 'total'){
            $total = self::getField($where, 'total');
            if($total <= 1){
                return self::del($where);
            }
        }
        return parent::fieldDec($where, $field, $num);
    }
}