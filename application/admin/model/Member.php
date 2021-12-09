<?php

namespace app\admin\model;

use app\common\model\MoneyLog;
use think\Model;

class Member extends Model
{

    // 表名
    protected $name = 'member';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $add_timeime = 'add_time';

    //protected $updateTime = 'updatetime';

    public function getJointimeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['add_time'];
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function select_data($where, $sort, $order, $offset, $limit)
    {
        return $this->where($where)->order($sort, $order)->limit($offset, $limit)->select();
    }
}