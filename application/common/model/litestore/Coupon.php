<?php

namespace app\common\model\litestore;

use think\Model;

class Coupon extends Model
{
    // 表名
    protected $name = 'litestore_coupon';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [];

    public function couponData()
    {
        return $this->hasMany('CouponData', 'litestore_coupon_id');
    }

    /**
     * 查询单条数据
     * @param $where
     * @param $field
     * @return mixed
     */
    public function find_data($where, $field)
    {
        return $this->where($where)->field($field)->find();
    }

    /**
     * 查询多条数据
     * @param $where
     * @param $field
     * @param string $order
     * @param string $with
     * @return mixed
     */
    public function select_data($where, $field, $order = 'id desc', $with)
    {
        return $this->where($where)->with($with)->field($field)->order($order)->select();
    }

    /**
     * 修改|添加
     * @param array $where
     * @param array $data
     * @param string $sequence 自增序列名
     * @return mixed
     */
    public function save_data($where, $data, $sequence)
    {
        return $this->allowField(true)->save($data, $where, $sequence);
    }
}
