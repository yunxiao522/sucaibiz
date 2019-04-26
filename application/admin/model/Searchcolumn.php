<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/10 0010
 * Time: 2:00
 */

namespace app\admin\model;


class Searchcolumn extends Common
{
    public $table = 'search_column';
    public function __construct()
    {
        parent::__construct();
    }
}