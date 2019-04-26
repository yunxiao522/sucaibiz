<?php


namespace app\timing\command;

use app\timing\model\SinaComment;
use app\common\controller\Url;
use app\timing\model\Article;
use app\timing\model\BackUp;
use SucaiZ\File;
use SucaiZ\config;
use app\common\model\Column;
use app\common\controller\BaseController;
use think\Db;
use think\Exception;
use SucaiZ\Sina;


class Execute
{
    //数据库备份时,每次查询出来的条数,减少大数据量时对内存的消耗,避免报错
    private static $back_up_limit = 20;
    //数据库备份时,查询数据的间隔时间,单位毫秒
    protected static $back_up_usleep = 100;

    public static function test($data)
    {
        dump($data);
        return true;
    }

    /**
     * @param $data
     * @return bool
     * Description 离线下载
     */
    public static function liXianDown($data)
    {
        //获取离线下载的链接
        $url = $data['url'];
        if (!isset($url) || empty($url)) {
            return false;
        }

        //设置用户信息
        File::setUserInfo($data['user_type'], $data['user_id']);

        //获取储存阿里云Oss相关信息
        $bucket = isset($data['bucket']) ? $data['bucket'] : '';
        $object = isset($data['object']) ? $data['object'] : '';
        $ext = isset($data['ext']) ? $data['ext'] : '';

        //设置存储阿里云Oss相关信息
        File::setOssInfo($bucket, $object, $ext);

        //获取保存文件完整名字
        $savename = isset($data['savename']) ? $data['savename'] : '';

        //设置保存文件的名字
        File::$saveName = $savename;

        //获取是否上传阿里云Oss状态
        $oss = config::get('cfg_upload_site') == 1 ? true : false;

        //设置文档内容信息
        $article_id = isset($data['article_id']) ? $data['article_id'] : '';
        $article_title = isset($data['article_title']) ? $data['article_title'] : '';
        File::setArticleInfo($article_id, $article_title);

        //执行离线下载
        return File::liXianDown($url, $oss);
    }

    /**
     * @param $data
     * @return bool
     * Description 文件上传阿里云Oss
     */
    public static function uploadOss($data)
    {

        //设置用户信息
        File::setUserInfo($data['user_type'], $data['user_id']);

        //设置文件完整名称
        File::$filename = $data['file'];

        //设置文件名称
        File::$info['name'] = $data['filename'];

        //设置要存储的阿里云Oss相关信息
        $bucket = isset($data['backet']) ? $data['bucket'] : '';
        $object = isset($data['object']) ? $data['object'] : '';
        $ext = isset($data['ext']) ? $data['ext'] : '';
        File::setOssInfo($bucket, $object, $ext);

        //设置文档内容信息
        File::setArticleInfo($data['article_id'], $data['article_title']);

        //执行数据上传
        return File::uplodOss();

    }

    /**
     * @param $data
     * @return bool
     * @throws \think\Exception
     * Description make or update site ssr.xml
     */
    public static function makeRss($data)
    {
        $column = new Column();
        //查询桌面壁纸和素材资讯
        $column_list = $column->getAll([], 'id,parent_id', 10000);
        $bz_column_list = BaseController::getSonList(1, $column_list);
        $zx_column_list = BaseController::getSonList(24, $column_list);
        $sj_column_list = BaseController::getSonList(54, $column_list);
        $arr = array_merge($bz_column_list, $zx_column_list, $sj_column_list);
        array_push($arr, 1, 24, 54);
        $where = [
            'column_id' => [
                'in',
                $arr
            ],
            'is_delete' => 1,
            'is_audit' => 1
        ];
        $article_list = (new Article())->getAll($where, 'id,title,column_id,pubdate', 100000, true);
        foreach ($article_list as $key => $value) {
            $article_list[$key]['time'] = date('r', $value['pubdate']);
            $article_list[$key]['url'] = (new Url())->getArticleUrl($value['id'], true, true);
        }
        $view = new \think\View();
        $view->share('article', $article_list);
        $html_data = $view->fetch('./templates/default/rss.xml');
        makeHtml($html_data, './', 'rss.xml');
        return true;
    }

    /**
     * @param $data
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     * Description 备份数据库
     */
    public static function backupDatabase($data)
    {
        //获取系统设置的备份目录
        $back_dir = config::get('cfg_mysql_back_path');
        $path = './' . $back_dir;

        //获取数据库数据表
        $database = config('database')['database'];
        $info = "-- ----------------------------\r\n";
        $info .= "-- 日期：" . date("Y-m-d H:i:s", time()) . "\r\n";
        $info .= "-- MySQL - 8.0.1-MariaDB : Database - " . $database . "\r\n";
        $info .= "-- ----------------------------\r\n\r\n";
        $info .= "CREATE DATAbase IF NOT EXISTS `" . $database . "` DEFAULT CHARACTER SET utf8 ;\r\n\r\n";
        $info .= "USE `" . $database . "`;\r\n\r\n";

        // 检查目录是否存在
        if (is_dir($path)) {
            // 检查目录是否可写
            if (is_writable($path)) {
            } else {
                chmod($path, 0777);
            }
        } else {
            // 新建目录
            mkdir($path, 0777, true);
        }

        // 检查文件是否存在
        $name = $database . '-' . date("Y-m-d H:i:s", time()) . '.sql';
        $file_name = $path . $name;
        if (file_exists($file_name)) {
            echo "数据备份文件已存在！";
            exit;
        }
        //组合数据添加到备份表
        $a = [
            'file_name' => $name,
            'create_time' => time(),
            'num' => 0,
            'file_path' => $file_name,
            'status' => 1
        ];
        $id = (new BackUp())->add($a);
        if (!$id) {
            return false;
        }

        file_put_contents($file_name, $info, FILE_APPEND);
        //查询数据库的所有表
        $result = Db::query('show tables');
        foreach ($result as $k => $v) {
            //查询表结构
            $val = $v['Tables_in_' . $database];
            $sql_table = "show create table " . $val;
            $res = Db::query($sql_table);
            $info_table = "-- ----------------------------\r\n";
            $info_table .= "-- Table structure for `" . $val . "`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            $info_table .= "DROP TABLE IF EXISTS `" . $val . "`;\r\n\r\n";
            $info_table .= $res[0]['Create Table'] . ";\r\n\r\n";
            //查询表数据
            $info_table .= "-- ----------------------------\r\n";
            $info_table .= "-- Data for the table `" . $val . "`\r\n";
            $info_table .= "-- ----------------------------\r\n\r\n";
            file_put_contents($file_name, $info_table, FILE_APPEND);
            unset($info_table);
            $sql_count = "select count(*) as count from " . $val;
            $count = Db::query($sql_count)[0]['count'];
            if ($count < 1) continue;
            $offset = 0;
            while ($offset < $count) {
                $sql_data = "select * from " . $val . " LIMIT " . self::$back_up_limit . " OFFSET " . $offset;
                $data = Db::query($sql_data);
                foreach ($data as $key => $value) {
                    $sqlStr = "INSERT INTO `" . $val . "` VALUES (";
                    foreach ($value as $v_d) {
                        $v_d = str_replace("'", "\'", $v_d);
                        $sqlStr .= "'" . $v_d . "', ";
                    }
                    //需要特别注意对数据的单引号进行转义处理
                    //去掉最后一个逗号和空格
                    $sqlStr = substr($sqlStr, 0, strlen($sqlStr) - 2);
                    $sqlStr .= ");\r\n";
                    file_put_contents($file_name, $sqlStr, FILE_APPEND);
                    unset($sqlStr);
                    //间隔一段时间,防止服务器死机
                    usleep(self::$back_up_usleep);
                };
                $offset += self::$back_up_limit;
                unset($data);
            }

            $info = "\r\n";
            file_put_contents($file_name, $info, FILE_APPEND);
        }
        //获取文件存储大小
        $size = getsize($file_name, 'mb');
        //更新备份表信息内容
        $res = (new BackUp())->edit(['id' => $id], ['status' => 2, 'size' => $size]);

        if ($res) {
            //判断是否要上传备份文件到阿里云Oss
            if (config::get('cfg_backup_oss_is') == 1) {
                //组合数据
                $b = [
                    'file' => $file_name,
                    'filename' => $name,
                    'bucket' => 'data-sucaiz',
                    'object' => date('Y-m-d', time()) . '/' . $name,
                    'user_type' => 3,
                    'user_id' => -1,
                    'article_id' => -1,
                    'article_title' => "that's database backup"
                ];
                //添加到Oss上传队列
                task('uploadOss', $b);
                $file_info = $b['bucket'] . ':' . $b['object'];
                //更新备份表数据
                (new BackUp())->edit(['id' => $id], ['file_path' => $file_info, 'is_oss' => 2]);
            }
            return true;
        }
    }

    /**
     * @return bool
     * Description 同步新浪微博的最新评论数据
     */
    public static function syncSinaComment(){
        //设置访问者ip为服务器ip
        $_SERVER['REMOTE_ADDR'] = '115.28.150.229';
        $new_comment_id = (new SinaComment())->getField([], 'comment_id', 'comment_id desc');
        $user_info = Sina::get_uid();
        $list = Sina::comments_to_me(1, 50,$new_comment_id);
        foreach ($list['comments'] as $value) {
            $arr = [
                'comment_id' => $value['id'],
                'content' => $value['text'],
                'user_id' => $value['user']['id'],
                'weibo_id' => $value['status']['id'],
                'create_time' => strtotime($value['created_at']),
                'status'=>1
            ];
            (new SinaComment())->add($arr);
        }
        return true;
    }

}