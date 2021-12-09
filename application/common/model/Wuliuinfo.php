<?php

namespace app\common\model;
use app\common\controller\Backend;
use think\Model;

class Wuliuinfo extends Model
{
    protected $name = 'wuliu_info';

    //获取单个物流公司信息
    public function getWuliuInfo($condition,$field='*'){
        return $this->field($field)->where($condition)->find();

    }





}
