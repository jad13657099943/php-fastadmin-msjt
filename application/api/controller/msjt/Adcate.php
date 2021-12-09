<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\AdcateService;
use think\Request;

class Adcate
{
    /**
     * 广告列表
     * @param Request $request
     * @param AdcateService $service
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list(Request $request, AdcateService $service)
    {
        $params = $request->param();
        return $service->list($params);
    }
}