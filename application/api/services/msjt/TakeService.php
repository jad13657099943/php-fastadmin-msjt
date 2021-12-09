<?php


namespace app\api\services\msjt;


use app\api\model\msjt\CurriculumOrder;
use app\api\model\msjt\Grade;
use app\api\model\msjt\Order;
use app\api\model\msjt\Users;
use app\api\services\msjt\common\AgencyOrderService;
use app\api\services\msjt\take\UpgradeService;
use app\api\services\PublicService;
use think\Db;

class TakeService extends PublicService
{
    /**
     * 确认收货
     * @param $uid
     * @param $params
     * @return mixed
     */
    public function take($uid, $params)
    {
        return Db::transaction(function () use ($uid, $params) {
            $this->checkStatus($uid, $params);
            $status = $this->setOrderStatus($uid, $params);

            $service = new UpgradeService($uid);
            $service->upgrade();

            $service = new AgencyOrderService();
            $service->plusOrder($params['order_no']);

            return $this->statusReturn($status, '收货成功', '收货失败');
        });

    }

    /**
     * 验证是否可确认收货
     * @param $uid
     * @param $params
     * @throws \think\Exception
     */
    public function checkStatus($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['order_no'] = $params['order_no'];
        $status = Order::whereValue($where, 'status');
        switch ($status) {
            case 1:
                $this->error('订单待付款');
                break;
            case 2:
                $this->error('商家暂未发货');
                break;
            case 3:
                $this->error('订单已取消');
                break;
        }
    }

    /**
     * 确认收货
     * @param $uid
     * @param $params
     */
    public function setOrderStatus($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['order_no'] = $params['order_no'];
        return Order::whereUpdate($where, ['status' => 6]);
    }


}