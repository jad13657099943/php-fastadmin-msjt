<?php

namespace app\admin\model;

use think\Model;

/**
 * 会员模型
 */
class Coupon Extends Model
{
    // 表名
    protected $name = 'coupon';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
// 追加属性
    protected $append = [
        'starttime_text',
        'endtime_text',
    ];


    public function getStatusList()
    {
        return ['1' => __('Normal'), '2' => __('Hidden')];
    }


    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['starttime']) ? $data['starttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    protected function setStarttimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['endtime']) ? $data['endtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    protected function setEndtimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    /*
         * 查询多条数据
         *
         */

    public function select_data($where,$field='*',$order='id desc')
    {

        return $this->where($where)->field($field)->order($order)->select();
    }

    public function find_data($where , $field='*'){
        return $this->where($where)->field($field)->find();

    }

    public function update_data($where , $save){
        return $this->where($where)->update($save);

    }
}
