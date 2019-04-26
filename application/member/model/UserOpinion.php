<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/10 0010
 * Time: 21:22
 */

namespace app\member\model;


use app\common\model\Base;

class UserOpinion extends Base
{
    public $table = 'user_opinion';
    public function __construct()
    {
        parent::__construct();
    }
}