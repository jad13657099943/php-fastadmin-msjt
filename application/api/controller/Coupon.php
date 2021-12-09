<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;


/**
 * 优惠券接口
 */
class Coupon extends Api
{
    protected $noNeedLogin = ['userCouponList'];
    protected $noNeedRight = ['*'];

    protected $coupon = null;
    protected $couponrecord = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->coupon = model('common/litestore/Coupon');
        $this->couponrecord = model('common/litestore/CouponData');
    }

    /**
     * 领卷中心优惠劵列表
     */
    public function coupon_list()
    {
        $where = ['status' => 1, 'get_type' => 1];
        $field = 'id,name,enough,coupon_type,deduct,discount,total,get_max,
        remainder_num,receive_start_time,receive_end_time,receive_num';

        $list = $this->coupon->where($where)->field($field)->withCount(['couponData' => function ($query) {
            $query->where('user_id', $this->auth->id);
        }])->select();

        $this->success('获取成功', ['list' => $list]);

    }

    /**
     * 领取优惠劵
     * @id 优惠劵id
     * @uid 用户id
     */
    public function getCoupon()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
//        $uid = 16;
        !$params['coupon_id'] && $this->error('优惠劵不存在');

        //优惠劵信息
        $coupon_info = $this->coupon->find_data(['id'=>$params['coupon_id']],[]);

        !$coupon_info && $this->error('优惠劵不存在');

        //判断优惠券状态是否正常
        $coupon_info['status'] == 0 && $this->error('该优惠劵已下架');

        //判断优惠券是否在可领取时间内?
        //判断是否有数量限制
        if ($coupon_info['total']) {
            //判断优惠券剩余数量是否>0
            if ($coupon_info['remainder_num'] <= 0) {
                $save['status'] = 0;
                $this->coupon->save_data(['id' => $params['coupon_id']], $save,'');
                $this->error('优惠劵库存不足');
            }
        }

        //判断用户是否已经领取该优惠券
        $where = ['user_id' => $uid, 'litestore_coupon_id' => $params['coupon_id']];
        if ($this->couponrecord->where($where)->count() >= $coupon_info['get_max']) {

            $this->error('领取数量已达到上限');
        }

        //生成领取优惠劵记录
        $add_r = [
            'user_id' => $uid,
            'litestore_coupon_id' => $params['coupon_id'],
            'get_type' => 'couponcenter'
        ];

        //使用时间
        if ($coupon_info['limit_type'] == 'timedays') {
            $day = $coupon_info['timedays'];
            $add_r['use_start_time'] = time();

            $add_r['use_end_time'] = strtotime($day . " day");
        } else {
            $add_r['use_start_time'] = $coupon_info['use_start_time'];
            $add_r['use_end_time'] = $coupon_info['use_end_time'];
        }
        //添加数据
        Db::startTrans();
        if ($this->couponrecord->save_data([], $add_r,'')) {
            //领取优惠劵，优惠劵领取数量加一
            $data = [
                'receive_num' => $coupon_info['receive_num'] + 1,
            ];

            //判断是否有数量限制(减少剩余数量)
            if ($coupon_info['total'])
                $data['remainder_num'] = $coupon_info['remainder_num'] - 1;

            $res = $this->coupon->save_data(['id' => $params['coupon_id']], $data,'');
            if ($res) {
                Db::commit();
                $this->success('领取优惠劵成功');
            }
        }
        Db::rollback();
        $this->error('领取优惠劵失败');
    }

    /*
     * 用户优惠劵列表
     * @uid 用户id
     * @$is_used  0)未使用 1)已使用
     * @ type  1)未使用 2)已使用 3）已过期
     */
    public function userCouponList()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
//        $uid = 16;
        empty($params['type']) && $this->error('缺少优惠券类型');
        $where = ['user_id' => $uid];
        switch ($params['type']) {
            case 1:
                $where['is_used'] = 0;
                $where['use_end_time'] = ['>', time()];
                break;
            case 2:
                $where['is_used'] = 1;
                break;
            case 3:
                $where['use_end_time'] = ['<', time()];
                break;
        }

        //获取用户优惠劵信息
        $field = 'id,litestore_coupon_id,get_type,add_time,use_time,is_new,use_start_time,use_end_time';
        $with = ['coupon' => function ($query) {
            $coupon_field = 'id,category_id,name,enough,coupon_type,deduct,discount';
            $query->withField($coupon_field);
        }];

        $record_list = $this->couponrecord->select_data($where, $field, 'id desc', $with);
//        //未使用优惠券
//        if ($params['type'] == 1) {
//            //判断优惠券是否过期
//            if ($record_list) {
//                foreach ($record_list as $k => $v) {
//                    if (time() > $v['$use_end_time']) {
//                        $this->couponrecord->save_data(['id' => $v['id']], ['status' => 3]);
//                    }
//                }
//            }
//        }

        //获取优惠劵数量
        $notUsed = $this->couponrecord->where(['is_used' => 0, 'use_end_time' => ['>', time()], 'user_id' => $uid])->count();
        $AlreadyUsed = $this->couponrecord->where(['is_used' => 1, 'user_id' => $uid])->count();
        $Expired = $this->couponrecord->where(['use_end_time' => ['<', time()], 'user_id' => $uid])->count();

        $this->success('', ['list' => $record_list, 'notUsed' => $notUsed, 'AlreadyUsed' => $AlreadyUsed, 'Expired' => $Expired]);

    }

}