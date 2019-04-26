<?php
/**
 * Created by PhpStorm.
 * User: ww
 * Date: 2017/6/25
 * Time: 11:40
 * Description:admin应用配置文件
 */
return [
    //Rbac权限控制相关配置
    'RBAC_USER'             =>  'Admin_user',                       //用户表
    'RBAC_USER_LEVEL'       =>  'Admin_level',                      //用户等级表
    'RBAC_NODE'             =>  'Rbac_node',                        //节点表
    'RBAC_USERRULE'         =>  'Rbac_admin_userrule',              //用户权限表
    'RBAC_TYPERULE'         =>  'Rbac_admin_typerule',              //等级权限表
    'RBAC_ADMIN_USER'       =>  '1',                                //超级管理员id
    'RBAC_AUTH_MODEL_NOT'   =>  array(

    ),//不需要验证的模块

];