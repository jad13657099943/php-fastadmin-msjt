<?php

namespace app\admin\model\distributor;

use think\Model;

class Level extends Model
{
    // 表名
    protected $name = 'distributor_level';
    
    // 定义时间戳字段名
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'create_time_text'
    ];
    

    



    public function getCreateTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['create_time']) ? $data['create_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCreateTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
