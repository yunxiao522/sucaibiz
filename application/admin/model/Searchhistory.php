<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/10 0010
 * Time: 1:59
 */

namespace app\admin\model;


class Searchhistory extends Common
{
    public $table = 'search_history';
    public function __construct()
    {
        parent::__construct();
    }
}