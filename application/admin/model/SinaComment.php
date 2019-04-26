<?php


namespace app\admin\model;


use app\common\model\Base;

class SinaComment extends Base
{
    public $table = 'sina_comment';
    public function __construct()
    {
        parent::__construct();
    }
}