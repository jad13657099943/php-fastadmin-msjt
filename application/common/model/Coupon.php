<?php

namespace app\common\model;

use think\Model;

/**
 * 会员模型
 */
class Coupon Extends Model
{

    // 表名
    protected $name = 'coupon';

    //获取优惠劵列表
    public function getCouponList($where,$field,$order='createtime desc',$page=1,$pagesize=10)
    {
        return $this->field($field)->where($where)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }

    /*
     * 获取单个优惠劵
     */
    public function getOneCoupon($id ,$field='*',$order='id desc')
    {
        $info = $this->where(['id' => $id])->field($field)->order($order)->find();
        return $info;
    }

    /*
     * 查询多条数据
     *
     */
    public function select_data($where,$field='*',$order='id desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }

    public function save_data($where,$data){
        return $this->where($where)->save($data);
    }



    /*
      * 获取单个优惠劵
      */
    public function find_data($where ,$field='*',$order='id desc')
    {
        $info = $this->where($where)->field($field)->order($order)->find();
        return $info;
    }

    /**
     * 修改信息
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update_data($where = [],$data = [])
    {
        if (empty($where)) {
            return false;
        }
        return $this->where($where)->save($data);
    }

    //优惠劵领取成功之后减少优惠券数量和增加优惠券领取数量

    public function after_get_coupon($coupon_id)
    {
        if ($coupon_id){
            $coupon_number = $this->where(['id' => $coupon_id])->value('number');
            if ($coupon_number <= 0){
                return false;
            }
            $this->where(['id' => $coupon_id])->setInc('receive_number');
            $this->where(['id' => $coupon_id])->setDec('number');
        }
        return true;

    }

}