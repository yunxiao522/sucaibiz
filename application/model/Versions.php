<?php


namespace app\model;

class Versions extends Base
{
    protected $name = 'version';
    protected $updateTime = '';
    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}