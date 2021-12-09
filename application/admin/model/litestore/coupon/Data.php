<?php

namespace app\admin\model\litestore\coupon;

use app\admin\model\litestore\Coupon;
use app\admin\model\User;
use app\admin\model\user\Level;
use think\Model;

class Data extends Model
{
    // 表名
    protected $name = 'litestore_coupon_data';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = 'add_time';
    protected $updateTime = false;
    
    // 追加属性
    protected $append = [
        'get_type_text',
        'add_time_text',
        'use_time_text',
        'use_start_time_text',
        'use_end_time_text'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'litestore_coupon_id');
    }

    
    public function getGetTypeList()
    {
        return ['couponcenter' => __('Couponcenter'),'newpersion' => __('Newpersion'),'backend' => __('Backend')];
    }

    public function getUserLevelDataList()
    {
        return Level::where(['status'=>'normal'])->column('id,name');
    }

    public function getGetTypeTextAttr($value, $data)
    {        
        $value = $value ? $value : (isset($data['get_type']) ? $data['get_type'] : '');
        $list = $this->getGetTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAddTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['add_time']) ? $data['add_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUseTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['use_time']) ? $data['use_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUseStartTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['use_start_time']) ? $data['use_start_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUseEndTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['use_end_time']) ? $data['use_end_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAddTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setUseTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setUseStartTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setUseEndTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


}
