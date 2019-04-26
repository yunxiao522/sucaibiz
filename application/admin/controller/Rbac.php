<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2018/6/6
 * Time: 18:28
 * Description: 权限管理模块
 */


namespace app\admin\controller;


use think\Request;

class Rbac extends Common
{

    //模块类型
    private $model_type = [1=>'外部访问',2=>'内部调用'];
    //构造函数
    public function __construct()
    {
        parent::__construct();
    }

    //模块管理
    public function modelMenage(){
        return View('rbac_show_model');
    }

    //获取模块列表数据
    public function getModelListJson(){
        $limit = (input('page') - 1) * input('limit') . ',' . input('limit');
        $rbac = new \app\admin\model\Rbac();
        $model_list = $rbac->getModelList([] ,' * ' ,$limit);
        //处理列表数据
        foreach($model_list as $key => $value){
            $model_list[$key]['type'] = $this->model_type[$value['type']];
            $model_list[$key]['create_time'] = date('Y-m-d H:i:s' ,$value['create_time']);
            if(!empty($value['alter_time'])){
                $model_list[$key]['alter_time'] = date('Y-m-d H:i:s' ,$value['alter_time']);
            }
        }
        $model_count = $rbac->getModelCount([]);
        $arr = [
            'data' => $model_list,
            'count' => $model_count,
            'code' => 0
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);


    }

    //新建模块
    public function addModel(){
        if(Request::instance()->isPost()){
            //验证数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';die;
            }
            $parent_id = input('parent_id');
            if(!isset($parent_id)){
                echo '非法访问';die;
            }
            if(!is_numeric($parent_id)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的格式不正确'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $ico = input('ico');
            if(!isset($ico)){
                echo '非法访问';die;
            }
            if(mb_strlen($ico ,'UTF-8') >50){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的图标地址不能超过50个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $app = input('app');
            if(!isset($app)){
                echo '非法访问';die;
            }
            if($app == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的应用名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $controller = input('controller');
            if(!isset($controller)){
                echo '非法访问';die;
            }
            if(mb_strlen($controller ,'UTF-8') >25){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的控制器名称不能超过25个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $method = input('method');
            if(!isset($method)){
                echo '非法访问';die;
            }
            if(mb_strlen($method ,'UTF-8') >25){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的方法名称不能超过25个'
                ];
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }
            $url = input('url');
            if(!isset($url)){
                echo '非法访问';die;
            }
            if(mb_strlen($url ,'UTF-8') >100){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的链接地址不能超过100个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(!isset($description)){
                echo '非法访问';die;
            }
            if(mb_strlen($description ,'UTF-8') >100){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的描述不能超过100个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $type = input('type');
            if(!isset($type)){
                echo '非法访问';die;
            }
            if(!is_numeric($type)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入参数类型错误'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $state = input('state');
            if(isset($state)){
                $state = 1;
            }else{
                $state = 2;
            }

            //组合数据添加到数据库
            $arr = [
                'parent_id'=>$parent_id,
                'name'=>$name,
                'app'=>$app,
                'controller'=>$controller,
                'method'=>$method,
                'url'=>$url,
                'description'=>$description,
                'create_time'=>time(),
                'ico'=>$ico,
                'type'=>$type,
                'state'=>$state
            ];
            $rbac = new \app\admin\model\Rbac();
            if($rbac->addModel($arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'添加成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'添加失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $model_list = $this->getModelList();
            $this->assign('list' ,$model_list);
            return View('rbac_add_model');
        }
    }

    //编辑模块
    public function alterMode(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $where = ['id'=>$id];
        $rbac = new \app\admin\model\Rbac();
        if(Request::instance()->isPost()){
            //验证数据
            $name = input('name');
            if(!isset($name)){
                echo '非法访问';die;
            }
            $parent_id = input('parent_id');
            if(!isset($parent_id)){
                echo '非法访问';die;
            }
            if(!is_numeric($parent_id)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的格式不正确'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $ico = input('ico');
            if(!isset($ico)){
                echo '非法访问';die;
            }
            if(mb_strlen($ico ,'UTF-8') >50){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的图标地址不能超过50个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $app = input('app');
            if(!isset($app)){
                echo '非法访问';die;
            }
            if($app == ''){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的应用名不能为空'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $controller = input('controller');
            if(!isset($controller)){
                echo '非法访问';die;
            }
            if(mb_strlen($controller ,'UTF-8') >25){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的控制器名称不能超过25个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $method = input('method');
            if(!isset($method)){
                echo '非法访问';die;
            }
            if(mb_strlen($method ,'UTF-8') >25){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的方法名称不能超过25个'
                ];
                return json_encode($a,JSON_UNESCAPED_UNICODE);
            }
            $url = input('url');
            if(!isset($url)){
                echo '非法访问';die;
            }
            if(mb_strlen($url ,'UTF-8') >100){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的链接地址不能超过100个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $description = input('description');
            if(!isset($description)){
                echo '非法访问';die;
            }
            if(mb_strlen($description ,'UTF-8') >100){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入的描述不能超过100个字符'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $type = input('type');
            if(!isset($type)){
                echo '非法访问';die;
            }
            if(!is_numeric($type)){
                $a = [
                    'errorcode'=>1,
                    'msg'=>'输入参数类型错误'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
            $state = input('state');
            if(isset($state)){
                $state = 1;
            }else{
                $state = 2;
            }

            //组合数据添加到数据库
            $arr = [
                'parent_id'=>$parent_id,
                'name'=>$name,
                'app'=>$app,
                'controller'=>$controller,
                'method'=>$method,
                'url'=>$url,
                'description'=>$description,
                'alter_time'=>time(),
                'ico'=>$ico,
                'type'=>$type,
                'state'=>$state
            ];
            if($rbac->alterModel($where ,$arr)){
                $a = [
                    'errorcode'=>0,
                    'msg'=>'修改成功'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }else{
                $a = [
                    'errorcode'=>1,
                    'msg'=>'修改失败'
                ];
                return json_encode($a ,JSON_UNESCAPED_UNICODE);
            }
        }else{
            $model_info = $rbac->getModelInfo($where ,' * ');
            $this->assign('info' ,$model_info);
            $model_list = $this->getModelList();
            $this->assign('list' ,$model_list);
            return View('rbac_alter_model');
        }
    }

    //删除模块
    public function delModel(){
        $id = input('id');
        if(!isset($id)){
            echo '非法访问';die;
        }
        $where = ['id'=>$id];
        $rbac = new \app\admin\model\Rbac();
        if($rbac->delModel($where)){
            $a = [
                'errorcode'=>0,
                'msg'=>'删除成功'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }else{
            $a = [
                'errorcode'=>1,
                'msg'=>'删除失败'
            ];
            return json_encode($a ,JSON_UNESCAPED_UNICODE);
        }
    }

    //权限管理页面
    public function access(){
        if(Request::instance()->isPost()){

        }else{
            //获取角色列表
            $user = new \app\admin\model\User();
            $level_list = $user->getUserLevelList();

            $this->assign('level' ,$level_list);
            $user_list = $user->getUserList();
            $this->assign('user' ,$user_list);
            return View('rbac_show');
        }
    }

    //显示权限页面
    public function showAccess(){
        //判断查询条件
        $level = input('level');
        if(isset($level)){
            $where['level'] = $level;
        }
        $id = input('id');
        if(isset($id)){
            $where['id'] = $id;
        }
        //获取模块列表数据
        $model_list = $this->getModelList();
//        dump($model_list);
        $this->assign('list',$model_list);

        return View('rbac_show_access');
    }

    //获取模块列表
    private function getModelList($where = [] ,$field = ' * ' ,$limit = 1000){
        $rbac = new \app\admin\model\Rbac();
        $model_list = $rbac->getModelList($where ,$field ,$limit);
        return getarticletype($model_list ,0 ,0);
    }
}