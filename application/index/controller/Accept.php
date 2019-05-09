<?php

namespace app\index\controller;

use SucaiZ\config;

class Accept
{
    public function sina(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $appsecret= config::get('oauth-sina-appid');  //开发者的appsecret
        $tmpArr = array($appsecret, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            echo input('echostr');
        }else{
            echo input('echostr');
        }
        $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true);
        if (!empty($data)) {
            //sender_id为发送回复消息的uid，即蓝v自己
            $sender_id = $data['receiver_id'];
            //receiver_id为接收回复消息的uid，即蓝v的粉丝
            $receiver_id = $data['sender_id'];

            //回复text类型的消息示例。
            $data_type = "text";
            $data = $this->textData("text消息回复测试");

            //回复articles类型的消息示例。
            //    $data_type = "articles";
            //    $article_data = array(
            //        array("display_name" => "第一个故事",
            //            "summary" => "今天讲两个故事，分享给你。谁是公司？谁又是中国人？",
            //            "image" => "http://storage.mcp.weibo.cn/0JlIv.jpg",
            //            "url" => "http://e.weibo.com/mediaprofile/article/detail?uid=1722052204&aid=983319"),
            //        array("display_name" => "第二个故事",
            //            "summary" => "今天讲两个故事，分享给你。谁是公司？谁又是中国人？",
            //            "image" => "http://storage.mcp.weibo.cn/0JlIv.jpg",
            //            "url" => "http://e.weibo.com/mediaprofile/article/detail?uid=1722052204&aid=983319")
            //    );
            //    $data = $call_back_SDK->articleData($article_data);

            //回复position类型的消息示例。
            //    $data_type = "position";
            //    $longitude = "123.01";
            //    $latitude = "154.2";
            //    $data = $call_back_SDK->positionData($longitude, $latitude);

            $str_return = $this->buildReplyMsg($receiver_id, $sender_id, $data, $data_type);
        }
        echo json_encode($str_return);
    }
    function textData($text) {
        return $data = array("text" => $text);
    }
    function buildReplyMsg($receiver_id, $sender_id, $data, $type) {
        return $msg = array(
            "sender_id" => $sender_id,
            "receiver_id" => $receiver_id,
            "type" => $type,
            //data字段需要进行urlencode编码
            "data" => urlencode(json_encode($data))
        );
    }
    public function wechat(){

    }
}