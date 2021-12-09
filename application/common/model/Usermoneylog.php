<?php

namespace app\common\model;

use think\Model;

class Usermoneylog extends Model
{

    // 表名
    protected $name = 'user_money_log';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

    //获取佣金明细列表

    public function select_page($where, $field, $order = 'createtime desc', $page = 1, $pagesize = 10)
    {

        $list = $this->field($field)->where($where)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
        return $list;

    }

    /**
     * 获取多条数据
     */
    public function select_data($where, $field, $order = 'id desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }
}