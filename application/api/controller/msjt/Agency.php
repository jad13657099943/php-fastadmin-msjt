<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\AgencyService;
use app\api\services\msjt\withdraw\WithdrawService;
use app\api\services\traits\Auth;
use think\Request;

class Agency
{
    use Auth;

    /**
     * 人员统计
     * @param Request $request
     * @param AgencyService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function total(Request $request, AgencyService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->total($uid, $params);
    }

    /**
     * 分销订单
     * @param Request $request
     * @param AgencyService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function order(Request $request, AgencyService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->order($uid, $params);
    }

    /**
     * 佣金明细
     * @param Request $request
     * @param AgencyService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function balance(Request $request, AgencyService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->balance($uid, $params);
    }

    /**
     * 推广中心
     * @param AgencyService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function centre(AgencyService $service)
    {
        $uid = $this->checkToken()->uid;
        return $service->centre($uid);
    }

    /**
     * 提现
     * @param Request $request
     * @return mixed
     * @throws \think\Exception
     */
    public function withdraw(Request $request)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        $service = new WithdrawService($uid, $params['type'], $params['money'], $params['info']);
        return $service->submit();
    }
}