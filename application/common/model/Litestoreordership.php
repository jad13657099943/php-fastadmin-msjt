<?php

namespace app\common\model;

use think\Model;

class Litestoreordership extends Model
{
    protected $name = 'litestore_order_ship';
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    /**
     * 获取分页数据
     * @param $where
     * @param $field
     * @param int $page
     * @param int $pageSize
     * @param string $order
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function select_page($where, $field, $page = 1, $pageSize = 10, $order = 'create_time desc')
    {
        return $this->where($where)->field($field)->page($page, $pageSize)->order($order)->select();
    }


    public function company()
    {
        return $this->hasOne('Kdniao', 'code', 'express_company')->bind('company_name');
    }

    /**
     * 获取多条数据
     * @param $where
     * @param $field
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function select_data($where, $field)
    {
        return $this->where($where)->field($field)->select();
    }
}