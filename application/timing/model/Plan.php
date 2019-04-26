<?php


namespace app\timing\model;


use app\common\model\Base;

class Plan extends Base
{
    public $table = 'task';
    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}