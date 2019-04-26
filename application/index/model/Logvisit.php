<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/2/17 0017
 * Time: 23:11
 */

namespace app\index\model;


use app\common\model\Base;

class Logvisit extends Base
{
    public $table = 'log_visit';
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}