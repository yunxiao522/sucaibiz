<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/12 0012
 * Time: 11:18
 */

namespace app\common\model;


class ColumnType extends Base
{
    public $table = 'column_type';
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}