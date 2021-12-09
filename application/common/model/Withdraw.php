<?php

namespace app\common\model;

use think\Model;

/**
 * 提现表
 */
class Withdraw extends Model
{
    // 表名
    protected $name = 'withdraw';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = false;

    //获取单条数据
    public function find_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }

    //获取多条数据
    public function select_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->select();
    }

    //获取多条分页数据
    public function get_page_data($where = [], $field = '*', $order = 'id desc', $page = 0, $pagesize = 10)
    {
        return $this->field($field)->where($where)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }

    //修改数据
    public function update_data($where, $data)
    {
        return $this->where($where)->update($data);
    }

    //删除数据
    public function delete_data($where)
    {
        return $this->where($where)->delete();
    }
}