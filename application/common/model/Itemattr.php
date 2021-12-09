<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 广告管理模型
 */
class Itemattr extends Model
{
    protected $name = 'item_attr';

    /**
     * 查询多条数据
    */
    public function select_data($where , $field){
        return $this->where($where)->field($field)->select();
    }
}