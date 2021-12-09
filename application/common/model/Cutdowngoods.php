<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 限时折扣模型
 */
class Cutdowngoods extends Model
{
    protected $name = 'cut_down_goods';

    /**
     * 添加限时折扣商品
     */
    public function addLimitDiscountGoods($params)
    {
        return $this->allowField(true)->save($params);

    }

    /**
     * 读取限时折扣商品列表
     */
    public function getLimitDiscountGoodsList($where, $wherea = NULL, $sort = '', $order = '', $offset = 0, $limit = 0, $field = '*')
    {
        $limitdiscountgoods_list = $this->where($where)
            ->where($wherea)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();

        return $limitdiscountgoods_list;
    }

    /**
     * 读取限时折扣单个商品
     */
    public function getLimitDiscountGoods($where, $field = '*')
    {
        $limitdiscountgoods_list = $this->where($where)
            ->field($field)
            ->find();
        return $limitdiscountgoods_list;
    }

    /**
     * 修改限时折扣商品
     */
    public function editLimitDiscountGoods($update, $condition)
    {
        $result = $this->where($condition)->update($update);
        return $result;
    }

    /**
     * 删除限时折扣商品列表
     */
    public function delLimitDiscountGoods($condition)
    {
        return $this->where($condition)->delete();
    }



    //获取商品列表
    public function select_page_data($where = [],$field = '*',$page = 0,$pagesize = 0,$order='id desc'){
        $where['status'] = 10;
        $list = $this->field($field)->where($where)->
        order($order)->limit(($page-1)*$pagesize, $pagesize)->select();

        return $this->joinArrayImages($list ,'image');
    }
    /*
     * 获取单条信息
     *
     */
    public function find_data($where,$field='*')
    {
        $where['status'] = 10;
        return $this->field($field)->where($where)->find();
    }

    /**
     * 支付后 添加销量 减少库存  取消订单 添加库存  减少销量
     * @param  $staus 1) 支付  2）取消订单
     * @param  $cut_down_id 活动id
     * @param  $number 数量
     * */
    public function updateSpec($cut_down_id, $number, $staus = 1)
    {
        $where = ['id' => $cut_down_id];
        $info = $this->find_data($where, 'stock_num,stock,stock,sales');
        switch ($staus) {
            case 1: //添加销量 减少库存
                if ($number > $info['stock'])
                    return false;

                $update = ['stock_num' => $info['stock_num'] - $number, 'sales' => $info['sales'] + $number , 'stock' => $info['stock'] - $number];
                break;

            case 2: //取消订单 添加库存
                if ($number > $info['sales'])
                    return false;

                $update = ['stock_num' => $info['stock_num'] + $number, 'sales' => $info['sales'] - $number,'stock' => $info['stock'] + $number];
                break;
        }
        $update['stock_num'] = $update['stock_num']< 0 ? 0 :$update['stock_num'];
        $update['sales'] = $update['sales']< 0 ? 0 :$update['sales'];
        $update['stock'] = $update['stock']< 0 ? 0 :$update['stock'];
        $update_ = $this->editLimitDiscountGoods($update , $where);

        if ($update_)
            return true;
        else
            return false;
    }
}
