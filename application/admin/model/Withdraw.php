<?php

namespace app\admin\model;

use think\Model;

class Withdraw extends Model
{
    // 表名
    protected $name = 'withdraw';
    

    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'add_time_text',
        'over_time_text'
    ];


    public function getAddTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['add_time']) ? $data['add_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getOverTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['over_time']) ? $data['over_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAddTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setOverTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
