<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/11 0011
 * Time: 21:38
 */

namespace app\index\model;


class Feedback extends Common
{
    public $table = 'feedback';
    public function __construct()
    {
        parent::__construct();
    }
}