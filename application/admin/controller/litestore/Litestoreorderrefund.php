<?php

namespace app\admin\controller\litestore;

use app\common\controller\Backend;
use app\api\controller\Pay;
use app\common\model\Litestoreordergoods;
use app\common\model\UserRebateBack;
use think\Db;
use addons\epay\library\Service;
use Yansongda\Pay\Exceptions\GatewayException;

/**
 * 商品订单管理
 *
 * @icon fa fa-circle-o
 */
class Litestoreorderrefund extends Backend
{

    /**
     * Refund模型对象
     * @var \app\admin\model\Litestoreorderrefund
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Litestoreorderrefund;
        $this->auth->school_id != 0 && $this->assignconfig('school_id' , $this->auth->school_id);
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $this->relationSearch = true;
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->with(['liteStoreOrder'])
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->with(['liteStoreOrder' => function ($query) {
                    $query->field('total_price,nickname,mobile');
                }/*, 'users' => function ($query) {
                    $query->field('nickname,mobile');
                }*/])
                ->limit($offset, $limit)
                ->select();
            $field = 'order_id,goods_name,key_name,goods_price,total_num,images,total_price,is_refund';
            foreach ($list as $k => $value) {
                unset($list[$k]['lite_store_order']);
                $list[$k]['goods'] = model('Litestoreordergoods')
                    ->where(['id' => ['in', $value['order_goods_id']]])->field($field)->select();
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        return $this->view->fetch();
    }

    public function review($ids = '')
    {
        $row = $this->model->get($ids);
        $goodsList = Litestoreordergoods::all($row->order_goods_id);
        if ($this->request->isAjax()) {
            $remark = $this->request->param('remark');
            $status = $this->request->param('status');
            $ids = $this->request->get('ids');
            $order_goods_id = $this->request->param('order_goods_id');
            $row = $row ? $row : $this->model->get($ids);

            $row->review_time = time();
            $row->review_remark = $remark;
            $row->apply_status = $status + 1;
            $this->model->startTrans();
            try {
                //获取订单的信息
                $order = \app\common\model\Litestoreorder::getByOrderNo($row->order_no);
                if ($status == 1) {
                    //$row 是退单信息
                    $result = Pay::refund($row, $order);
                    $where = ['order_sn' => $order->order_no, 'type' => 1];
                    $distributionOrder = UserRebateBack::get($where);
                    $where['type'] = 2;
                    $twoDistributionOrder = UserRebateBack::get($where);
                    if ($order->order_refund == 1) {
                        $distributionOrder && $distributionOrder->status = 3;
                        $twoDistributionOrder && $twoDistributionOrder->status = 3;
                    } else {
                        $commission = 0;
                        $twoCommission = 0;
                        foreach ($goodsList as $goods) {
                            switch ($goods->activity_type) {
                                case 1://正常商品
                                    $commission += config('site.distribution_rate') * $goods->total_price;
                                    $twoCommission += config('site.two_distribution_rate') * $goods->total_price;
                                    break;
                                case 2://抢购商品
                                    $commission += config('site.spike_distribution_rate') * $goods->total_price;
                                    $twoCommission += config('site.two_spike_distribution_rate') * $goods->total_price;
                                    break;
                                case 4://拼团商品
                                case 5:
                                    $commission += config('site.group_distribution_rate') * $goods->total_price;
                                    $twoCommission += config('site.two_group_distribution_rate') * $goods->total_price;
                                    break;
                            }
                        }
                        $distributionOrder && $distributionOrder->money -= $commission;
                        $twoDistributionOrder && $twoDistributionOrder->money -= $twoCommission;
                    }
                    $distributionOrder && $distributionOrder->save();
                    $twoDistributionOrder && $twoDistributionOrder->save();
                } else {//拒绝时修改相应状态
                    $result = Litestoreordergoods::update(['is_refund' => 4], ['id' => ['in', $row->order_goods_id]]);
                    $refund_status = 30;
                    $order->order_refund == 1 && $order->order_refund = 0;
                }
                $order->refund_status = $refund_status;
                if ($row->save() && $result && $order->save()) {
                    $this->model->commit();
                    if ($order_goods_id)
                        return true;
                    $this->success('审核成功');
                }
                $this->model->rollback();
                if ($order_goods_id)
                    return false;
                $this->error('审核失败');
            } catch (GatewayException $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }

        }
        $row->liteStoreOrder;
        $this->assign(['row' => $row, 'goodsList' => $goodsList]);
        return $this->fetch();
    }

    public function detail($ids = '')
    {
        $row = $this->model->get($ids);
        $goodsList = Litestoreordergoods::all($row->order_goods_id);
        $row->liteStoreOrder;
        $this->assign(['row' => $row, 'goodsList' => $goodsList]);
        return $this->fetch();
    }
}
