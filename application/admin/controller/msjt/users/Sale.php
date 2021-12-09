<?php

namespace app\admin\controller\msjt\users;

use addons\epay\library\Service;
use app\admin\model\msjt\users\order\Agency;
use app\admin\model\msjt\users\OrderGoods;
use app\api\controller\Order;
use app\api\services\msjt\common\AgencyOrderService;
use app\api\services\msjt\SaleService;
use app\common\controller\Backend;
use app\common\model\Litestoreorderrefund;
use think\Db;
use think\Exception;
use think\Request;

/**
 * 申请售后管理
 *
 * @icon fa fa-circle-o
 */
class Sale extends Backend
{

    /**
     * Sale模型对象
     * @var \app\admin\model\msjt\users\Sale
     */
    protected $model = null;
    protected $user = null;
    protected $goods = null;
    protected $order = null;
    protected $agency = null;
    protected $status = [
        '' => '',
        1 => '待审核',
        2 => '已取消',
        3 => '已拒绝',
        4 => '已通过',
    ];
    protected $order_status = [
        '' => '',
        1 => '待支付',
        2 => '待发货',
        3 => '已取消',
        4 => '待收货',
        5 => '已完成'
    ];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\msjt\users\Sale;
        $this->user = new \app\admin\model\msjt\users\Users();
        $this->goods = new OrderGoods();
        $this->agency = new Agency();
        $this->order = new \app\admin\model\msjt\users\Order();
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function info($ids = '')
    {
        $info = $this->model->where('id', $ids)->find();
        $info['status'] = $this->status[$info->status];
        $info['order'] = $this->order->where('order_no', $info->order_no)->field('status,freight')->find();
        $info['order_status'] = $this->order_status[$info['order']['status']];
        $info['user'] = $this->user->where('id', $info->user_id)->find();
        $info['goods'] = $this->goods->where('order_no', $info->order_no)->where('goods_id', 'in', json_decode($info->order_goods_id, true))->select();
        foreach ($info['goods'] as $good) {
            $good->info = json_decode($good->info, true);
            $good->sku = json_decode($good->sku, true);
        }
        $info['createtime'] = $info['createtime'] ? date('Y-m-d H:i:s', $info['createtime']) : '';
        $info['saleimages'] = explode(',', $info->saleimages) ?? '';
        $this->view->assign('info', $info);
        return $this->view->fetch();
    }


    public function refund($ids = '')
    {
        $info = $this->model->get($ids);
        if ($info->status != 1) $this->error('状态变化');
        Db::transaction(function () use ($info, $ids) {
            $where['order_no'] = $info['order_no'];
            $goodsId = $this->goods->where($where)->column('goods_id');
            $orderGoodsId = json_decode($info->order_goods_id, true);

            $model = $this->order->where($where)->find();

            if (count($goodsId) == count($orderGoodsId)) {
                $model->state = 2;
                $model->status = 8;
                $this->agency->where($where)->delete();
            } else {
                $goods_num = $model->goods_num - $info->sale_money;
                $model->goods_num = $goods_num;
                $money = $model->money - $info->sale_money;
                $model->money = $money;
                $infos = $this->agency->get($where);
                if (!empty($infos)) {
                    $service = new AgencyOrderService();
                    $infos->money = $money;
                    $infos->agency = $money * $infos->bl;
                    $infos->info = $service->getGoods(1, $info['order_no']);
                    $infos->save();
                }
            }
            $model->save();
            $this->goods->where($where)->where('goods_id', 'in', $orderGoodsId)->update(['status' => 2]);
            $this->model->where('id', $ids)->update(['status' => 4]);
            $this->wxRefund($info['order_no'],$info['sale_no'],$model['money'],$info['sale_money'],$info['content']??'未说明');

        });

        $this->success('退款成功');
    }

    public function wxRefund($order_no, $refund_no, $order_price, $refund_price, $remark='未说明')
    {
        $pay = new \Yansongda\Pay\Pay(Service::getConfig('wechat'));
        $params = [
            'out_trade_no' => $order_no,//商户订单号
            'out_refund_no' => $refund_no,//退款单号
            'total_fee' => $order_price * 100,//订单金额 必须是整数
            'refund_fee' => $refund_price * 100,//退款金额 必须是整数
            'refund_desc' => $remark,//退款原因
            'notify_url' =>Request::instance()->root(true). '/refund/notify',//退款原因
        ];

        $result = $pay->driver('wechat')->gateway('miniapp')->refund($params);
        if ($result && $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return true;
        } else {
            $this->error('退款失败');
        }
    }




    public function review($ids = '')
    {
        $model = $this->model->where('id', $ids)->find();
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($model->status != 1) $this->error('状态变化');
            Db::transaction(function () use ($ids, $params, $model) {
                $this->model->where('id', $ids)->update([
                    'review_time' => time(),
                    'review_remark' => $params['review_remark'],
                    'status' => 3]);
                $where['order_no'] = $model->order_no;
                $this->order->where($where)->update(['state' => 1]);
                $this->goods->where($where)->update(['status' => 1]);
            });

            $this->success('审核成功');
        }
        $this->view->assign('row', $model);
        $this->view->assign('ids', $ids);
        return $this->view->fetch();
    }

}
