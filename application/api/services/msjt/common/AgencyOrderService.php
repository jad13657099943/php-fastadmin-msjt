<?php


namespace app\api\services\msjt\common;


use app\api\model\msjt\Agency;
use app\api\model\msjt\Balance;
use app\api\model\msjt\Config;
use app\api\model\msjt\CurriculumOrder;
use app\api\model\msjt\Goods;
use app\api\model\msjt\Grade;
use app\api\model\msjt\Order;
use app\api\model\msjt\OrderGoods;
use app\api\model\msjt\Type;
use app\api\model\msjt\Users;
use think\Db;

class AgencyOrderService
{
    /**
     * 记录分销订单
     * @param $order_no
     * @param $type
     * @param $status
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addAgencyOrder($order_no, $type, $status)
    {
        Db::transaction(function () use ($order_no, $type, $status) {
            $where['order_no'] = $order_no;
            if ($type == 1) {
                $info = Order::whereFind($where);
            } else {
                $info = CurriculumOrder::whereFind($where);
            }

            if ($pid = $this->isPid($info->user_id)) {
                $this->addAgency($pid, $info, 1, $type, $status);
                if ($two_pid = $this->isPid($pid)) {
                    $this->addAgency($two_pid, $info, 2, $type, $status);
                }
            }
        });

    }


    /**
     * 是否存在上级
     * @param $uid
     * @return false|float|mixed|string
     */
    public function isPid($uid)
    {
        $pid = Users::whereValue(['id' => $uid], 'pid');
        $dai = Users::whereValue(['id' => $pid], 'dai');
        if (empty($pid) || $dai != 2) {
            return false;
        } else {
            return $pid;
        }
    }

    /**
     * 记录分销订单
     * @param $pid
     * @param $info
     * @param $level
     * @param $type
     * @param $status
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addAgency($pid, $info, $level, $type, $status)
    {
        $bl = $this->getBl($level);
        $agency = $info->money * $bl;
        Agency::whereInsert([
            'user_id' => $pid,
            'users' => $info->user_id,
            'bl' => $bl,
            'order_no' => $info->order_no,
            'info' => $this->getGoods($type, $info->order_no),
            'money' => $info->money,
            'agency' => $agency,
            'type' => $type,
            'status' => $status,
        ]);

        if ($status == 2) {
            $this->plus($pid, $agency);
        }
    }

    /**
     * 增加余额并记录
     * @throws \think\Exception
     */
    public function plus($uid = '', $money = '', $name = '分销提成')
    {
        Db::transaction(function () use ($uid, $money, $name) {
            $this->balance($uid, $money, $name);
        });
    }

    /**
     * 分销订单增加余额并记录
     * @param string $order_no
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function plusOrder($order_no = '')
    {
        if (!empty($order_no)) {
            $model = Agency::whereSelect(['order_no' => $order_no, 'status' => 1]);
            if (empty($model)) return true;
            Agency::whereUpdate(['order_no' => $order_no], ['status' => 2]);
            foreach ($model as $item) {
                $this->balance($item->user_id, $item->agency);
            }
            return true;
        }
    }

    /**
     * 余额增加
     * @param $uid
     * @param $money
     * @param $name
     */
    public function balance($uid, $money, $name = '分销提成')
    {
        Db::transaction(function () use ($uid, $money, $name) {
            Users::whereSetInc(['id' => $uid], 'balance', $money);
            if ($name = '分销提成') Users::whereSetInc(['id' => $uid], 'total_balance', $money);
            Balance::whereInsert([
                'user_id' => $uid,
                'name' => $name,
                'money' => $money,
            ]);
        });

    }

    /**
     * 获取比例
     * @param $level
     * @return float|mixed|string
     */
    public function getBl($level)
    {
        if ($level == 1) {
            return Config::whereValue(['name' => 'distribution_rate'], 'value');
        }
        if ($level == 2) {
            return Config::whereValue(['name' => 'two_distribution_rate'], 'value');
        }
    }

    /**
     * 获取订单商品信息
     * @param $type
     * @param $order_no
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoods($type, $order_no)
    {
        if ($type == 1) {
            $list = OrderGoods::whereSelect(['order_no' => $order_no, 'status' => 1]);
            foreach ($list as $item) {
                $info = json_decode($item->info);
                $sku = json_decode($item->sku);
                $data[] = [
                    'name' => $info->name,
                    'simages' => $info->simages,
                    'sku' => $sku->name,
                    'price' => $item->unit_price,
                    'num' => $item->num,
                ];
            }
        }
        if ($type == 2) {
            $list = CurriculumOrder::whereFind(['order_no' => $order_no]);
            $info = json_decode($list['info']);
            $data[] = [
                'name' => $info->name,
                'simages' => $info->simages,
                'sku' => '',
                'price' => $item->money,
                'num' => 1
            ];
        }
        return json_encode($data);
    }

}