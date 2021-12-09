<?php


namespace app\api\controller\msjt;


use app\api\services\msjt\MessageService;
use app\api\services\traits\Auth;
use think\Request;

class Message
{
    use Auth;

    /**
     * 提交反馈
     * @param Request $request
     * @param MessageService $service
     * @return false|string
     * @throws \think\Exception
     */
    public function set(Request $request, MessageService $service)
    {
        $uid = $this->checkToken()->uid;
        $params = $request->param();
        return $service->set($uid, $params);
    }
}