<?php


namespace app\model;

interface Log
{
    public function log($model, $type, $data);
}