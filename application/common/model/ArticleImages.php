<?php
/**
 * Created by PhpStorm.
 * User: yunxi
 * Date: 2019/3/12 0012
 * Time: 11:10
 */

namespace app\common\model;


class ArticleImages extends Base
{
    public $table = 'article_images';
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}