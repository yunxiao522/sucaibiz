<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/1/3
 * Time: 13:00
 */
function getSonComment($list ,$id  ,$tier ,$i){
    static $arr = [];
    foreach($list as $value){
        if($value['parent_id'] == $id){
            getSonComment($list ,$value['id'] ,$tier ,$i);
            $value['level']=$tier ."楼#" .$value['tier'];
            $arr[$i][] = $value;
        }
    }
    return $arr;
}