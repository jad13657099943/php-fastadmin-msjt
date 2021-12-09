<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\TypeService;
use think\Request;

class Type
{
    /**
     * 商品分类
     * @param Request $request
     * @param TypeService $service
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function goodsType(Request $request, TypeService $service)
    {
        $params = $request->param();
        return $service->goodsType($params);
    }

    /**
     * 课程分类
     * @param Request $request
     * @param TypeService $service
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function curriculumType(Request $request, TypeService $service)
    {
        $params = $request->param();
        return $service->curriculumType($params);
    }

}