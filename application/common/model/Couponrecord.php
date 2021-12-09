<?php

namespace app\common\model;

use think\Model;

/**
 * 会员模型
 */
class Couponrecord Extends Model
{

    // 表名
    protected $name = 'coupon_record';

    public function user()
    {
        return $this->belongsTo('user', 'uid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function coupon()
    {
        return $this->belongsTo('coupon', 'coupon_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /*
     * 查询多条数据
     *
     */

    public function select_data($where,$field='*',$order='id desc')
    {

        return $this->where($where)->field($field)->order($order)->select();
    }

    /*
     * 获取分页数据
     *
     */
    public function select_page($where,$field='*',$order='id desc',$page=0,$pagesize=10)
    {

        return $this->where($where)->field($field)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }


    /*
     * 添加数据
     *
     */

    public function add_data($data){
        $this->data($data);
        return $this->save();
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
        return $this->where($where)->update($data);
    }


    public function save_data($where = [] , $data){
        return $this->where($where)->save($data);
    }



    /*
     * 根据状态 改变优惠券状态
     * @param $coupon_id 优惠券ID
     * @param $uid
     * @param $status 1) 使用优惠券 2）退货优惠券
     * */
    public function update_coupon_status($coupon_id , $uid , $status){
        $where = ['uid' => $uid , 'id' => $coupon_id];
        $coupon_info = $this->find_data($where ,'coupon_id');

        $coupon_model = model('Coupon');
        switch ($status){
            case 1: //使用
                $set_coupon_record = $this->update_data($where ,['status' => 2]);
                $set_coupon = $coupon_model->where(['id' => $coupon_info['coupon_id']])->setInc('complete_number');
                break;
            case 2: //恢复
                $set_coupon_record = $this->update_data($where, ['status' => 1]);
                $set_coupon = $coupon_model->where(['id' => $coupon_info['coupon_id']])->setDec('complete_number');
                break;
        }
        return true;
    }
}