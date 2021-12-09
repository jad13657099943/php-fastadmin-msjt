<?php

namespace app\common\model;

use think\Model;

class Litestorecoupondata extends Model
{
    protected $name = 'litestore_coupon_data';
    
    protected $autoWriteTimestamp = false;

    /**
     * 查询单条数据
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

    public function coupon()
    {
        return $this->hasOne('Litestorecoupon', 'id', 'litestore_coupon_id', null, 'LEFT')->setEagerlyType(0);
    }

    /*
     * 根据状态 改变优惠券状态
     * @param $coupon_id 优惠券ID
     * @param $uid
     * @param $status 1) 使用优惠券 2）退货优惠券
     * */
    public function update_coupon_status($coupon_id , $uid , $status){
        $where = ['user_id' => $uid , 'id' => $coupon_id];
        $coupon_info = $this->find_data($where ,'litestore_coupon_id');

        $coupon_model = model('Litestorecoupon');
        switch ($status){
            case 1: //使用
                $set_coupon_record = $this->update_data($where ,['is_used' => 1]);
                $set_coupon = $coupon_model->where(['id' => $coupon_info['litestore_coupon_id']])->setInc('use_num');
                $coupon_model->where(['id' => $coupon_info['litestore_coupon_id']])->setDec('remainder_num');
                break;
            case 2: //恢复
                $set_coupon_record = $this->update_data($where, ['is_used' => 0]);
                $set_coupon = $coupon_model->where(['id' => $coupon_info['litestore_coupon_id']])->setDec('use_num');
                $coupon_model->where(['id' => $coupon_info['litestore_coupon_id']])->setInc('remainder_num');
                break;
        }
        return true;
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
        return $this->where($where)->update($data);
    }

}