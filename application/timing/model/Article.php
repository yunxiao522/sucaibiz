<?php


namespace app\timing\model;


use app\common\model\Base;

class Article extends Base
{
    public $table = 'article';

    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}