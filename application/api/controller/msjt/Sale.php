<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\SaleService;
use app\api\services\traits\Auth;
use think\Request;

class Sale
{
    use Auth;

    /**
     * 提交售后
     * @param Request $request
     * @param SaleService $service
     * @throws \think\Exception
     */
    public function add(Request $request, SaleService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->add($uid, $params);
    }

    /**
     * 售后列表
     * @param Request $request
     * @param SaleService $service
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function lists(Request $request, SaleService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->lists($uid, $params);
    }

    /**
     * 售后详情
     * @param Request $request
     * @param SaleService $service
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(Request $request, SaleService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->detail($uid, $params);
    }

    /**
     * 取消售后
     * @param Request $request
     * @param SaleService $service
     * @throws \think\Exception
     */
    public function status(Request $request, SaleService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->status($uid, $params);
    }
}