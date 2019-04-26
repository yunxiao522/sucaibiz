<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/2/11
 * Time: 11:10
 */
namespace app\sina\controller;
use Sina\Sae\SaeTClientV2;
use SucaiZ\config;
use think\Controller;
use think\Request;

class Api extends Controller{

    //储存access_token;
    private $access_token;
    //储存redis实例
    private $redis;
    public function __construct($access_token)
    {
        parent::__construct();
        $this->access_token = $access_token;
        $this->redis = getRedis(3);
    }

    //获取用户发布的微博
    public function getUserWeiBo($uid = null ,$screen_name = null ,$since_id = null ,$max_id = null ,$count = 10 ,$page = 1 ,$base_app = 0, $feature = 0 ,$trim_user = 0){
        $url = 'https://api.weibo.com/2/statuses/user_timeline.json';
        $arr = [
            'access_token'=>$this->access_token,
            'uid'=>$uid,
            'screen_name'=>$screen_name,
            'since_id'=>$since_id,
            'max_id'=>$max_id,
            'count'=>$count,
            'page'=>$page,
            'base_app'=>$base_app,
            'feature'=>$feature,
            'trim_user'=>$trim_user
        ];
        $content = $this->request($url ,'true' ,'get' ,$arr);
        return json_decode($content ,true);
    }
    //获取当前用户的uid
    public function getUserUid(){
        $url = 'https://api.weibo.com/2/account/get_uid.json';
        $arr = [
            'access_token'=>$this->access_token

        ];
        $content = $this->request($url ,'true' ,'get' ,$arr);
        $result = json_decode($content ,true);
        if(isset($result['uid'])){
            return $result['uid'];
        }else{
            echo 'get uid error';die;
        }
    }
    //微博长地址转短地址
    public function urlShorten($long){
        $url = 'https://api.weibo.com/2/short_url/shorten.json';
        $arr = [
            'access_token'=>$this->access_token,
            'url_long'=>$long
        ];
        $content = $this->request($url ,'true' ,'get' ,$arr);
        $result = json_decode($content ,true);
        if(isset($result['urls']) && !empty($result['urls'])){
            return $result['urls'][0]['url_short'];
        }else{
            echo 'get  url_short error';die;
        }
    }
    //微博短地址转长地址
    public function urlExpand($short){
        $url = 'https://api.weibo.com/2/short_url/expand.json';
        $arr = [
            'access_token'=>$this->access_token,
            'url_short'=>$short
        ];
        $content = $this->request($url ,'true' ,'get' ,$arr);
        $result = json_decode($content ,true);
        if(isset($result['urls']) && !empty($result['urls'])){
            return $result['urls'][0]['url_long'];
        }else{
            echo 'get url_long error';die;
        }
    }
    //发送新浪微博
    public function pushWeiBo($text ,$pic = null){
        $url = 'https://api.weibo.com/2/statuses/update.json';

        $sae = new SaeTClientV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey') ,$this->access_token);
        dump($sae->share(cut_str($text ,135) ,$pic,Request::instance()->ip()));
    }
    //获取微博粉丝列表
    public function getFans($cursor = 0 , $count = 5){

    }
    //获取账号评论信息
    public function getCommentList($since_id = 0,$max_id = 0,$count = 50,$page = 1,$filter_by_author=0){
        $sae = new SaeTClientV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey') ,$this->access_token);
        $sae->comments_timeline($page,$count,$since_id,$max_id);
    }
    //获取sae实例
    public function getSae(){
        return new SaeTClientV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey') ,$this->access_token);
    }
    //发送请求方法
    function request($url, $https = true, $method = 'get', $data = null ,$file = [])
    {
        //满足get
        if($method === 'get'){
            $url = $url.'?'.http_build_query($data);
        }
        //1.初始化
        $ch = curl_init($url);
        //2.设置curl
        //返回数据不输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //设置header
//        curl_setopt($ch, CURLOPT_HTTPHEADER, ['content-Type:application/x-www-form-urlencoded']);
        //满足https
        if ($https == true) {
            //绕过ssl验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //满足post
        if ($method === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //满足上传文件
        if(!empty($file)){
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $file );
        }

//    curl_setopt($ch, CURLOPT_NOSIGNAL, true);    //注意，毫秒超时一定要设置这个
//    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100); //超时时间200毫秒
        //3.发送请求
        $content = curl_exec($ch);
        //4.关闭资源
        curl_close($ch);
        return $content;
    }

    public static function __callStatic($name, $arguments)
    {
        new SaeTClientV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey') ,(new static())->access_token);
        // TODO: Implement __callStatic() method.
    }
}