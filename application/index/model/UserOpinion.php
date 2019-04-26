<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/10 0010
 * Time: 13:05
 * Description：用户评论操作表模型
 */

namespace app\index\model;


class UserOpinion extends Common
{
    public $table = 'user_opinion';
    public function __construct()
    {
        parent::__construct();
    }
}