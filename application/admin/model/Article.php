<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/31
 * Time: 17:39
 * Description：文章数据库模型
 */

namespace app\admin\model;

use app\common\model\Base;
use SucaiZ\Cache\Mysql;
use think\Db;

class Article extends Common
{
    public $table_name = 'article';
    public $table = 'article';
    public $resource_table_name = 'article_resource';
    public $body_table_name = 'article_body';
    public $images_table_name = 'article_images';
    public $affiliate_table_name = [1 => 'article_body', 2 => 'article_images', 3 => 'article_album', 4 => 'article_resource'];

    public function __construct()
    {
        parent::__construct();
    }


    //获取文章列表
    public function getArticleList($where = [], $field = ' * ', $limit = 100, $order = 'id desc')
    {
        $res = Db::name($this->table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //获取文章总条数
    public function getArticleCount($where = [])
    {
        $res = Db::name($this->table_name)->where($where)->count('id');
        return $res;
    }

    //修改文章方法
    public function alterArticleInfo($where = [], $arr = [])
    {
        if (empty($where) || empty($arr)) {
            return false;
        }
        if (isset($where['id'])) {
            return Mysql::update($this->table_name, 'id', $where['id'], $arr);
        } else {
            $res = $this->updateData($where, $arr, 'article', $this->table_name);
            return $res;
        }

    }

    //删除文章方法
    public function delArticle($where = [])
    {
        if (empty($where)) {
            return false;
        }
        if (isset($where['id'])) {
            return Mysql::del($this->table_name, 'id', $where['id']);
        }
        $res = Db::name($this->table_name)->where($where)->delete($where);
        return $res;
    }

    //查询文章信息
    public function getArticleInfo($where = [], $field = ' * ')
    {
        if (empty($where)) {
            return [];
        }
        if (isset($where['id'])) {
            return Mysql::find($this->table_name, 'id', $where['id']);
        } else {
            $res = Db::name($this->table_name)->where($where)->field(' * ')->find();
            return $res;
        }
        $res = Db::name($this->table_name)->where($where)->field(' * ')->find();
        return $res;
    }

    //添加文章方法
    public function addArticle($data = [])
    {
        if (empty($data)) {
            return false;
        }
        $res = Db::name($this->table_name)->insertGetId($data);
        if ($res !== false) {
            return $res;
        } else {
            return false;
        }
    }

    //添加文档资源表方法
    public function addArticleResource($data = [])
    {
        if (empty($data)) {
            return false;
        }
        $res = Db::name($this->resource_table_name)->insertGetId($data);
        if ($res !== false) {
            return true;
        } else {
            return false;
        }
    }

    //添加文档表方法
    public function addArticleBody($data = [])
    {
        if (empty($data)) {
            return false;
        }
        $res = Db::name($this->body_table_name)->insertGetId($data);
        if ($res !== false) {
            return true;
        } else {
            return false;
        }
    }

    //添加文档图集表
    public function addArticleImages($data = [])
    {
        if (empty($data)) {
            return false;
        }
        $res = Db::name($this->images_table_name)->insertGetId($data);
        if ($res !== false) {
            return true;
        } else {
            return false;
        }
    }

    //获取文档附属表信息
    public function getArticleAffiliateInfo($table_name = '', $where = [], $field = ' * ')
    {
        //验证数据
        if (empty($table_name)) {
            return false;
        }
        if (empty($where)) {
            return [];
        }

        //获取数据
        $res = Db::name($table_name)->field($field)->where($where)->find();
        return $res;
    }

    //获取文档附属表列表
    public function getArticleAffiliateList($table_name = '', $where = [], $field = ' * ', $limit = 10000, $order = 'id desc')
    {
        //验证数据
        if (empty($table_name)) {
            return false;
        }
        $res = Db::name($table_name)->field($field)->where($where)->limit($limit)->order($order)->select();
        return $res;
    }

    //更新附属表信息方法
    public function updateArticleAffiliateList($table_name = '', $where = [], $data = [])
    {
        //验证数据
        if (empty($table_name) || empty($where) || empty($data)) {
            return false;
        }
        $res = $this->updateData($where, $data, 'article', $table_name);
        return $res;
    }

    //获取图集文档总条数
    public function getImagesSum($where)
    {
        return Db::name('article_images')->where($where)->sum(' imgnum ');
    }

    //获取文档全部信息
    public function getArticleInfoAll($where = [], $channel = '')
    {
        //判断验证数据
        if (empty($where) || empty($channel)) {
            return [];
        }
        $affiliate_table_name = $this->affiliate_table_name[$channel];

        //查询数据
        $res = Db::name($this->table_name)
            ->alias(' a ')
            ->join(" $affiliate_table_name f ", ' f.article_id = a.id ', ' left ')
            ->field(' *,a.id as article_id ')
            ->where($where)
            ->find();

        return $res;
    }

    //获取图集信息
    public function getArticleImages($where = [], $field = ' * ')
    {
        if (empty($where)) {
            return [];
        }
        return Db::name('article_images')->field($field)->where($where)->find();
    }

    //获取文章列表方法to缓存
    public function getArticleListToCache($where = [], $field = ' * ', $limit = 1000, $order = ' id desc ')
    {
        return $this->getDataListCache($this->table_name, $where, $field, $limit, $order);
    }

    //修改文档评论条数
    public function alterCommentNum($where = [])
    {
        if (empty($where)) {
            return false;
        }
        Db::name($this->table_name)->where($where)->setInc('comment_num', 1);
    }

}