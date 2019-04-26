<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/12
 * Time: 16:44
 */
//admin应用生成密码
/**
 * @param $string
 * @return string
 */
function getAdminPassword($string)
{
    $password = md5(md5($string) . '_sucaiz');
    return $password;
}

//文章分类列表排序
/**
 * @param $data
 * @param int $reid
 * @param int $lev
 * @return array
 */
function getarticletype($data, $parent_id = 0, $lev = 0)
{
    static $arr = array();
    foreach ($data as $key => $value) {
        if ($value['parent_id'] == $parent_id) {
            $value['lev'] = $lev;
            $arr[] = $value;
            getarticletype($data, $value['id'], $lev + 1);
        }
    }
    return $arr;
}

//根据栏目id查询是否存在子栏目
function findchildcolumn($column_list, $category_id)
{
    $category_ids = $category_id . ",";
    $child_category = [];
    foreach ($column_list as $k => $v) {
        if ($v['parent_id'] == $category_id) {
            $child_category[] = $v;
        }
    }
    foreach ($child_category as $key => $val) {
        $category_ids .= findchildcolumn($column_list, $val["id"]);
    }
    return $category_ids;
}



function getArticleToColumn($column_id)
{
    return \app\common\controller\Article::getArticleList(['column_id'=>$column_id],'*',20,true);
}

//获取图集颜色列表
function getImagesColorList()
{
    $color = new \app\index\model\Images();
    return $color->getImagesColor();
}

//获取图集屏幕尺寸列表
function getImagesScrnList()
{
    $color = new \app\index\model\Images();
    return $color->getImagesScrn();
}

//获取子栏目列表
function getSonColumn($column_id, $field = ' * ')
{
    $column = new \app\index\model\Column();
    $son_list = $column->getColumnList(['parent_id' => $column_id], ' * ', 1000, ' id asc ');
    //循环设置访问链接
    foreach ($son_list as $key => $value) {
        $son_list[$key]['url'] = $value['type_dir'] . '/' . $value['defaultname'];
    }
    return $son_list;
}

//获取同级栏目列表
function getColumn($parent_id)
{
    $column = new \app\index\model\Column();
    $column_list = $column->getColumnList(['parent_id' => $parent_id], ' * ', 1000, ' id asc ');
    //循环设置访问链接
    foreach ($column_list as $key => $value) {
        $column_list[$key]['url'] = $value['type_dir'] . '/' . $value['defaultname'];
    }
    return $column_list;
}

//获取文档tag标签
function getArticleTag($article_id)
{
    $tag = new \app\index\model\Tag();
    //查询文档的tag列表
    return $tag->getTagListInfo(['l.article_id' => $article_id], ' t.id,t.tag_name as name ');
}

//获取文档位置
function getArticleSite($column_id, $article_title)
{

//获取文档所属栏目及父级栏目信息
    $column = new \app\index\model\Column();
    $column_list = $column->getColumnList([], ' id,parent_id,type_name,type_dir,defaultname ');

    //使用redis,减少递归查询次数
    $redis = getRedis();
    $key = 'parent_column_' .$column_id;
    $column_arr_string = $redis->get($key);
    if($column_arr_string == ''){
        $column_arr = getParentColumn($column_list, $column_id);
        $redis->set($key ,json_encode($column_arr ,JSON_UNESCAPED_UNICODE));
    }else{
        $column_arr = json_decode($column_arr_string ,true);
    }

    //添加首页和文档到数组中
    krsort($column_arr);
    array_unshift($column_arr, '<a href="/" title="首页">首页</a>');
    array_push($column_arr, $article_title);

    //文档位置字符串
    return implode(SucaiZ\config::get('cfg_list_symbol'), $column_arr);
}

function getParentColumn($column_list, $column_id)
{


    $column_arr = [];
    foreach ($column_list as $key => $value) {
        if ($value['id'] == $column_id) {
            $column_path = rtrim(str_replace('{cmspath}', '', $value['type_dir']), '/') . '/';
            $url = $column_path . $value['defaultname'];
            $column_arr[] = "<a href='$url' title='$value[type_name]'>$value[type_name]</a>";
            $value['type_name'];
            if ($value['parent_id'] != 0) {
                getParentColumn($column_list, $value['parent_id']);
            }
        }
    }


    return $column_arr;
}

//获取相关文档
function getConcernArticleList($article_id, $column_id)
{
    //获取本栏目下相关文档
    $index = new \app\index\controller\Index();
    $related = $index->getArticleList(['column_id' => $column_id], 'id,title,litpic', '20', 'id desc');

    //删除该文档信息
    foreach ($related as $key => $value) {
        if ($value['id'] == $article_id) {
            unset($related[$key]);
            break;
        }
    }
    return $related;
}

//获取随机文档
function getGuessArticle($column_id)
{
    $article = new \app\admin\model\Article();
    //获取同级文大部分列表
    $peer_article = $article->getArticleList(['column_id' => $column_id], ' id ', 100);
    $peer_article_list = [];
    foreach ($peer_article as $value) {
        $peer_article_list[] = $value['id'];
    }
    $guess_list = array_rand($peer_article_list, 20);
    $guess_article = [];
    foreach ($guess_list as $value) {
        //构建参数查询文档信息
        $w = ['id' => $value];
        $guess_article[] = $article->getArticleInfo($w, ' id,title,litpic,pubdate ');

    }
    return $guess_article;
}

//获取热门内容
function hot($type = '', $column_id = '', $is_son = false)
{
    //验证数据
    if (empty($type) || empty($column_id)) {
        return [];
    }
    //判断是否要查询子栏目内容
    if (!$is_son) {
        $where = ['column_id' => $column_id];
    } else {
        //获取栏目及子栏目
        $column_list = getSonColumn($column_id, ' id ');
//        $column_list['id'] = $column_id;
        //获取文档数据
        //循环拼接查询条件
        $where = '';
        foreach ($column_list as $value) {
            $where .= " column_id = $value[id] or ";
        }

        //去掉多余的or
        $where = rtrim($where, 'or ');
    }
    $tag = new \app\index\model\Tag();
    $index = new \app\index\controller\Index();
    //判断请求类型
    if (strtolower($type) == 'tag') {
        //查询热门tag标签
        return $tag->getTagList($where, ' id,tag_name ', 100, ' count desc ');
    } else if (strtolower($type) == 'article') {
        //查询热门文档
        return $index->getArticleList($where, ' * ', 20, ' click desc ');
    }
}

//获取图集内容
function images($type = '', $article_id = '')
{
    //验证数据
    if (empty($type) || empty($article_id)) {
        return [];
    }
    //实例化文档模型
    $article = new \app\admin\model\Article();
    //实例化栏目模型
    $column = new \app\admin\model\Column();
    //匹配src图片路径方法
    $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
    //判断类型
    if (strtolower($type) == 'smalllist') {
        //获取文档信息
        $article_info = $article->getArticleInfoAll(['a.id' => $article_id], 2);
        //获取栏目信息
        $column_info = $column->getColumnInfo(['id' => $article_info['column_id']], ' type_dir,namerule ');
        $smallimgurls = explode(',', $article_info['smallimgurl']);
        $smallimgurls_list = [];

        //取出年月日和文档id存入数组
        $name_info = [
            '{y}' => date('Y', $article_info['pubdate']),
            '{m}' => date('m', $article_info['pubdate']),
            '{d}' => date('d', $article_info['pubdate']),
            '{aid}' => $article_id,
        ];
        $page = 1;
        //循环存储数据
        foreach ($smallimgurls as $value) {
            $namerule = strtolower($column_info['namerule']);
            //取出图像地址
            preg_match($src_rule, $value, $match);
            $a['imgurl'] = $match[3];
            //处理访问地址
            if ($page == 1) {
                $name_info['_{page}'] = '';
            } else {
                $name_info['_{page}'] = '_' . $page;
            }
            //循环替换文档名规则内容

            foreach ($name_info as $key => $value) {
                $namerule = str_replace($key, $value, $namerule);
            }
            //拼接访问路径
            $a['url'] = $column_info['type_dir'] . $namerule;
            $smallimgurls_list[] = $a;
            $page++;
        }
        return $smallimgurls_list;
    } else if (strtolower($type)) {

    }
}

function getImgUrl($str)
{
    //匹配src图片路径方法
    $src_rule = "/(href|src)=([\"|']?)([^\"'>]+.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG))/i";
    preg_match($src_rule, $str, $match);
    return $match[3];
}

function article($type = 'list', $column = '', $length = 20)
{

    //判断获取类型
    if ($type == 'list') {
        //获取本栏目下相关文档
        $index = new \app\index\controller\Index();
        $make = new \app\admin\controller\Make();

        //获取子栏目列表查询条件
        $where = $make->getWhere($column);
        $where = " ( $where ) and is_delete=1";

        //查询文档列表
        return $index->getArticleList($where, 'id,title,litpic', $length, 'id desc');
    }

}

//获取栏目数据
function nav($type = '', $column, $static = false, $length = 20)
{
    $make = new \app\admin\controller\Make();
    return $make->getNav($type, $column, $length, $static);
}

//获取当前位置
function site($column_id, $column_title = '')
{
    $make = new \app\admin\controller\Make();
    return $make->getCrumbs($column_id, $column_title);
}

//获取文档链接
function geturl($pubdate, $article, $page = 1, $column_id)
{
    $column = new \app\admin\model\Column();
    //获取文档所属栏目信息
    $column_info = $column->getColumnInfo(['id' => $column_id], ' type_dir,namerule ');
    //获取要保存的文件名规则
    $namerule = strtolower('/{Y}{M}{D}{aid}_{page}.html');

    //获取文档的创建时间
    $create_time = $pubdate;

    //取出年月日和文档id存入数组
    $name_info = [
        '{y}' => date('Y', $create_time),
        '{m}' => date('m', $create_time),
        '{d}' => date('d', $create_time),
        '{aid}' => $article,

    ];
    if ($page == 1) {
        $name_info['_{page}'] = '';
    } else {
        $name_info['_{page}'] = '_' . $page;
    }

    //循环替换文档名规则内容
    foreach ($name_info as $key => $value) {
        $namerule = str_replace($key, $value, $namerule);
    }
    return $column_info['type_dir'] . $namerule;
}

//tag列表获取
function tag($type = 'column', $num = '', $down = false)
{
    if ($type == 'column') {
        //判断参数
        if (empty($num)) {
            return [];
        }
        //判断是否查询子集

    } else if ($type == 'article') {

    }
}


