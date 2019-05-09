<?php


namespace app\model;

class Article extends Base
{
    protected $name = 'article';

    public static $is_make = [1=>'已生成', 2=>'未生成'];

    public static $iscommend = [1=>'允许', 2=>'不允许'];

    public static $is_delete = [1=>'未删除', 2=>'已删除'];

    public static $is_audit = [1=>'审核通过', 2=>'未审核', 3=>'审核未通过'];

    public static $draft = [1=>'草稿', 2=>'正式'];

    public function getIsMakeAttr($value){
        return self::$is_make[$value];
    }

    public function getIscommendAttr($value){
        return self::$iscommend[$value];
    }

    public function getIsDeleteAttr($value){
        return self::$is_delete[$value];
    }

    public function getISAuditAttr($value){
        return self::$is_audit[$value];
    }

    public function getDrfatAttr($value){
        return self::$draft[$value];
    }

    public function getPubdateAttr($value){
        return date('Y-m-d H:i:s', $value);
    }

    public function getArcrankAttr($value){
        $Arcrank_arr = explode(',', $value);
        $Arcrank_List = [];
        foreach ($Arcrank_arr as $value){
            $Arcrank_List[] = UserLevel::getField(['id'=>$value], 'level_name', 'id desc', true);
        }
        return implode(',', $Arcrank_List);
    }
}