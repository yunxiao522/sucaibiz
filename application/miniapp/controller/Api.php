<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/13
 * Time: 0:59
 * Description:
 */


namespace app\miniapp\controller;


use think\Controller;

class Api extends Controller
{
    public function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = 'sucaiz';
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            echo input('echostr');
        }else{
            echo false;
        }
    }
}