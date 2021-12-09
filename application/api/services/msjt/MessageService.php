<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Message;
use app\api\services\PublicService;

class MessageService extends PublicService
{
    /**
     * 提交反馈
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     */
    public function set($uid, $params)
    {
        $params['user_id'] = $uid;
        $status = Message::whereInsert($params);
        return $this->statusReturn($status, '提交成功', '提交失败');
    }
}