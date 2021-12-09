<?php

namespace app\common\model;

use think\Model;
use app\common\model\User;

class Litestoreordergoods extends Model
{
    // 表名
    protected $name = 'litestore_order_goods';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [];

    public function goods()
    {
        return $this->belongsTo('Litestoregoods', 'goods_id', 'goods_id');
    }

    public function spec()
    {
        return $this->belongsTo('Litestoregoodsspec', 'goods_spec_id', 'goods_spec_id');
    }

    public function discount()
    {
        return $this->hasOne('Limitdiscountgoods', 'goods_id', 'goods_id');
    }

    public function group()
    {
        return $this->hasOne('Groupbuygoods','goods_id','goods_id');
    }

    public function order()
    {
        return $this->belongsTo('Litestoreorder','id','order_id')->field('id');
    }



    //查找一条数据

    public function find_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }

    //查找多条数据

    public function select_data($where, $field = '*')
    {
        $list = $this->where($where)->field($field)->select();
        return $this->joinArrayImages($list, 'images');
    }


    /*
     * 添加数据
     * @param  $param 查询条件
     */
    public function add_data($param = [])
    {
        return $this->allowField(true)->saveAll($param);
    }

    /**
     * 修改信息
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update_data($where = [], $data = [])
    {
        if (empty($where)) {
            return false;
        }
        return $this->where($where)->update($data);
    }

    public function getValue($where, $field)
    {
        return $this->where($where)->value($field);
    }
}
