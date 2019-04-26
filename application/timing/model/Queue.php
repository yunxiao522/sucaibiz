<?php


namespace app\timing\model;


use app\common\model\Base;

class Queue extends Base
{
    public $table = 'queue';
    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}
