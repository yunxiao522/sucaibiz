<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/2/10
 * Time: 20:58
 */
namespace app\wechat\controller;
use think\Controller;
use SucaiZ\config;
use Tencent\Wechat\WXBizMsgCrypt;

class Api extends Controller
{
    public $wechat_access_token;
    public $wechat_appid;
    public $wechat_secret;
    public function __construct()
    {
        parent::__construct();
        $this->wechat_appid = config::get('cfg_wechat_appid');
        $this->wechat_secret = config::get('cfg_wechat_appkey');
    }

    public function checkSignature()
    {
        $xmldata=file_get_contents("php://input");
        $redis = getRedis(5);
        $data=simplexml_load_string($xmldata ,'SimpleXMLElement' , LIBXML_NOCDATA);
        $array = get_object_vars($data);
        $a = json_encode($array ,JSON_UNESCAPED_UNICODE);
        $redis->set('test' ,$a);

        $formusername = $array['FromUserName'];
        $tousername = $array['ToUserName'];
        //判断消息类型
        if($array['MsgType'] == 'text'){
            $xml1 = $this->textString();
            $xml = sprintf($xml1, $formusername, $tousername, time(), '你好，欢迎光临素材站');
        }

        return $xml;
    }

    //接收微信服务器验证请求
    public function check(){
        $signature = input("signature");
        $timestamp = input("timestamp");
        $nonce = input("nonce");
        $echostr = input('echostr');
        $mssage_token = config('MESSAGE_TOKEN');

        $tmpArr = array($mssage_token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($signature == $tmpStr) {
            echo $echostr;
        }
        echo 'sucaiz';
    }

    private function textString(){
        return '<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[%s]]></Content></xml>';
    }

    private function imageTextString(){
        return '<xml><ToUserName><![CDATA[toUser]]></ToUserName><FromUserName><![CDATA[fromUser]]></FromUserName><CreateTime>12345678</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>2</ArticleCount><Articles><item><Title><![CDATA[title1]]></Title><Description><![CDATA[description1]]></Description><PicUrl><![CDATA[picurl]]></PicUrl><Url><![CDATA[url]]></Url></item><item><Title><![CDATA[title]]></Title><Description><![CDATA[description]]></Description><PicUrl><![CDATA[picurl]]></PicUrl><Url><![CDATA[url]]></Url></item></Articles></xml>';
    }
}