<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 地区数据模型
 */
class ActivityCate extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    /*
     * 查询活动分类
     */
    public function getActivityCate($where)
    {
        $field = 'name,image';
        return $this->where($where)->field($field)->order('ordid')->select();
    }
}