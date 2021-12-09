<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\GoodsService;
use app\api\services\traits\Auth;
use think\Request;

class Goods
{
    use Auth;

    /**
     * 商品列表
     * @param Request $request
     * @param GoodsService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function lists(Request $request, GoodsService $service)
    {
        $uid = $this->checkToken('noNeed')->uid;
        $params = $request->param();
        return $service->lists($uid, $params);
    }

    /**
     * 商品详情
     * @param Request $request
     * @param GoodsService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info(Request $request, GoodsService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->info($uid, $params['id']);
    }
}