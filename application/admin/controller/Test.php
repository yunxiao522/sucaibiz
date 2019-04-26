<?php

namespace app\admin\controller;

use SucaiZ\Kafka;
use SucaiZ\Wechat;
use think\Collection;

class Test extends Collection
{
    public function __construct(array $items = [])
    {
        parent::__construct($items);
    }

    public function index()
    {
        Wechat::getAccessToken();

    }

    public function test2()
    {
        $kafuka = new Kafka();
        $kafuka->broker_list = '10.30.104.94:9092';
        $kafuka->consumer();
    }

}