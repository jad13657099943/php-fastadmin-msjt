<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\ApplyService;
use app\api\services\traits\Auth;
use app\api\validate\msjt\ApplyValidate;
use think\Request;

class Apply
{
    use Auth;

    /**
     * 提交申请
     * @param Request $request
     * @param ApplyValidate $validate
     * @param ApplyService $service
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(Request $request, ApplyValidate $validate, ApplyService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        $validate->isCheck($params);
        return $service->add($uid, $params);
    }
}