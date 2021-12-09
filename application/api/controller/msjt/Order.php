<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\OrderService;
use app\api\services\traits\Auth;
use app\api\validate\msjt\OrderValidate;
use think\Request;

class Order
{
    use Auth;

    /**
     * 下单
     * @param Request $request
     * @param OrderService $service
     * @return mixed
     * @throws \think\Exception
     */
    public function set(Request $request, OrderService $service, OrderValidate $validate)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        $validate->isCheck($params);
        return $service->set($uid, $params);
    }

    /**
     * 运费
     * @param OrderService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function freight(OrderService $service){
        $uid = $this->checkToken()->uid;
        return $service->freight();
    }

    /**
     * 微信支付
     * @param Request $request
     * @param OrderService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pay(Request $request, OrderService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->pay($uid, $params['order_no']);
    }

    /**
     * 回调
     * @param OrderService $service
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notifyurl(OrderService $service)
    {
        $service->notifyurl();
    }

}