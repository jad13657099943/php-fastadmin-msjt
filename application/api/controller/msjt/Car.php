<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\CarService;
use app\api\services\traits\Auth;
use think\Request;

class Car
{
    use Auth;

    /**
     * 购物车
     * @param Request $request
     * @param CarService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function lists(Request $request, CarService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->lists($uid, $params);
    }

    /**
     * 加入购物车
     * @param Request $request
     * @param CarService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function add(Request $request, CarService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->add($uid, $params);
    }

    /**
     * 删除购物车
     * @param Request $request
     * @param CarService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function del(Request $request, CarService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->del($uid, $params);
    }

    /**
     * 编辑购物车
     * @param Request $request
     * @param CarService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function edit(Request $request, CarService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->edit($uid, $params);
    }
}