<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\CollectionService;
use app\api\services\traits\Auth;
use think\Request;

class Collection
{
    use Auth;

    /**
     * 添加收藏
     * @param Request $request
     * @param CollectionService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(Request $request, CollectionService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->add($uid, $params);
    }

    /**
     * 删除收藏
     * @param Request $request
     * @param CollectionService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function del(Request $request, CollectionService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->del($uid, $params);
    }

    /**
     * 收藏列表
     * @param Request $request
     * @param CollectionService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function lists(Request $request, CollectionService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->lists($uid, $params);
    }
}