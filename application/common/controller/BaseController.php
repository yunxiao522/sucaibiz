<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2019/1/6 0006
 * Time: 15:15
 */

namespace app\common\controller;

use SucaiZ\config;
use think\Controller;
use think\Request;
use SucaiZ\Pinyin\ChinesePinyin;
use think\Validate;

class BaseController extends Controller
{
    private static $article_state_url = '/article.html';
    private static $column_state_url = '/column.html';

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        self::validateRule();
    }

    public function ajaxOk($msg = null, $url = null, $code = 0)
    {
        $data = [
            'msg' => $msg,
            'success' => true,
            'status' => 'success',
            'url' => $url,
            'errorcode' => 0,
            'code' => $code
        ];
        return $this->ajaxReturn($data);
    }

    public function ajaxError($msg = null, $url = null, $code = 1)
    {
        $data = [
            'msg' => $msg,
            'success' => false,
            'status' => 'fail',
            'url' => $url,
            'errorcode' => 1,
            'code' => $code
        ];
        return $this->ajaxReturn($data);
    }

    public function ajaxOkdata($data = null, $msg = '', $url = '', $code = 0)
    {
        $data = [
            'msg' => $msg,
            'url' => $url,
            'success' => true,
            'status' => 'success',
            'data' => $data,
            'code' => $code
        ];
        return $this->ajaxReturn($data);
    }

    public function ajaxErrordata($data = null, $msg = '', $url = '', $code = 1)
    {
        $data = [
            'msg' => $msg,
            'url' => $url,
            'success' => false,
            'status' => 'fail',
            'data' => $data,
            'code' => $code
        ];
        return $this->ajaxReturn($data);
    }

    public function ajaxReturn($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param int $aid 文档id
     * @param int $page 第几页
     * @param bool $state 是否生成静态链接
     * @param bool $full 是否生成全链接(包含域名)
     * @return string 返回访问链接
     * Description 获取文档访问链接
     */
    public static function getArticleUrl($aid = 0, $page = 0, $state = false, $full = false)
    {
        if ($aid == 0 || !is_numeric($aid)) {
            return '';
        }
        //判断获取的链接属性
        if ($state) {
            $article_info = Model('article')->getOne(['id' => $aid]);
            //判断是否单独设置了文件路径
            if (empty($article_info['redirecturl'])) {
                $column_info = Model('column')->getOne(['id' => $article_info['column_id']]);
                //将文档命名规则字符串处理成小写
                $namerule = strtolower($column_info['namerule']);
                //取出年月日和文档id存入数组
                $name_info = [
                    '{y}' => date('Y', $article_info['pubdate']),
                    '{m}' => date('m', $article_info['pubdate']),
                    '{d}' => date('d', $article_info['pubdate']),
                    '{aid}' => $aid,
                ];
                if (!$page) {
                    $name_info['_{page}'] = '';
                } else {
                    $page++;
                    $name_info['_{page}'] = "_$page";
                }
                //循环替换文档名规则内容
                foreach ($name_info as $key => $value) {
                    $namerule = str_replace($key, $value, $namerule);
                }
                //组合文档访问url
                $file = $column_info['type_dir'] . $namerule;
            } else {
                $file = $article_info['redirecturl'];
            }
            if ($full) {
                return config::get('cfg_hostsite') . rtrim($file, '/');
            } else {
                return rtrim($file, '/');
            }
        } else {
            if ($full) {
                return config::get('cfg_hostsite') . self::$article_state_url . '?id=' . $aid;
            } else {
                return self::$article_state_url . '?id=' . $aid;
            }
        }
    }

    /**
     * @param int $cid 栏目id
     * @param bool $state 是否生成静态链接
     * @param bool $full 是否生成全连接（包含访问域名）
     * @return string 返回生成的链接
     * Description 获取栏目访问链接
     */
    public static function getColumnUrl($cid = 0, $state = false, $full = false)
    {
        if ($cid == 0 || !is_numeric($cid)) {
            return '';
        }
        //判断获取的链接属性
        if ($state) {
            //根据栏目id获取栏目信息
            $column_info = Model('column')->getOne(['id' => $cid]);
            //组合栏目访问url
            $file = $column_info['type_dir'] . '/' . $column_info['defaultname'];
            if ($full) {
                return config::get('cfg_hostsite') . $file;
            } else {
                return $file;
            }
        } else {
            if ($full) {
                return config::get('cfg_hostsite') . self::$column_state_url . '?id=' . $cid;
            } else {
                return self::$column_state_url . '?id=' . $cid;
            }
        }
    }

    /**
     * @param int $tagid tag标签id
     * @param bool $state 是否生成静态链接
     * @param bool $full 是否生成全链接(包含域名)
     * @return string 返回生成的链接
     * Description 获取tag标签访问链接
     */
    public static function getTagUrl($tagid = 0, $state = false, $full = false)
    {
        if ($tagid == 0 || !is_numeric($tagid)) {
            return '';
        }
        //判断获取的链接属性
        if ($state) {
            //根据tagid获取tag信息
            $chinesepinyin = new ChinesePinyin();
            $tag_info = Model('tag')->getOne(['id' => $tagid]);
            //获取tag标签汉语拼音字符串
            $tag_name = $tag_info['tag_name'];
            $tag_name = $chinesepinyin->TransformWithoutTone($tag_name, '');
            //组合tag访问的url
            $file = '/tag/' . $tag_name . '.html';
            if ($full) {
                return config::get('cfg_hostsite') . $file;
            } else {
                return $file;
            }

        } else {
            if ($full) {
                return config::get('cfg_hostsite') . '/tags?id=' . $tagid;
            } else {
                return '/tags?id=' . $tagid;
            }
        }
    }

    /**
     * @param $html_data 生成静态文件需要的数据
     * @param $html_file_dir 生成静态文件的目录
     * @param $html_file_name 生成静态文件的全名
     * @return bool@param 返回生成文件的结果
     * Description 生成静态文件方法
     */
    public function Html($html_data, $html_file_dir, $html_file_name)
    {
        //判断目录是否存在，不存在则创建目录
        if (!file_exists($html_file_dir)) {
            $res = mkdir($html_file_dir, 0777, true);
            if (!$res) {
                return false;
            }
        }
        //组合静态文件全路径
        $html_file = $html_file_dir . '/' . $html_file_name;
        //创建一个文件，并打开，准备写入
        $fp = fopen($html_file, "w");
        //把php页面的内容全部写入html文件
        $res = fwrite($fp, $html_data);
        fclose($fp);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $parent_id 查询的栏目id
     * @param $column_list 栏目列表数据
     * @return array
     * Description 递归获取父级栏目列表
     */
    public static function getParentColumn($parent_id, $column_list)
    {
        $parent_arr = [];
        foreach ($column_list as $value) {
            if ($value['id'] == $parent_id) {
                $parent_arr[] = $value['id'];
                self::getParentColumn($value['parent_id'], $column_list);
            }
        }
        return $parent_arr;
    }

    /**
     * @param $son_id 查询的栏目id
     * @param $column_list 栏目列表数据
     * @return array
     * Description 递归获取子栏目列表
     */
    public static function getSonList($son_id, $column_list)
    {
        $son_arr = [];
        foreach ($column_list as $value) {
            if ($value['parent_id'] == $son_id) {
                $son_arr[] = $value['id'];
                self::getSonList($value['id'], $column_list);
            }
        }
        return $son_arr;
    }

    /**
     * Description 添加自定义验证规则
     */
    public static function validateRule()
    {
        Validate::extend('is_email', function ($value) {
            $email_rule = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
            if(preg_match($email_rule, $value) == 0){
                return '输入的邮箱格式不正确';
            }else{
                return true;
            }
        });
        Validate::extend('is_phone', function ($value) {
            $phone_rule = '/^1[345678]\d{9}$/';
            if(preg_match($phone_rule, $value) == 0){
                return '输入的手机号码格式不正确';
            }else{
                return true;
            }
        });
    }

    /**
     * @param $rule
     * @param $msg
     * @param $callback
     * @return array|string|true
     * Description 验证表单数据,并返回数据
     */
    public function checkForm($rule, $msg, $callback){
        $result = $this->validate(input(), $rule, $msg);
        if(true !== $result){
            return $result;
        }
        return $callback(input());
    }
}