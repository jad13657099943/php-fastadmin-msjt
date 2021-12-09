<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Apply;
use app\api\services\PublicService;

class ApplyService extends PublicService
{
    /**
     * 提交申请
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add($uid, $params)
    {
        if ($this->isApply($uid)) {
            $params['user_id'] = $uid;
            $params['status'] = 1;
            $status = Apply::whereInsert($params);
            return $this->statusReturn($status, '提交成功', '提交失败');
        }
    }

    /**
     * 验证申请状态
     * @param $uid
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function isApply($uid)
    {
        $where['user_id'] = $uid;
        $info = Apply::whereFind($where);
        if (empty($info)) return true;
        switch ($info->status) {
            case 1:
                $this->error('待审核,请勿重复提交');
                break;
            case 2:
                $this->error('已通过,请勿再次提交');
                break;
            case 3:
                return true;
                break;
            default:
                return true;
        }
        return true;
    }

    /**
     * 获取申请状态
     * @param $uid
     * @return float|mixed|string
     */
    public function getApplyStatus($uid)
    {
        $where['user_id'] = $uid;
        return Apply::whereValue($where, 'status');
    }
}