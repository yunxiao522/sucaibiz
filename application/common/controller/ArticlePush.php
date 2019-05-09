<?php


namespace app\common\controller;


interface ArticlePush
{
    public static function add($article_info, $data);

    public static function edit($article_info, $data);
}