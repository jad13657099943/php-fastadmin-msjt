<?php

namespace app\common\model;

use think\Model;

class Litestorecoupon extends Model
{

    protected $updateTime = false;
    protected $name = 'litestore_coupon';

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

    /**
     * 获取首页推荐优惠券
     * @param $userId
     * @return array|string
     * @throws \think\exception\DbException
     */
    public static function checkCoupon($userId)
    {
        $coupon = self::get(['is_index' => 1]);
        if ($coupon && $userId) {
            if (!Litestorecoupondata::get(['user_id' => $userId, 'litestore_coupon_id' => $coupon->id])) {
                    return [
                        'id' => $coupon->id,
                        'img' => config('item_url') . $coupon->icon_image,
                    ];
            }
        }
        $where['user_id'] = ['eq',$userId];
        $where['litestore_coupon_id'] =['eq',$coupon->id];
        $data = new Litestorecoupondata();
        $aa = $data->where($where)->count();
        if ($coupon->get_max <= $aa){
            return null;
        }else{
            return [
                'id' => $coupon->id,
                'img' => config('item_url') . $coupon->icon_image,
            ];
        }


        /*if ($userId && !self::where('is_index', 1)->where("find_in_set($userId,read_user)")->find()) {
            $coupon = self::get(['is_index' => 1]);
            if ($coupon) {
                $coupon->read_user .= "$userId,";
                $coupon->save();
                return $coupon->id;
            }
        }
        return 0;*/
    }
}