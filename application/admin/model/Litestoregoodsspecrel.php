<?php

namespace app\admin\model;

use think\Model;

class Litestoregoodsspecrel extends Model
{
    // 表名
    protected $name = 'litestore_goods_spec_rel';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = '';

    /**
     * 关联规格组
     * @return \think\model\relation\BelongsTo
     */
    public function spec()
    {
        return $this->belongsTo('Litestorespec', 'spec_id');
    }

    /**
     *  关联规格值
     * @return \think\model\relation\BelongsTo
     */
    public function specValue()
    {
        return $this->belongsTo('Litestorespecvalue', 'spec_value_id');
    }

    /**
     * 查询数据
     */
    public function find_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }
}