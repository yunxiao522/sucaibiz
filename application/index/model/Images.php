<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/24
 * Time: 18:32
 * Description:
 */


namespace app\index\model;
use think\Db;
use think\Model;

class Images extends Model
{
    private $color_table_name = 'images_color';
    private $scrn_table_name = 'images_scrn';
    //获取颜色列表
    public function getImagesColor(){
        return Db::name($this->color_table_name)->select();
    }
    //获取屏幕尺寸列表
    public function getImagesScrn(){
        return Db::name($this->scrn_table_name)->select();
    }
}