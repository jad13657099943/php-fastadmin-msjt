<?php

namespace app\admin\model;

use think\Cache;
use think\Model;

/**
 * 积分商品
 */
class Integralitem extends Model
{
    protected $name = 'integral_item';
    

    /**
     * 添加积分商品
     */
    public function addIntegraItem($params)
    {
        return $this->allowField(true)->save($params);

    }


    /**
     * 更新积分商品
     */
    public function editIntegralItem($params, $condition)
    {
        return $this->where($condition)->update($params);

    }

    /**
     * 分页数据
     * @param array $where
     * @param string $field
     * @param string $order
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function select_page($where = [], $field = '*', $order = 'id desc', $page = 1, $pageSize = 10)
    {
        return $this->where($where)->field($field)->order($order)->page($page, $pageSize)->select();
    }

    /*
     * 获取多个数据
     */
    public function select_data($where, $field)
    {
        //上架商品
        $where['status'] = 1;
        return $this->where($where)->field($field)->select();
    }


    /*
     * 获取单条数据
     */
    public function find_data($where = [], $field = '*')
    {
        $where['status'] = 1;
        return $this->where($where)->field($field)->find();
    }



//    //获取分页数据
//    public function select_page($where = [], $field = "*", $page = 1, $pagesize = 10, $order = "add_time desc", $type = 0)
//    {
//        $where['status'] = 1;
//        $list = $this->where($where)->field($field)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
//
//        return $list;
//    }


}
