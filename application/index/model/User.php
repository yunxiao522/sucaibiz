<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/4 0004
 * Time: 22:07
 */

namespace app\index\model;


use app\common\model\Base;

class User extends Base
{
    public $table = 'user';
    public $except = ['password'];
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}