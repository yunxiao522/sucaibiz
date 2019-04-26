<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/16 0016
 * Time: 15:10
 */

namespace app\index\model;


class Versions extends Common
{
    public $table = 'version';
    public function __construct()
    {
        parent::__construct();
    }
}