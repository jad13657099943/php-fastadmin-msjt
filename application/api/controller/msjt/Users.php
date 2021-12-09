<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\TakeService;
use app\api\services\msjt\UsersService;
use app\api\services\traits\Auth;
use app\api\validate\msjt\MobileValidate;
use app\api\validate\msjt\RegisterValidate;
use think\Request;

class Users
{
    use Auth;

    /**
     * wx授权登录
     * @param Request $request
     * @param RegisterValidate $validate
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function wxRegister(Request $request, RegisterValidate $validate, UsersService $service)
    {
        $params = $request->param();
        $validate->isCheck($params);
        return $service->wxRegister($params);
    }

    /**
     * 设置手机
     * @param Request $request
     * @param MobileValidate $validate
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function mobile(Request $request, MobileValidate $validate, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        $validate->isCheck($params);
        return $service->mobile($uid, $params);
    }

    /**
     * 用户信息
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info(UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        return $service->info($uid);
    }

    /**
     * 编辑用户信息
     * @param Request $request
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function set(Request $request, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->set($uid, $params);
    }

    /**
     * 我的订单
     * @param Request $request
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function order(Request $request, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->order($uid, $params);
    }

    /**
     * 订单详情
     * @param Request $request
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(Request $request, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->detail($uid, $params);
    }

    /**
     * 取消订单
     * @param Request $request
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function del(Request $request, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->del($uid, $params);
    }

    /**
     * 确认收货
     * @param Request $request
     * @param TakeService $service
     * @return mixed
     * @throws \think\Exception
     */
    public function take(Request $request, TakeService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->take($uid, $params);
    }

    /**
     * 我的课程
     * @param Request $request
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function curriculum(Request $request, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->curriculum($uid, $params);
    }

    /**
     * 删除课程订单
     * @param Request $request
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function delCurriculum(Request $request, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->delCurriculum($uid, $params);
    }

    /**
     * 取消课程订单
     * @param Request $request
     * @param UsersService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function cancelCurriculum(Request $request, UsersService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->cancelCurriculum($uid, $params);
    }

}