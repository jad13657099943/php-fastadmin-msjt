<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 团购商品模型
 */
class Groupbuygoods extends Model
{
    protected $name = 'groupbuy_goods';
    /**
     * 添加团购商品
     */
    public function addGroupbuyGoods($params,$where=[]){
        return $this->allowField(true)->save($params ,$where);

    }

    /**
     * 批量添加团购商品
     */
    public function addGroupbuyGoodsAll($params){
        return $this->insertAll($params);

    }


    /**
     * 读取团购商品列表
     */
    public function getGroupbuyList($where=[],$sort='',$order='',$offset=0,$limit=0,$field='*'){
        $groupbuy_list = $this->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->field($field)
            ->select();
        return $groupbuy_list;
    }

    /**
     * 删除团购商品列表
     */
    public function delGroupbuyGoods($condition){

        return $this->where($condition)->delete();
    }


    /*
     * 获取多条数据
     * */
    public function select_data($where= [] , $field='*'){
        return $this->where($where)->field($field)->select();
    }

    /*
     * 获取单条数据
     * */
    public function find_data($where , $field){
        return $this->where($where)->field($field)->find();
    }

    /*
    * 统计数据
    * */
    public function find_count($where){
        return $this->where($where)->count();
    }


    /**
     * 修改限时折扣商品
     */
    public function editLimitDiscountGoods($update, $condition)
    {
        $result = $this->where($condition)->update($update);
        return $result;
    }


    //获取商品列表
    public function select_page_data($where = [],$field = '*',$page = 0,$pagesize = 0,$order='id desc'){
        $where['status'] = 10;
        $list = $this->field($field)->where($where)->
        order($order)->limit(($page-1)*$pagesize, $pagesize)->select();

        return $this->joinArrayImages($list ,'image');
    }


    /**
     * 支付后 添加销量 减少库存  取消订单 添加库存  减少销量
     * @param  $staus 1) 支付  2）取消订单
     * @param  $cut_down_id 活动id
     * @param  $number 数量
     * @param status 1 减库存 加销量  2）加库存减销量
     * @param type 2） 修改团购数量
     * */
    public function updateSpec($groupbuy_id, $number, $staus = 1 ,$type)
    {
        $where = ['id' => $groupbuy_id];
        $info = $this->find_data($where, 'stock_num,group_nums');
        switch ($staus) {
            case 1: //添加销量 减少库存
                if ($number > $info['stock_num'])
                    return false;

                $update = [
                    'stock_num' => $info['stock_num'] - $number,
                    'group_nums' => $type == 2 ? $info['group_nums'] + 1 : info['group_nums'],
                ];

                break;

            case 2: //取消订单 添加库存
                $update = ['stock_num' => $info['stock_num'] + $number, 'group_nums' => $info['group_nums'] - 1];
                break;
        }
        $update['stock_num'] = $update['stock_num']< 0 ? 0 :$update['stock_num'];
        $update['group_nums'] = $update['group_nums']< 0 ? 0 :$update['group_nums'];
        $update_ = $this->editLimitDiscountGoods($update , $where);

        if ($update_)
            return true;
        else
            return false;
    }

}
