<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\CurriculumService;
use app\api\services\msjt\StudyService;
use app\api\services\traits\Auth;
use think\Request;

class Curriculum
{
    use Auth;

    /**
     * 课程列表
     * @param Request $request
     * @param CurriculumService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function list(Request $request, CurriculumService $service)
    {
        $uid = $this->checkToken('noNeed')->uid;
        $params = $request->param();
        return $service->lists($uid, $params);
    }

    /**
     * 课程详情
     * @param Request $request
     * @param CurriculumService $service
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(Request $request, CurriculumService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->detail($uid, $params);
    }

    /**
     * 下单
     * @param Request $request
     * @param CurriculumService $service
     * @return mixed
     * @throws \think\Exception
     */
    public function setOder(Request $request, CurriculumService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->setOder($uid, $params);
    }

    /**
     * 支付
     * @param Request $request
     * @param CurriculumService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pay(Request $request, CurriculumService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->pay($uid, $params['order_no']);
    }

    /**
     * 回调
     * @param Request $request
     * @param CurriculumService $service
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notifyurl(CurriculumService $service)
    {
        $service->notifyurl();
    }

    /**
     * 课程报名
     * @param Request $request
     * @param CurriculumService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sign(Request $request, CurriculumService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->sign($uid, $params);
    }

    /**
     * 学习人数
     * @param Request $request
     * @param CurriculumService $service
     * @throws \think\Exception
     */
    public function study(Request $request, StudyService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        $service->addStudyNum($uid, $params);
    }
}