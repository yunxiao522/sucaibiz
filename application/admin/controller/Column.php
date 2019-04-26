<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/12/12
 * Time: 20:16
 * Description：栏目管理
 */
namespace app\admin\controller;

use think\Request;
use app\admin\model\Column as c;
use SucaiZ\File;
use think\Session;
use think\View;

class Column extends Common{
    //储存column模型
    private $column;
    //验证规则
    protected $rule = [
        'parent_id'=>'require|number',
        'type_name'=>'require|max:30|min:1',
        'channel_type'=>'require',
        'sort_rank'=>'require|number',
        'like'=>'require|array',
        'keywords'=>'require|max:100',
        'type_dir'=>'require|max:100',
        'defaultname'=>'require|max:20',
        'default_index'=>'require|max:50',
        'templist'=>'require|max:60',
        'temparticle'=>'require|max:60',
        'namerule'=>'require|max:60',
        'listrule'=>'require|max:60',
        'modename'=>'require|max:60',
        'description'=>'max:200',
        'cover_url'=>'require|max:100'
    ];
    //验证规则信息
    protected $msg = [
        'parent_id.require'=>'请选择父级栏目',
        'parent_id.number'=>'父级栏目id必须是数字',
        'type_name.require'=>'缺少参数',
        'type_name.max'=>'栏目名称不能超过30个字符',
        'type_name.min'=>'栏目名称不能为空',
        'channel_type.require'=>'缺少参数',
        'sort_rank.number'=>'栏目顺序只能为数字',
        'sort_rank.require'=>'缺少参数',
        'like.array'=>'参数错误',
        'keywords.require'=>'参数错误',
        'keywords.max'=>'关键词不能超过100个字符',
        'type_dir.require'=>'参数错误',
        'type_dir.max'=>'关键词不能超过100个字符',
        'defaultname.require'=>'缺少参数',
        'defaultname.max'=>'首页名称不能超过20个字符',
        'default_index.require'=>'缺少参数',
        'default_index.max'=>'模板封面不能超过60个字符',
        'templist.require'=>'缺少参数',
        'templist.max'=>'列表封面不能超过60个字符',
        'temparticle.require'=>'缺少参数',
        'temparticle.max'=>'文章封面不能超过60个字符',
        'namerule.require'=>'缺少参数',
        'namerule.max'=>'文章命名规则不能超过60个字符',
        'listrule.require'=>'缺少参数',
        'listrule.max'=>'列表命名规范不能超过60个字符',
        'modename.require'=>'缺少参数',
        'modename.max'=>'模板名称不能超过60个字符',
        'description.max'=>'栏目介绍不能超过200个字符',
        'cover_url.require'=>'缺少参数',
        'cover_url.max'=>'封面地址不能超过100个字符'
    ];
    public function __construct()
    {
        parent::__construct();
        $this->column = new c();
    }

    public function addColumn(){
        if(Request::instance()->isGet()){
            $parent_id = input('parent_id');
            $channeltype = model('Channeltype');
            $channel_type_data = $channeltype->getList(['enable'=>1],' typename,id ');
            $this->assign('channeltype_data' , $channel_type_data);
            $column = model('Column');
            $sort_num = $column->getSortNum($parent_id);
            $this->assign('sort_num',$sort_num);
            $member = model('Member');
            $member_level_list = $member->getMemberLevel([] , ' id,level_name ');
            $this->assign('member_level' , $member_level_list);
            return View();
        }
    }
    //添加顶级栏目
    public function addtop(){
        if(Request::instance()->isPost()){
            $data = input();
            //验证数据
            if(input('type_name') == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目名称不能为空哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen(input('type_name')) > 30){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目名称不能超过30个字符哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $channel_type = input('channel_type');
            if(!isset($channel_type) || $channel_type == 0){
                $a['errorcode'] = 1;
                $a['msg'] = '请选择内容模型哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $sort_rank = input('sort_rank');
            if(!is_numeric(input('sort_rank')) || !isset($sort_rank)){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目顺序不能为空，并且只能为数字';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $is_send = input('issend');
            if(isset($is_send)){
                $is_send = 2;
            }else{
                $is_send = 1;
            }
            $like = $data['like'];
            if(isset($like) && is_array($like)){
                $corank_arr = array_keys($like);
                $corank_str = implode(',',$corank_arr);
            }
            if(mb_strlen(input('keywords')) > 100){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目关键词不能超过100个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $type_dir = input('type_dir');
            if(!isset($type_dir)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($type_dir) > 100){
                $a['errorcode'] = 1;
                $a['msg'] = '栏目目录不能超过100个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $default_index = input('default_index');
            if(!isset($default_index)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($default_index) > 20){
                $a['errorcode'] = 1;
                $a['msg'] = '首页名称不能超过20个字符哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $temp_index = input('temp_index');
            if(!isset($temp_index)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($temp_index) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '模板封面不能超过60个字符哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $temp_list = input('temp_list');
            if(!isset($temp_list)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($temp_list) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '列表封面不能超过60个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $temp_article = input('temp_article');
            if(!isset($temp_article)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($temp_article) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '文章封面不能超过60个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $name_rule = input('name_rule');
            if(!isset($name_rule)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($name_rule) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '文章命名规则不能超过60个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $list_rule = input('list_rule');
            if(!isset($list_rule)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($list_rule) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '列表命名规则不能超过60个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $mode_name = input('mode_name');
            if(!isset($mode_name)){
                $a['erorcode'] = 1;
                $a['msg'] = '字符不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($mode_name) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '模板名称不能超过60个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(mb_strlen($description) > 200){
                $a['errorcode'] = 1;
                $a['msg'] = '栏目介绍不能超过200个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $data = [
                'parent_id' => 0,
                'sort_rank' => $sort_rank ,
                'type_name' => input('type_name'),
                'type_dir' => $type_dir,
                'defaultname' => $default_index,
                'issend' => $is_send,
                'channel_type' => $channel_type,
                'corank' => $corank_str,
                'default_index' => $temp_index,
                'templist' => $temp_list,
                'temparticle' => $temp_article,
                'namerule' => $name_rule,
                'listrule' => $list_rule,
                'modename' => $mode_name,
                'description' => $description,
                'keywords' => input('keywords'),
                'create_time'=>time()
            ];
            $column = model('Column');
            $column_id = $column->createColumn($data);
            if($column_id){
                $a['errorcode'] = 0;
                $a['msg'] = '添加顶级栏目成功';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }else{
                $a['errorcode'] = 1;
                $a['msg'] = '添加顶级栏目失败';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $parent_id = input('parent_id');
            $channeltype = model('Channeltype');
            $channel_type_data = $channeltype->getList(['enable'=>1],' typename,id ');
            $this->assign('channeltype_data' , $channel_type_data);
            $column = model('Column');
            $sort_num = $column->getSortNum($parent_id);
            $this->assign('sort_num',$sort_num);
            $member = model('Member');
            $member_level_list = $member->getMemberLevel([] , ' id,level_name ');
            $this->assign('member_level' , $member_level_list);
            return View();
        }
    }

    //获取栏目列表方法
    public function getColumnListTojson(){
        $parent_id = input('parent_id');
        if(isset($parent_id) && !is_numeric($parent_id)){
            echo "非法请求";
            die;
        }
        if(!isset($parent_id)){
            $parent_id = 0;
        }
        if(!is_numeric(input('page')) || !is_numeric(input('limit'))){
            echo "非法请求";
            die;
        }
        $where = [
            'parent_id'=>$parent_id
        ];
        $limit=(input('page') - 1)*input('limit') .',' .input('limit');
        $column = model('Column');
        $column_list = $column->getColumnList($where , 'id,sort_rank,type_name,defaultname,type_dir',$limit);
        //从数据库中读取系统配置信息
        $i = 0;
        foreach($column_list as $key=>$value){
            $column_list[$key]['index'] = $value['type_dir'] .'/' .$value['defaultname'];
            $i++;
        }
        $arr = [
            'data'=>$column_list,
            'count'=>$i,
            'code'=>0
        ];
        return json_encode($arr , JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return false|string|\think\response\View
     * Description 修改类目
     */
    public function alterColumn(){
        $id = input('id');
        if(!isset($id) || !is_numeric($id)){
            return self::ajaxError('非法请求');
        }
        //组合条件
        $where = ['id'=>$id];
        if(Request::instance()->isPost()){
            //验证数据
            $result = $this->validate(input(),$this->rule,$this->msg);
            if(true !== $result){
                return self::ajaxError($result);
            }
            //组合数据
            $data['parent_id'] = input('parent_id');
            $is_send = input('issend');
            $data['issend'] = empty($is_send)?1:2;
            $data['sort_rank'] = input('sort_rank');
            $data['type_name'] = input('type_name');
            $data['channel_type'] = input('channel_type');
            $data['corank'] = implode(',',array_keys(input()['like']));
            $data['type_dir'] = input('type_dir');
            $data['defaultname'] = input('defaultname');
            $data['default_index'] = input('default_index');
            $data['templist'] = input('templist');
            $data['temparticle'] = input('temparticle');
            $data['namerule'] = input('namerule');
            $data['listrule'] = input('listrule');
            $data['modename'] = input('modename');
            $data['description'] = input('description');
            $data['keywords'] = input('keywords');
            $data['cover_img'] = input('cover_url');
            $data['alter_time'] = time();
            $resulte = Model('column')->edit($where,$data);
            if($resulte){
                return self::ajaxOk('修改成功');
            }else{
                return self::ajaxError('修改失败');
            }
        }else{
            $column_info = Model('column')->getOne($where);
            $column_list = Model('column')->getAll([] , ' type_name,id ');
            foreach($column_list as $key => $value){
                if($value['id'] == $id){
                    unset($column_list[$key]);
                }
            }
            $member_level = explode(',' , $column_info['corank']);
            $channel_type_data = model('channeltype')->getALL(['enable'=>1],' typename,id ');
            $member_level_list = model('Member')->getMemberLevel([] , ' id,level_name ');

            View::share('column_info' , $column_info);
            View::share('column_list' , $column_list);
            View::share('corank' , $member_level);
            View::share('channeltype_data' , $channel_type_data);
            View::share('member_level' , $member_level_list);
            return View();
        }
    }

    //添加子栏目
    public function addchild(){
        $parent_id = input('parent_id');
        if(!isset($parent_id) || !is_numeric($parent_id)){
            echo '非法访问';
            die;
        }
        if(Request::instance()->isPost()){
            $data = input();
            //验证数据
            if(input('type_name') == ''){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目名称不能为空哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen(input('type_name')) > 30){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目名称不能超过30个字符哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $channel_type = input('channel_type');
            if(!isset($channel_type) || $channel_type == 0){
                $a['errorcode'] = 1;
                $a['msg'] = '请选择内容模型哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $sort_rank = input('sort_rank');
            if(!is_numeric(input('sort_rank')) || !isset($sort_rank)){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目顺序不能为空，并且只能为数字';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $is_send = input('issend');
            if(isset($is_send)){
                $is_send = 2;
            }else{
                $is_send = 1;
            }
            if(isset($data['like']) && is_array($data['like'])){
                $corank_arr = array_keys($data['like']);
                $corank_str = implode(',',$corank_arr);
            }
            if(mb_strlen(input('keywords')) > 100){
                $a['errorcode'] = 1;
                $a['msg'] = '输入的栏目关键词不能超过100个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $type_dir = input('type_dir');
            if(!isset($type_dir)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($type_dir) > 100){
                $a['errorcode'] = 1;
                $a['msg'] = '栏目目录不能超过100个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $default_index = input('defaultname');
            if(!isset($default_index)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($default_index) > 20){
                $a['errorcode'] = 1;
                $a['msg'] = '首页名称不能超过20个字符哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $temp_index = input('default_index');
            if(!isset($temp_index)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($temp_index) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '模板封面不能超过60个字符哦';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $temp_list = input('templist');
            if(!isset($temp_list)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($temp_list) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '列表封面不能超过60个字符哦';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $temp_article = input('temparticle');
            if(!isset($temp_article)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($temp_article) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '文章封面不能超过60个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $name_rule = input('namerule');
            if(!isset($name_rule)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }

            if(mb_strlen($name_rule) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '文章命名规则不能超过60个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $list_rule = input('listrule');
            if(!isset($list_rule)){
                $a['errorcode'] = 1;
                $a['msg'] = '字段不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($list_rule) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '列表命名规则不能超过60个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $mode_name = input('modename');
            if(!isset($mode_name)){
                $a['erorcode'] = 1;
                $a['msg'] = '字符不完整';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            if(mb_strlen($mode_name) > 60){
                $a['errorcode'] = 1;
                $a['msg'] = '模板名称不能超过60个字符';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(mb_strlen($description) > 200){
                $a['errorcode'] = 1;
                $a['msg'] = '栏目介绍不能超过200个字符';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }
            $data = [
                'parent_id' => $parent_id,
                'sort_rank' => $sort_rank ,
                'type_name' => input('type_name'),
                'type_dir' => $type_dir,
                'defaultname' => $default_index,
                'issend' => $is_send,
                'channel_type' => $channel_type,
                'corank' => $corank_str,
                'default_index' => $temp_index,
                'templist' => $temp_list,
                'temparticle' => $temp_article,
                'namerule' => $name_rule,
                'listrule' => $list_rule,
                'modename' => $mode_name,
                'description' => $description,
                'keywords' => input('keywords'),
                'create_time'=>time()
            ];
            $column = model('Column');
            $column_id = $column->createColumn($data);
            if($column_id){
                $a['errorcode'] = 0;
                $a['msg'] = '添加栏目成功';
                return json_encode($a , JSON_UNESCAPED_UNICODE);
            }else{
                $a['errorcode'] = 1;
                $a['msg'] = '添加栏目失败';
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{

            $this->assign('parent_id' , $parent_id);
            $channeltype = model('Channeltype');
            $channel_type_data = $channeltype->getList(['enable'=>1],' typename,id ');
            $this->assign('channeltype_data' , $channel_type_data);
            $column = model('Column');
            $sort_num = $column->getSortNum($parent_id);
            $column_list = $column->getColumnList([] , ' type_name,id ');
            $this->assign('column_list' , $column_list);
            $this->assign('sort_num',$sort_num);
            $member = model('Member');
            $member_level_list = $member->getMemberLevel([] , ' id,level_name ');
            $this->assign('member_level' , $member_level_list);
            return View();
        }
    }

    //删除栏目
    public function delcolumn(){
        $id = input('id');
        if(!isset($id) || !is_numeric($id)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        $column = model('column');
        $result = $column->delColumn($where);
        if($result){
            $a['errorcode'] = 0;
            $a['msg'] = '栏目删除成功';
            return json_encode($a , JSON_UNESCAPED_UNICODE);
        }else{
            $a['errorcode'] = 1;
            $a['msg'] = '栏目删除失败';
            return json_encode($a , JSON_UNESCAPED_UNICODE);
        }
    }

    //修改栏目排序编号方法
    public function altercolumnsortrank(){
        $id = input('id');
        $sort_num = input('sort_num');
        if(!isset($id) || !is_numeric($id) || !isset($sort_num) || !is_numeric($sort_num)){
            echo '非法访问';
            die;
        }
        $where = ['id'=>$id];
        $arr = ['sort_rank'=>$sort_num];
        $column = model('Column');
        $result = $column->alterColumnInfo($where , $arr);
        if($result){
            $a['errorcode'] = 0;
            $a['msg'] = '修改成功';
            return json_encode($a , JSON_UNESCAPED_UNICODE);
        }else{
            $a['errorcode'] = 1;
            $a['msg'] = '修改失败';
            return json_encode($a , JSON_UNESCAPED_UNICODE);
        }
    }

    //获取栏目列表树形数据
    public function getcolumnlisttree(){
        $column = model('Column');
        $column_list = $column->getColumnList([] , ' id,type_name as name,parent_id ');
        $res = [];
        $tree = [];
        //整理数组
        foreach($column_list as $key => $value){
            $res[$value['id']] = $value;
            $res[$value['id']]['children'] = [];
        }
        //查询子孙
        foreach($res as $key => $value){
            if($value['parent_id'] != 0){
                $res[$value['parent_id']]['children'][] = &$res[$key];
            }
        }
        //去除杂质
        foreach($res as $key => $value){
            if($value['parent_id'] == 0){
                $tree[] = $value;
            }
        }
        unset($res);
        return json_encode($tree , JSON_UNESCAPED_UNICODE);
    }

    //显示栏目内容方法
    public function getcolumninfo(){
        $id = input('id');
        $this->assign('id',$id);
        return View();
    }

    //上传栏目封面方法
    public function uploadColumnCoverImg(){
        //设置用户信息
        File::setUserInfo(2, Session::get('admin')['id']);
        //设置文档信息
        File::setArticleInfo(-1,"this's column cover img");
        if (File::uploadFile($_FILES['file'], '', '', true)) {
            $a['url'] = File::$url;
            $a['id'] = File::$upload_id;
            $a['errorcode'] = 0;
            $a['msg'] = '上传成功';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        } else {
            $a['errorcode'] = 1;
            $a['msg'] = '上传失败';
            return json_encode($a, JSON_UNESCAPED_UNICODE);
        }
    }
}