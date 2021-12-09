<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\SearchService;
use app\api\services\traits\Auth;
use think\Request;

class Search
{
    use Auth;

    /**
     * 搜素记录
     * @param Request $request
     * @param SearchService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function list(Request $request, SearchService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->list($uid, $params);
    }

    /**
     * 猜你喜欢
     * @param Request $request
     * @param SearchService $service
     * @return false|string
     */
    public function hot(Request $request, SearchService $service)
    {
        $params = $request->param();
        return $service->hot($params);
    }

    /**
     * 删除搜索记录
     * @param Request $request
     * @param SearchService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function del(Request $request, SearchService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->del($uid, $params);
    }

}