<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/5/13
 * Time: 16:03
 * Description:
 */

use SucaiZ\config;
use SucaiZ\Kafka;

if(!function_exists('task')){
    function task($name ='' ,$value =''){
        $kafuka = new Kafka();
        $kafuka->broker_list=config::get('cfg_kafka_host');
        if(empty($name)||empty($value)){
            return false;
        }
        $kafuka->Producer([
            'fun'=>$name,
            'agrs'=>$value
        ]);
        return true;
    }
}
 
 