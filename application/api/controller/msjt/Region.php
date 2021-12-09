<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\RegionService;
use app\api\services\traits\Auth;
use app\api\validate\msjt\RegionValidate;
use think\Request;

class Region
{
    use Auth;

    /**
     * 添加地址
     * @param Request $request
     * @param RegionService $service
     * @param RegionValidate $validate
     * @return false|string
     * @throws \think\Exception
     */
    public function set(Request $request, RegionService $service, RegionValidate $validate)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        $validate->isCheck($params);
        return $service->set($uid, $params);
    }

    /**
     * 设置默认地址
     * @param Request $request
     * @param RegionService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function setDefault(Request $request, RegionService $service)
    {
        $uid= $this->checkToken()->uid;
        $params = $request->param();
        return $service->setDefault($uid,$params['id']);
    }

    /**
     * 删除地址
     * @param Request $request
     * @param RegionService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function del(Request $request, RegionService $service)
    {
        $this->checkToken();
        $params = $request->param();
        return $service->del($params['id']);
    }

    /**
     * 编辑地址
     * @param Request $request
     * @param RegionService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function update(Request $request, RegionService $service)
    {
        $uid= $this->checkToken()->uid;
        $params = $request->param();
        return $service->update($uid,$params['id'], $params);
    }

    /**
     * 地址列表
     * @param Request $request
     * @param RegionService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function lists(Request $request, RegionService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->lists($uid, $params);
    }

}