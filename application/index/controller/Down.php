<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/7/5
 * Time: 21:04
 * Description: 下载文件控制器
 */


namespace app\index\controller;

use SucaiZ\File;
use think\Db;
use think\View;

class Down extends Common
{
    public function image()
    {
        echo '';
    }

    public function index()
    {
        //验证数据
        $aid = input('aid');
        if (!isset($aid) || empty($aid) || !is_numeric($aid)) {
            echo '非法访问';
            die;
        }
        $uid = input('uid');
        if(!isset($uid) || empty($uid) || !is_numeric($uid)){
            echo '非法访问';die;
        }

        //获取文档内容
        $article = new \app\index\model\Article();
        $article_info = $article->getArticleInfoAll(['a.id' => $aid], 2);
        $article_info['create_time'] = date('Y-m-d H:i:s', $article_info['create_time']);
        $file_info = [
            'ext' => File::getRemoteFileExt($article_info['packurl']),
            'name' => File::getRemoteFileName($article_info['packurl']),
            'size' => tosize(File::getRemoteFileSize($article_info['packurl']))
        ];
//        $article_info['packurl'] = str_replace('http:' ,'' ,$article_info['packurl']);
        View::share('file_info', $file_info);
        View::share('article_info', $article_info);
        View::share('aid', $aid);
        View::share('uid' ,$uid);
        return View('./templates/down_index');
    }

    //修改下载次数方法
    private function UpdateDownNum($info)
    {
        //组合条件，查询是否已经存在相关信息
        $where = ['article_id' => $info['article_id'], 'page' => $info['page'], 'token' => $info['token']];
        $down = new \app\index\model\Down();
        $count = $down->getDownCount($where);
        if ($count == 0) {
            return $down->insertDownInfo($info);
        } else {
            return $down->incrDownNum($where);
        }
    }

    /**
     * @return false|string
     * Description 下载文件方法
     */
    public function down()
    {
        //获取并验证前台数据
        $aid = input('aid');
        if (!isset($aid) || empty($aid) || !is_numeric($aid)) {
            return $this->ajaxError('参数错误');
        }

        $url = input('url');
        if (!isset($url) || empty($url)) {
            return $this->ajaxError('参数错误');
        }
        $page = input('page');
        if (isset($page) && !is_numeric($page)) {
            return $this->ajaxError('参数错误');
        }
        //获取文件后缀
        $ext = File::getRemoteFileExt($url);
        if (in_array($ext, ['zip', 'rar', '7z'])) {
            $page = 999;
        }
        if(empty($page)){
            $page = 0;
        }
        $type = input('type');
        if(empty($type)){
            $type = 'blob';
        }
        //查询文档信息
        $article_info = Model('article')->getOne(['id' => $aid], ' token,column_id ');
        //组合条件查询判断该文件是否被下载过
        $where = [
            'article_id'=>$aid,
            'page'=>$page
        ];
        $down_info= Model('down')->getOne($where,'id,num');
        Db::startTrans();
        if(!empty($down_info)){
            $res = Model('down')->edit([
                'id'=>$down_info['id']
            ],[
                'num'=>$down_info['num']+1,
                'end_time'=>time()
            ]);
        }else{
            $res = Model('down')->add([
                'article_id'=>$aid,
                'num'=>1,
                'token'=>$article_info['token'],
                'source_file'=>$url,
                'page'=>$page,
                'end_time'=>time()
            ]);
        }
        if(!$res){
            Db::rollback();
            return $this->ajaxError('获取下载地址失败');
        }
        //判断用户是否登录
        if($this->uid != 0){
            //构建查询条件，判断是否已经下载过该文件
            $where = [
                'uid'=>$this->uid,
                'article_id'=>$aid,
                'file_url'=>$url
            ];
            $my_down_info = Model('MyDown')->getField($where,'uid','');
            if(empty($my_down_info)){
                $res = Model('MyDown')->add([
                    'article_id'=>$aid,
                    'file_type'=>File::getRemoteFileExt($url),
                    'file_size'=>File::getRemoteFileSize($url),
                    'uid'=>$this->uid,
                    'file_url'=>$url,
                    'create_time'=>time(),
                    'column_id'=>$article_info['column_id']
                ]);
                if(!$res){
                    Db::rollback();
                    return $this->ajaxError('获取下载地址失败');
                }
            }
        }
        Db::commit();
        //获取文件mini
        $mini = getFileMimeArray()[$ext];
        //组合header内容
        $header_content = "Content-Type:$mini;text/html; charset=utf-8";
        header($header_content);

        echo file_get_contents($url);
    }

    //下载图片方法
    public function downimg(){
        //验证数据
        $url = input('url');
        if(!isset($url) || empty($url)){
            echo '非法访问';die;
        }
        //强行跳转页面
    }

    /**
     * 将字符串转换成二进制
     * @param type $str
     * @return type
     */
    public function StrToBin($str){
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach($arr as &$v){
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ',$arr);
    }
}