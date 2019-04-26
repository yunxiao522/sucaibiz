<?php


namespace app\timing\model;

use app\common\model\Base;

class SinaComment extends Base
{
    public $table = 'sina_comment';
    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}