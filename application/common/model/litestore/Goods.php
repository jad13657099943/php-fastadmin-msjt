<?php

namespace app\admin\model\litestore;

use think\Model;

class Goods extends Model
{
    // 表名
    protected $name = 'litestore_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'spec_type_text',
        'deduct_stock_type_text',
        'goods_status_text',
        'is_delete_text',
        'uppershelf_time_text'
    ];
    

    
    public function getSpecTypeList()
    {
        return ['10' => __('Spec_type 10'),'20' => __('Spec_type 20')];
    }     

    public function getDeductStockTypeList()
    {
        return ['10' => __('Deduct_stock_type 10'),'20' => __('Deduct_stock_type 20')];
    }     

    public function getGoodsStatusList()
    {
        return ['10' => __('Goods_status 10'),'20' => __('Goods_status 20'),'30' => __('Goods_status 30'),'40' => __('Goods_status 40')];
    }     

    public function getIsDeleteList()
    {
        return ['0' => __('Is_delete 0'),'1' => __('Is_delete 1')];
    }     


    public function getSpecTypeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['spec_type']) ? $data['spec_type'] : '');
        $list = $this->getSpecTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDeductStockTypeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['deduct_stock_type']) ? $data['deduct_stock_type'] : '');
        $list = $this->getDeductStockTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGoodsStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['goods_status']) ? $data['goods_status'] : '');
        $list = $this->getGoodsStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsDeleteTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['is_delete']) ? $data['is_delete'] : '');
        $list = $this->getIsDeleteList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getUppershelfTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['uppershelf_time']) ? $data['uppershelf_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setUppershelfTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
