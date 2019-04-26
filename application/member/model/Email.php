<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/4/19
 * Time: 11:19
 */

namespace app\member\model;

use think\Model;
use think\Db;

class Email extends Model
{
    private $table_name = 'user_email';

    public function __construct()
    {
        parent::__construct();
    }

    //写入会员邮件表
    public function addEmail($data = [])
    {

        if (empty($data)) {
            return false;
        }
        $res = Db::name($this->table_name)->insert($data);
        return $res;
    }
}