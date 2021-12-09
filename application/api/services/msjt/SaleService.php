<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Order;
use app\api\model\msjt\OrderGoods;
use app\api\model\msjt\Sale;
use app\api\services\PublicService;
use Symfony\Component\DomCrawler\Tests\Field\InputFormFieldTest;
use think\Db;

class SaleService extends PublicService
{


    /**
     * 提交售后
     * @param $uid
     * @param $params
     * @return mixed
     */
    public function add($uid, $params)
    {
        return Db::transaction(function () use ($uid, $params) {
            if ($this->checkAdd($uid, $params)) {
                $status = Sale::whereInsert([
                    'user_id' => $uid,
                    'order_no' => $params['order_no'],
                    'order_goods_id' => json_encode($params['goods_id']),
                    'sale_no' => $this->orderNo('T'),
                    'sale_money' => $this->getSaleGoodsMoney($uid, $params),
                    'content' => $params['content'],
                    'saleimages' => $params['saleimages']
                ]);
            } else {
                $status = Sale::whereUpdate(['order_no' => $params['order_no']], [
                    'order_goods_id' => json_encode($params['goods_id']),
                    'sale_money' => $this->getSaleGoodsMoney($uid, $params),
                    'content' => $params['content'],
                    'status'=>1,
                    'saleimages' => $params['saleimages'],
                    'createtime'=>time()
                ]);
            }
            return $this->statusReturn($status, '提交成功', '提交失败');
        });

    }


    /**
     * 验证是否可提交售后
     * @param $uid
     * @param $params
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkAdd($uid, $params)
    {
        $where['order_no'] = $params['order_no'];

        $order = Order::whereFind($where);
        if ($order->status == 1 || $order->status == 3 || $order->status == 6) $this->error('该订单无法申请售后');

        $where['user_id'] = $uid;
        $info = Sale::whereFind($where);
        if (empty($info)) return true;

        switch ($info->status) {
            case 1:
                $this->error('售后审核中,请勿重复提交');
                break;
            case 2:
                return false;
                break;
            case 3:
                return false;
                break;
            case 4:
                $this->error('售后已通过,请勿重复提交');
                break;
        }
    }

    /**
     * 验证是否全部退款
     * @param $params
     * @return bool
     */
    public function checkAllRefund($params)
    {
        $where['order_no'] = $params['order_no'];
        $goodsId = OrderGoods::whereColumn($where, 'goods_id');
        if (count($goodsId) == count($params['goods_id'])) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 返回退款金额并修改订单状态
     * @param $uid
     * @param $params
     * @return float|int|string
     */
    private function getSaleGoodsMoney($uid, $params, $status = 2)
    {
        $this->setOrderGoodsStatus($params, $status);
        if ($this->checkAllRefund($params)) {
            $this->setOrderStatus($uid, $params, $status);
            return $this->getOderMoney($params);
        } else {
            return $this->getOderGoodsMoney($uid, $params);
        }
    }

    /**
     * 返回订单金额
     * @param $params
     * @return float|mixed|string
     */
    public function getOderMoney($params)
    {
        $where['order_no'] = $params['order_no'];
        return Order::whereValue($where, 'money');
    }

    /**
     * 返回部分订单商品价格
     * @param $uid
     * @param $params
     * @return float|int|string
     */
    public function getOderGoodsMoney($uid, $params)
    {
        $where['order_no'] = $params['order_no'];
        if (!empty($params['goods_id'])) {
            $where['goods_id'] = ['in', $params['goods_id']];
        }
        $where['user_id'] = $uid;
        return OrderGoods::whereSum($where, 'money');
    }

    /**
     * 全部售后隐藏订单
     * @param $uid
     * @param $params
     * @param int $status
     */
    public function setOrderStatus($uid, $params, $status = 2)
    {
        $where['user_id'] = $uid;
        $where['order_no'] = $params['order_no'];
        Order::whereUpdate($where, ['state' => $status]);
    }

    /**
     * 部分退隐藏部分订单商品
     * @param $params
     * @param int $status
     * @return \app\api\model\Kernel
     */
    public function setOrderGoodsStatus($params, $status = 2)
    {
        $where['order_no'] = $params['order_no'];
        if (!empty($params['goods_id'])) {
            $where['goods_id'] = ['in', $params['goods_id']];
        }
        return OrderGoods::whereUpdate($where, ['status' => $status]);
    }

    /**
     * 售后列表
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function lists($uid, $params)
    {
        $where['user_id'] = $uid;
        if (!empty($params['status'])) $where['status'] = $params['status'];

        $list = Sale::wherePaginate($where, '*', $params['limit'] ?? 10);
        foreach ($list as $item) {
            $item->goods = OrderGoods::whereSelect(['order_no' => $item->order_no, 'goods_id' => ['in', json_decode($item->order_goods_id, true)]]);
        }
        return $this->success('售后列表', $list);
    }

    /**
     * 售后详情
     * @param $uid
     * @param $params
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['id'] = $params['id'];
        $with = 'orders';
        $list = Sale::whereWithFind($with, $where);
        $list['goods'] = OrderGoods::whereSelect(['order_no' => $list->order_no, 'goods_id' => ['in', json_decode($list->order_goods_id, true)]]);
        return $this->success('售后列表', $list);
    }

    /**
     * 取消售后
     * @param $uid
     * @param $params
     * @return mixed
     */
    public function status($uid, $params)
    {
        return Db::transaction(function () use ($uid, $params) {
            $model = $this->checkCancel($uid, $params);
            $model->status = 2;
            $status = $model->save();
            $this->setOrderStatus($uid, $params, 1);
            $this->setOrderGoodsStatus($params, 1);
            return $this->statusReturn($status, '取消成功', '取消失败');
        });
    }

    /**
     * 验证是否可取消
     * @param $uid
     * @param $params
     * @return array|bool|\PDOStatement|string|\think\Model|null
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkCancel($uid, $params)
    {
        $where['user_id'] = $uid;
        $where['order_no'] = $params['order_no'];
        $model = Sale::whereFind($where);
        if (empty($model)) $this->error('售后信息未找到');
        switch ($model->status) {
            case 1:
                return $model;
                break;
            case 2:
                $this->error('售后已取消');
                break;
            case 3:
                return $model;
                break;
            case 4:
                $this->error('售后已通过,无法取消');
                break;
        }
    }
}