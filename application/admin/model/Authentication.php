<?php

namespace app\admin\model;

use think\Model;

class Authentication extends Model
{
    // 表名
    protected $name = 'store';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'audit_time_text',
        'add_time_text'
    ];

//申请状态 0）待审核 1）审核通过 2）审核失败
    public function getStoreStatusList()
    {
        $count0 = $this->where(['store_status' => 0])->count();
        $count1 = $this->where(['store_status' => 1])->count();
        $count2 = $this->where(['store_status' => 2])->count();
        return [
            '0' => "待审核($count0)",
            '1' => "已通过($count1)",
            '2' => "已拒绝($count2)"
        ];
    }
    /**
     * 关联查询
     * @return mixed
     */
    public function user()
    {
//        return $this->belongsTo('User', 'uid', 'id')->bind('username');
        return $this->belongsTo('User', 'uid')->setEagerlyType(0);
    }

    /**
     * 关联查询
     * @return mixed
     */
//    public function province()
//    {
//        return $this->belongsTo('Area', 'province_id','id')->field('id,shortname');
//    }
    //无效
//    public function city()
//    {
//        return $this->belongsTo('Area', 'city_id', 'id')->field('id,shortname as city');
//    }
//     无效
//    public function area()
//    {
//        return $this->belongsTo('Area', 'area_id', 'id')->field('id,shortname as area');
//    }


    /**
     * 查询数据
     * @param $where
     * @param $field
     * @return mixed
     */
    public function select_data($where, $field)
    {
        return $this->where($where)->field($field)->select();
    }

    /**
     * 更新数据
     * @param $where
     * @param $field
     * @return mixed
     */
    public function update_data($where, $field)
    {
        return $this->where($where)->update($field);
    }

    /**
     * 查询一条数据
     * @param $where
     * @param string $field
     * @return mixed
     */
    public function find_data($where, $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }


    public function getIdcardFrontAttr($value)
    {
        return $value ? config('item_url') . $value : '';
    }

    public function getIdcardBackAttr($value)
    {
        return $value ? config('item_url') . $value : '';
    }

    public function getStoreFrontPhotoAttr($value)
    {
        return $value ? config('item_url') . $value : '';
    }

    public function getBusinessLicenseAttr($value)
    {
        return $value ? config('item_url') . $value : '';
    }

    public function getAuditTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['audit_time']) ? $data['audit_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getAddTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['add_time']) ? $data['add_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setAuditTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setAddTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

}
