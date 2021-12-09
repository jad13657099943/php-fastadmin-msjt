<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 积分商品
 */
class Integralorder extends Model
{
    protected $name = 'integral_order';


    /*
     * 获取分页数据
     */
    public function select_page($where = [], $field = '*', $order = 'add_time desc', $page = 1, $pagesize = 10)
    {

        return $this->field($field)->where($where)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }

    /*
     * 获取多条数据
     */
    public function select_data($where = [], $field = '*', $order = 'id desc', $with='')
    {
        return $this->where($where)->with($with)->field($field)->order($order)->select();
//        return $this->joinArrayImages($list,'image');
    }

    /*
     * 获取单条数据
     */
    public function find_data($where = [], $field = '*')
    {

        return $this->where($where)->field($field)->find();
    }

    /*
     * 确认收货
     * @param $where
     * @return bool
     */
    public function receipt($where)
    {
        $where['order_status'] = '1';
        if ($this->where($where)->update(['order_status' => 2])) {
            return true;
        } else {
            return false;
        }
    }

    public function integral(){
        return $this
            ->belongsTo('Integralitem', 'goods_id', 'id', null, 'LEFT')
            ->setEagerlyType(0);
    }
}