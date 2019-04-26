<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/17 0017
 * Time: 20:51
 */

namespace app\common\model;


class TagList extends Base
{
    public $table = 'tag_list';
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}