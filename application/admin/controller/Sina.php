<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/30
 * Time: 10:26
 * Description: 新浪微博管理
 */


namespace app\admin\controller;
use app\sina\controller\Api;
use Sina\Sae\SaeTClientV2;
use SucaiZ\config;

use Sina\Sae\SaeTOAuthV2;
use think\Collection;
use think\Request;

class Sina extends Collection
{
    //微博access_token
    public $access_token = null;
    //redis
    private $redis = null;
    //新浪微博api
    private $api = null;
    public function __construct()
    {
        parent::__construct();
        $this->redis = getRedis(3);
        //获取当前访问url地址
        $refresh = Request::instance()->url();
        //获取微博accesstoken
        $this->getAccessToken($refresh);
        //实例化新浪微博api
        $this->api = new Api($this->access_token);
    }
    //获取微博accesstoken
    public function getAccessToken($refresh){
        $code = input('code');
        $this->access_token = $this->redis->get('sina_access_token');
        if(!$this->access_token){
            if(!isset($code)){
                $callback_url = 'http://www.sucai.biz/admin/sina/getAccessToken?refresh='.config::get('cfg_hostsite').$refresh;
                $obj = new SaeTOAuthV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey'));
                $weibo_login_url = $obj->getAuthorizeURL($callback_url);
                header("Location:".$weibo_login_url);
            }else{
                $callback_url = 'http://www.sucai.biz/callback.html?type=weibo';
                $url = "https://api.weibo.com/oauth2/access_token?client_id=". config::get('oauth-sina-appid') ."&client_secret=". config::get('oauth-sina-appkey') ."&grant_type=authorization_code&code=" . $code . "&redirect_uri=".$callback_url;
                $data = [];
                $result = request1($url ,'true' ,'post' ,$data);
                $content = json_decode($result ,true);
                if(isset($content['access_token'])){
                    $this->redis->set('sina_access_token' ,$content['access_token'] ,$content['expires_in']);
                    $refresh = input('refresh');
                    header('Location:'.$refresh);
                }else{
                    echo 'get sina accesstoken error';die;
                }
            }
        }
    }
    //显示微博列表
    public function showList(){
        return View('show_list');
    }
    //显示粉丝列表
    public function showFensList(){
        return View('show_fens_list');
    }
    //获取粉丝列表数据
    public function getFensList(){
        $limit = input('limit');
//        if(!isset($limit) || $limit = ''){
//            echo '非法访问';die;
//        }
//        $num = $limit/5;
        $list = [];
        $api = new Api($this->access_token);
        $list = $api->getFans();
        dump($list);
    }

    //获取微博列表
    public function getWeiBoList(){
        $page = input('page');
        $limit = input('limit');
        $this->api->pushWeiBo("测试微博11". 'https://www.sucai.biz' ,'http://image.sucai.biz/2018-07-31/d20342cd98e9a4aac631eab2d83bc16b.jpg');
    }
    public function author(){
        $sae = new SaeTClientV2(config::get('oauth-sina-appid') ,config::get('oauth-sina-appkey') ,$this->access_token);
        $list = $sae->mentions();
        dump($list);
    }
}