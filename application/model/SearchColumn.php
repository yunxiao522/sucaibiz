<?php


namespace app\model;

class SearchColumn extends Base
{
    protected $name = 'search_column';

    public function getTidAttr($value)
    {
        return ColumnType::getField(['id'=>$value], 'typename', 'id desc', true);
    }


}