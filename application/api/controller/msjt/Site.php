<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\SiteService;
use app\api\services\traits\Auth;
use think\Request;

class Site
{
    use Auth;

    /**
     * 地址列表
     * @param Request $request
     * @param SiteService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function site(Request $request, SiteService $service)
    {
        $this->checkToken();
        $params = $request->param();
        return $service->site($params);
    }
}