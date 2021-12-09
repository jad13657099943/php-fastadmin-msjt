<?php


namespace app\api\services\msjt;


use app\api\model\msjt\CurriculumOrder;
use app\api\model\msjt\CurriculumSign;
use app\api\model\msjt\Order;
use app\api\model\msjt\OrderGoods;
use app\api\services\PublicService;

class NumService extends PublicService
{
    /**
     * 商品月销售
     * @param $id
     * @return float|int|string
     */
    public function goodsNum($id)
    {
        $where['status'] = ['in', [2, 4, 6]];
        $order_no_array = Order::where($where)->whereTime('createtime', 'month')->column('order_no');
        $wheres['goods_id'] = ['in', $order_no_array];
        return OrderGoods::whereSum($wheres, 'num');
    }

    /**
     * 课程报名或者观看人数
     * @param $id
     * @param $type
     * @return int|string
     * @throws \think\Exception
     */
    public function curriculumNum($id, $type)
    {
        $where['curriculum_id'] = $id;
        if ($type == 1) {
            $where['status'] = ['in', 2];
            $num = CurriculumOrder::whereCount($where);
        }
        if ($type == 2) {
            $num = CurriculumSign::whereCount($where);
        }
        return $num;
    }

}