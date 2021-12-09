<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 限时折扣模型
 */
class UserRebateBack extends Model
{
    protected $name = 'user_rebate_back';

    protected $updateTime = false;

    /*
     * 统计数量
     */
    public function count_data($where)
    {
        return $this->where($where)->count();
    }

    /*
     * 获取多条数据
     */
    public function select_data($where, $field, $order = 'create_time desc')
    {
        $list = $this->where($where)->field($field)->order($order)->select();
        return $list;
    }

    /*
     * 获取分页多条数据
     */
    public function select_page($where, $field, $order = 'create_time desc', $page = 1, $pagesize = 10)
    {
        return $this->field($field)->where($where)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }


    public function storeOrder()
    {
        $field = 'order_no,pay_price,total_price,';
        return $this->hasOne('Litestoreorder', 'id', 'order_id');
    }

    public function goods()
    {
        return $this->hasMany('Litestoreordergoods', 'order_id', 'order_id');
    }

    public function user()
    {
        return $this->hasOne('User', 'id', 'uid')->bind('username,mobile');
    }

}