<?php

namespace app\admin\controller\distribution;

use addons\epay\library\Service;
use app\common\controller\Backend;
use app\common\model\Litestoreorderrefund;
use app\common\model\UserRebate;

/**
 * 代理商申请管理
 *
 * @icon fa fa-circle-o
 */
class Apply extends Backend
{

    /**
     * Apply模型对象
     * @var \app\admin\model\user\agent\Apply
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\user\agent\Apply;
        $this->user = new \app\common\model\User;

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


        //当前是否为关联查询
//        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $wheres['store_status'] = ['neq', 1];
//            $wheres['pay_time'] = ['neq', 0];
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $result = admin_list($this->model, $where, $sort, $order, $offset, $limit, $wheres);
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 代理商审核 拒绝
     */
    public function refuse($ids = '')
    {
        $row = $this->model->get($ids);
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');

            if ($row) {
//                //退款流水数据
//                $refundData = [
//                    'order_no' => $row->order_no,
//                    'money' => $row->order_no,
//                    'refund_no' => order_sn(9),
//                ];


                //提交数据
//                $params = [
//                    'out_trade_no' => $row->order_no,//商户订单号
//
//                    'out_refund_no' => $refundData['refund_no'],//退款单号
//                    'total_fee' => $row->pay_money * 100,//订单金额 必须是整数
//                    'refund_fee' => $row->pay_money * 100,//退款金额 必须是整数
//                    'refund_desc' => '申请被拒',//退款原因
//                    'notify_url' => config('url_domain_root') . '/api/pay/agentRefund/',//退款原因
//                ];
                $row->store_status = 2;
                $row->msg = $params['msg'];
//                try {
//                    $this->model->startTrans();
//                    $pay = new \Yansongda\Pay\Pay(Service::getConfig('wechat'));
                    // 在判断里面的不知道什么用&& Litestoreorderrefund::create($refundData) && $pay->driver('wechat')->gateway('miniapp')->refund($params)
//                    if ($row->save()&& Litestoreorderrefund::create($refundData) && $pay->driver('wechat')->gateway('miniapp')->refund($params)) {
//                        $this->model->commit();
//                        $this->success('拒绝成功');
//                    }
                    $data =$row->save();
                    if ($data){
//                        $this->model->commit();
                        $this->success('拒绝成功');
                    }
//                }catch (\Exception $e) {
//                    $this->error($e->getMessage());
//                }
//                $this->model->rollback();
            }
            $this->error('拒绝失败');
        }
        $this->assign('row', $row);
        return $this->fetch();
    }

    /**
     * 代理商审核 成功
     */
    public function by($ids = '')
    {
        !$ids && $this->error('ids有误');
        $where['id'] = ['in', $ids];
        $row = $this->model->get($ids);
        $this->model->startTrans();
        if ($this->model->update(['store_status' => '1'], $where)) {
            $distributor = UserRebate::getByUid($row->uid);
            $field = "uid as id,1 as distributor,1 as distributor_id,username ,create_time as jointime";
            if ($distributor && config('site.dealer_switch')) {
                $distributorUser = \app\admin\model\User::get($distributor->first_id);
                $distributorUser->distributor != 2   && $distributorUser->save(['distributor' => 2,
                ]);
            }
            $list = $this->model->where(['id' => $ids])->field($field)->select();
//            if (\app\common\model\User::update(['distributor' => '1', 'distributor_id' => 1], ['id' => ['in', $uid]])) {
            if (model('User')->saveAll(collection($list)->toArray())) {
                $this->model->commit();
                $this->success('审核成功');
            }
        }
        $this->model->rollback();
        $this->error('审核失败');
    }
}
