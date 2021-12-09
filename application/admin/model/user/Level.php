<?php

namespace app\admin\model\user;

use think\Model;

class Level extends Model
{
    // 表名
    protected $name = 'user_level';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'status_text',
        'upgrade_price_text',
        'discount_text',
    ];
    

    
    public function getStatusList()
    {
        return ['normal' => __('Normal'),'hidden' => __('Hidden')];
    }     


    public function getStatusTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getUpgradePriceTextAttr($value, $data)
    {
        if ($data['id'] == 0) {
            return '默认等级';
        }
        if ($data['upgrade_price'] <= 0) {
            return '不自动升级';
        }
        return '完成订单金额满 ' . $data['upgrade_price'] . '元';
    }

    public function getDiscountTextAttr($value, $data)
    {
        if (!$data['discount']) {
            return '不参与折扣';
        }
        return $data['discount'] . '折';
    }


}
