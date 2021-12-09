<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Agency;
use app\api\model\msjt\Balance;
use app\api\model\msjt\Order;
use app\api\model\msjt\Users;
use app\api\services\PublicService;

class AgencyService extends PublicService
{
    /**
     * 人员统计
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function total($uid, $params)
    {
        $where['pid'] = $uid;
        $one_num = Users::whereCount($where);

        $user = Users::whereColumn($where, 'id');
        if (!empty($user)) {
            $wheres['pid'] = ['in', $user];
            $two_num = Users::whereCount($wheres);
        } else {
            $wheres['id'] = ['<', 1];
        }
        if ($params['type'] == 1) {
            $list = Users::wherePaginate($where, 'id,nickname,head_image', $params['limit'] ?? 10);
        } else {

            $list = Users::wherePaginate($wheres, 'id,nickname,head_image', $params['limit'] ?? 10);
        }

        $data = [
            'one_num' => $one_num,
            'two_num' => $two_num ?? 0,
            'list' => $list ?? []
        ];

        return $this->success('人员统计', $data);
    }

    /**
     * 分销订单
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function order($uid, $params)
    {
        $where['user_id'] = $uid;
        if (!empty($params['status'])) $where['status'] = $params['status'];
        $with = 'user';
        $list = Agency::whereWithPaginate($with, $where, '*', $params['limit'] ?? 10);
        foreach ($list as $item) {
            $item->info = json_decode($item->info, true);
        }
        return $this->success('分销订单记录', $list);
    }

    /**
     *佣金明细
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function balance($uid, $params)
    {
        $where['user_id'] = $uid;
        $list = Balance::wherePaginate($where, '*', $params['limit'] ?? 10);
        foreach ($list as $item) {
            $item->money = $item->money > 0 ? '+' . $item->money : $item->money;
        }
        return $this->success('佣金明细', $list);
    }

    /**
     * 推广中心
     * @param $uid
     * @return false|string
     */
    public function centre($uid)
    {
        $where['id'] = $uid;
        $data = [
            'balance' => $balance = Users::whereValue($where, 'balance'),
            'total' => $total = Balance::whereSum(['user_id' => $uid, 'money' => ['>', 0]], 'money'),
            'withdraw' => $withdraw = Balance::whereSum(['user_id' => $uid, 'money' => ['<', 0]], 'money'),
            'yesterday' => $yesterday = Balance::whereTime('createtime', 'yesterday')->where(['user_id' => $uid, 'money' => ['>', 0]])->sum('money')
        ];
        return $this->success('推广中心', $data);
    }
}