<?php

namespace app\admin\model;

use think\Model;

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
        return $this->belongsTo('Litestoregoods','goods_id','goods_id');
    }

    public function spec()
    {
        return $this->belongsTo('Litestoregoodsspec','spec_sku_id','spec_sku_id');
    }


    /*
     * 获取多条订单数据
     * */
    public function select_data($where , $field){
        $list =  $this->where($where)->field($field)->select();
        return $this->joinArrayImages($list,'images');
    }

    //查找一条数据

    public function find_data($where=[],$field='*')
    {
        return $this->where($where)->field($field)->find();
    }
}
