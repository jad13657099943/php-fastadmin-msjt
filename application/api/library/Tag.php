<?php

namespace app\api\library;

use app\common\model\Litestorecoupondata;
use fast\Date;

class Tag
{
    /**
     * 用户注册赠送优惠券
     * @param $user
     * @return bool
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\db\exception\DataNotFoundException
     */
    public function userBindMobileSuccess($user)
    {
        if (!$user) {
            return false;
        }
        $time = time();
        $coupon = model('Litestorecoupon');

        $field = 'id,use_start_time,use_end_time,timedays,limit_type';
        $where = "(receive_start_time < $time or receive_start_time = 0) and (receive_end_time > $time or receive_end_time = 0) and find_in_set('newperson',type_data)";

        $list = $coupon->where($where)->field($field)->select();

        foreach ($list as $item) {
            if ($item->limit_type == 'timedays') {
                $startTime = $time;
                $endTime = $time + Date::DAY * $item->timedays;
            } else {
                $startTime = $item->use_start_time;
                $endTime = $item->use_end_time;
            }

            Litestorecoupondata::create([
                'user_id' => $user->id,
                'litestore_coupon_id' => $item->id,
                'get_type' => 'newpersion',
                'use_start_time' => $startTime,
                'use_end_time' => $endTime,
                'add_time' => $time,
            ]);
        }
    }
}