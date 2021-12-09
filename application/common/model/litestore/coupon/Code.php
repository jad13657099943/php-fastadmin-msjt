<?php

namespace app\admin\model\litestore\coupon;

use app\admin\model\litestore\Coupon;
use think\Model;

class Code extends Model
{
    // 表名
    protected $name = 'litestore_coupon_code';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'create_time_text',
        'active_time_text'
    ];


    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }



    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getActiveTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['active_time']) ? $data['active_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setActiveTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function cenerateCode()
    {
        return random(7);
    }

}
