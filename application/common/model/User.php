<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/22 0022
 * Time: 14:53
 */

namespace app\common\model;


class User extends Base
{
    public $table = 'user';
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}