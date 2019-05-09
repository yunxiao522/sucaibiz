<?php


namespace app\model;


class Advert extends Base
{
    protected $name = 'advert';

    public static $advert_status = [1=>'启用',2=>'禁用'];
}