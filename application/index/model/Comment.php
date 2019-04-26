<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/10 0010
 * Time: 12:53
 */

namespace app\index\model;


class Comment extends Common
{
    public $table = 'comment';
    public function __construct()
    {
        parent::__construct();
    }
}