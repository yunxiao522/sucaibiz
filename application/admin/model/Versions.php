<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/14 0014
 * Time: 16:10
 */

namespace app\admin\model;


class Versions extends Common
{
    public $table = 'version';
    public function __construct()
    {
        parent::__construct();
    }
}