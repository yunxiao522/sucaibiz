<?php


namespace app\model;


class Down extends Base
{
    protected $name = 'down';

    protected $createTime = 'create_time';

    protected $updateTime = 'end_time';

    public function getArticleIdAttr($value){
        return Article::getField(['id'=>$value], 'title', 'id desc', true);
    }
}