<?php


namespace app\model;


class RbacModel extends Base
{
    protected $name = 'rbac_model';

    public static $model_type = [1=>'外部访问',2=>'内部调用'];
}