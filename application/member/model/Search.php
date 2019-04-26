<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/9 0009
 * Time: 14:31
 */

namespace app\member\model;


use app\common\model\Base;

class Search extends Base
{
    public $table = 'search';
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}