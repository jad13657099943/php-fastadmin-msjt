<?php

namespace app\common\model;

use think\Model;

/**
 * 核销记录表
 * Class Litestoreorderwriteoff
 * @package app\common\model
 */
class Litestoreorderwriteoff extends Model
{
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;
    protected $name = 'litestore_order_writeoff';

    /**
     * 添加数据
     * @param $data
     * @return false|int
     */
    public function add($data)
    {
        return $this->save($data);
    }

    /**
     * 查找单条数据
     * @param $where
     * @param string $field
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function find_data($where, $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }

    /**
     * 查询单挑条件  排序后查询
     * @param $where
     * @param string $field
     * @param string $order
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function find_order_data($where, $field = '*', $order = 'create_time desc')
    {
        return $this->where($where)->field($field)->order($order)->find();
    }

    /**
     * 查询多条数据
     * @param $where
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function select_data($where, $field = '*')
    {
        return $this->where($where)->field($field)->select();
    }

    /**
     * 统计数据
     * @param string $where
     * @param string $field
     * @return int|string
     * @throws \think\Exception
     */
    public function count($where, $field = '*')
    {
        return parent::where($where)->count();
    }
}