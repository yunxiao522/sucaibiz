<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/16
 * Time: 9:18
 * Description：文档类型数据库模型
 */
namespace app\admin\model;
use app\common\model\Base;
use think\Db;
class Channeltype extends Base {
    private $table_name = 'column_type';
    public $table = 'column_type';
    public function __construct()
    {
        parent::__construct();
    }
}