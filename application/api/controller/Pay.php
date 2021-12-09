<?php

namespace app\api\controller;

use addons\epay\library\Service;
use app\common\model\Commission;
use app\common\model\Config;
use app\common\model\Groupbuygoods;
use app\common\model\Litestoregoodsspec;
use app\common\model\Litestoreordergoods;
use app\common\model\Litestoreorder;
use app\common\model\Joingroupbuy;
use app\common\model\Litestoreorderrefund;
use app\common\model\User;
use app\common\model\UserAgentApply;
use app\common\model\UserRebate;
use app\common\model\UserRebateBack;
use think\Db;
use app\common\controller\Api;

class Pay extends Api
{

    protected $noNeedLogin = ['notify', 'refundNotify', 'refund_groupbuy', 'addFunc', 'agentNotify', 'agentRefund', 'adminAgentRefund'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->order = new  Litestoreorder();
        $this->order_goods = new Litestoreordergoods();
        $this->item_spec = new Litestoregoodsspec();
        $this->groupbuy_goods_model = new Groupbuygoods();
        $this->join_groupbuy_model = new Joingroupbuy();
//        $this->config = model('common/Config');
        $this->config = new Config();

        $this->user = new User();
    }

    /**
     * 支付接口
     * @param order_id 订单id
     * @param type 支付类型 1 微信支付 2余额支付
     * @param status 订单类型 1 购买商品 2 余额充值 3 费用补交
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function pay()
    {
        $orderNo = $this->request->request('order_no');
        !$orderNo && $this->error('订单编号不能为空');

        $orderInfo = Litestoreorder::getByOrderNo($orderNo);

        //判断订单是否存在
        !$orderInfo && $this->error('订单不存在');
        if ($orderInfo->pay_price == 0 ){
            $this->notify_to($orderNo);
        }else{
            $orderInfo->pay_price <=0 && $this->error('订单金额有误');
            $orderInfo->pay_status != 10 && $this->error('订单状态有误');
        //拼接支付信息
        $params = ['openid' => $this->auth->wxopenid];
        $params['orderid'] = $orderInfo['order_no'];
        $params['amount'] = $orderInfo->pay_price;
        $params['type'] = 'wechat';
        $params['title'] = '购买商品';
        $params['method'] = 'miniapp';
        $params['notifyurl'] = $this->request->root(true) . '/api/pay/notify/';
        $this->success($params, Service::submitOrder($params));
        }
    }

    /**
     * 微信支付成功回调
     */
    public function notify()
    {
        $pay = \addons\epay\library\Service::checkNotify('wechat');
        !$pay && die;
        $data = $pay->verify();
//        订单号
        $orderNo = $data['out_trade_no'];

        Db::startTrans();
        try {
            $order = Litestoreorder::getByOrderNo($orderNo);

            //判断订单状态是否正常
            if (!$order || $order->pay_status == 20) {
                throw new \Exception('订单状态错误');
            }

            //套餐订单立即分佣
            self::createDistributionOrder($order);

            //支付后减少商品库存，订单商品模型
            if ($order->type == 0) {
                $orderGoods = model('common/Litestoreordergoods');
                //商品规格模型
                $spec = model('common/Litestoregoodsspec');

                $field = 'goods_id,goods_spec_id,total_num';

                switch ($order->activity_type) {
                    case 1://普通订单
                        //闭包关联查询商品表
                        $goodsList = $orderGoods->where('order_id=' . $order->id)->field($field)
                            ->with(['goods' => function ($query) {
                                $query->withField('goods_id,spec_type,is_marketing,sales_actual,stock_num,goods_status');
                            }, 'discount' => function ($query) {
                                $query->withField('id,goods_id,stock_num,sales,status');
                            }])->select();

                        //遍历订单购买的商品，进行库存数量计算
                        foreach ($goodsList as $goods) {
                            if ($goods->goods->is_marketing == 2) {
                                $goods->discount->sales += $goods->total_num;
                                $goods->discount->stock_num -= $goods->total_num;
                                if ($goods->discount->stock_num <= 0) {
                                    $goods->discount->status = 20;
                                }
                                $goods->discount->save();
                            }
                            //总库存和销量
                            $goods->goods->sales_actual += $goods->total_num;
                            $goods->goods->stock_num -= $goods->total_num;
                            //规格库存和销量
                            $specInfo = $spec->get($goods->goods_spec_id);
                            $specInfo->stock_num -= $goods->total_num;
                            $specInfo->goods_sales += $goods->total_num;

                            $specInfo->save();
                            //单规格时，商品库存为0时 修改商品状态
                            if ($goods->goods->spec_type == 10) {
                                $goods->goods->stock_num <= 0 && $goods->goods->goods_status = 20;
                            } else {
                                //多规格时，统计所有规格库存，全部库存为0时修改商品状态
                                $stock_num = $spec->where('goods_id=' . $goods->goods_id)->sum('stock_num');
                                $stock_num <= 0 && $goods->goods->goods_status = 20;
                            }
                            $goods->goods->save();
                        }
                        break;
                    case 2://限时抢购订单
                        $goodsList = $orderGoods->where('order_id=' . $order->id)->field($field)
                            ->with(['discount' => function ($query) {
                                $query->withField('id,goods_id,stock_num,sales,status');
                            }, 'goods' => function ($query) {
                                $query->withField('goods_id,spec_type,is_marketing,sales_actual,stock_num,goods_status');
                            }])->select();
                        //计算活动库存
                        foreach ($goodsList as $goods) {
                            $goods->discount->sales += $goods->total_num;
                            $goods->discount->stock_num -= $goods->total_num;
                            if ($goods->discount->stock_num <= 0) {
                                $goods->discount->status = 20;
                            }
                            //总库存和销量
                            $goods->goods->sales_actual += $goods->total_num;
                            $goods->goods->stock_num -= $goods->total_num;
                            //规格库存和销量
                            $specInfo = $spec->get($goods->goods_spec_id);
                            $specInfo->stock_num -= $goods->total_num;
                            $specInfo->goods_sales += $goods->total_num;

                            $specInfo->save();
                            $goods->goods->save();
                            $goods->discount->save();
                        }
                        break;
                    case 4://团购订单系列操作  支付回调 (两人拼团)
                        $this->group_buy_goods_order($order->id, $order->activity_id, $order->total_num, false);
                        break;
                    case 5://拼团单独购买
                        model('Groupbuygoods')->updateSpec($order->activity_id, $order->total_num, 1);
                        break;
                    default:
                        break;
                }
            } else {
                //查询用户
                $user = User::get($order->user_id);
                //记录当前发货时间
                $order->ship_time = time();

                //套餐订单发货次数调整
                $goodsInfo = \app\common\model\Litestoregoods::get($order->goods[0]->goods_id);
                $order->total_frequency = $goodsInfo->send_num;
                $order->time_interval = $goodsInfo->time_interval;
                $goodsInfo->upper_num && $user->addByVipGoods($goodsInfo->goods_id);

//                $order->total_frequency = config('site.vip' . $order->type . '_frequency');
                if ($order->type == 1) {
                    $user->is_buy_ordinary_vip = 1;
                }
                if ($user->pid != 0) {
                    $agent = User::get($user->pid);
                    $agent->count++;
                    $agent->save();
                }
                if ($order->type > $user->vip_type) {
                    $user->vip_type = $order->type;
                }
                $user->save();
            }
            $scoreconfig=$this->config->getConfigData(['name'=> 'product_points']);
            //修改订单状态
            $order->zf_type = 20;
            $order->pay_price = $data['cash_fee'] / 100;
            $order->pay_status = 20;
            $order->order_status = $order->is_status == 1 ? 20 : 30;
            $order->order_status = $order->activity_type == 4 ? 60 : $order->order_status;
            $order->pay_time = strtotime($data['time_end']);
            $integal=$order->pay_price + $order->use_money;
            $config = new Config();
            $scoreconfig=$config ->getConfigData(['name'=> 'score_ratio']);
            $integal=$integal * $scoreconfig;
            $order->integral=$integal;
            $score= $order->use_integral * $scoreconfig;
            $order->use_integral = $score;
            if (!empty($order->use_integral) && $order->use_integral != 0 ){
                User::score($score, $order->user_id, '下单使用积分', 0);}
            if (!empty($order->use_money) && $order->use_money != 0){
                \app\common\model\User::money($order->use_money,$order->user_id,'下单使用余额',20,$order->id);
            }
            if ($order->save()) {
                Db::commit();
                $this->wxSuccess();
                exit();
            }
        } catch (\Exception $e) {
            file_put_contents('log.log', $e->getFile() . ":" . $e->getMessage());
        }
        Db::rollback();
    }



    public function notify_to($orderNo){
        Db::startTrans();
        try {
            $order = Litestoreorder::getByOrderNo($orderNo);
            //判断订单状态是否正常
            if (!$order || $order->pay_status == 20) {
                throw new \Exception('订单状态错误');
            }

            //套餐订单立即分佣
            self::createDistributionOrder($order);

            //支付后减少商品库存，订单商品模型
            if ($order->type == 0) {
                $orderGoods = model('common/Litestoreordergoods');
                //商品规格模型
                $spec = model('common/Litestoregoodsspec');

                $field = 'goods_id,goods_spec_id,total_num';

                switch ($order->activity_type) {
                    case 1://普通订单
                        //闭包关联查询商品表
                        $goodsList = $orderGoods->where('order_id=' . $order->id)->field($field)
                            ->with(['goods' => function ($query) {
                                $query->withField('goods_id,spec_type,is_marketing,sales_actual,stock_num,goods_status');
                            }, 'discount' => function ($query) {
                                $query->withField('id,goods_id,stock_num,sales,status');
                            }])->select();

                        //遍历订单购买的商品，进行库存数量计算
                        foreach ($goodsList as $goods) {
                            if ($goods->goods->is_marketing == 2) {
                                $goods->discount->sales += $goods->total_num;
                                $goods->discount->stock_num -= $goods->total_num;
                                if ($goods->discount->stock_num <= 0) {
                                    $goods->discount->status = 20;
                                }
                                $goods->discount->save();
                            }
                            //总库存和销量
                            $goods->goods->sales_actual += $goods->total_num;
                            $goods->goods->stock_num -= $goods->total_num;
                            //规格库存和销量
                            $specInfo = $spec->get($goods->goods_spec_id);
                            $specInfo->stock_num -= $goods->total_num;
                            $specInfo->goods_sales += $goods->total_num;

                            $specInfo->save();
                            //单规格时，商品库存为0时 修改商品状态
                            if ($goods->goods->spec_type == 10) {
                                $goods->goods->stock_num <= 0 && $goods->goods->goods_status = 20;
                            } else {
                                //多规格时，统计所有规格库存，全部库存为0时修改商品状态
                                $stock_num = $spec->where('goods_id=' . $goods->goods_id)->sum('stock_num');
                                $stock_num <= 0 && $goods->goods->goods_status = 20;
                            }
                            $goods->goods->save();
                        }
                        break;
                    case 2://限时抢购订单
                        $goodsList = $orderGoods->where('order_id=' . $order->id)->field($field)
                            ->with(['discount' => function ($query) {
                                $query->withField('id,goods_id,stock_num,sales,status');
                            }, 'goods' => function ($query) {
                                $query->withField('goods_id,spec_type,is_marketing,sales_actual,stock_num,goods_status');
                            }])->select();
                        //计算活动库存
                        foreach ($goodsList as $goods) {
                            $goods->discount->sales += $goods->total_num;
                            $goods->discount->stock_num -= $goods->total_num;
                            if ($goods->discount->stock_num <= 0) {
                                $goods->discount->status = 20;
                            }
                            //总库存和销量
                            $goods->goods->sales_actual += $goods->total_num;
                            $goods->goods->stock_num -= $goods->total_num;
                            //规格库存和销量
                            $specInfo = $spec->get($goods->goods_spec_id);
                            $specInfo->stock_num -= $goods->total_num;
                            $specInfo->goods_sales += $goods->total_num;

                            $specInfo->save();
                            $goods->goods->save();
                            $goods->discount->save();
                        }
                        break;
                    case 4://团购订单系列操作  支付回调 (两人拼团)
                        $this->group_buy_goods_order($order->id, $order->activity_id, $order->total_num, false);
                        break;
                    case 5://拼团单独购买
                        model('Groupbuygoods')->updateSpec($order->activity_id, $order->total_num, 1);
                        break;
                    default:
                        break;
                }
            }
            else {
                //查询用户
                $user = User::get($order->user_id);
                //记录当前发货时间
                $order->ship_time = time();

                //套餐订单发货次数调整
                $goodsInfo = \app\common\model\Litestoregoods::get($order->goods[0]->goods_id);
                $order->total_frequency = $goodsInfo->send_num;
                $order->time_interval = $goodsInfo->time_interval;
                $goodsInfo->upper_num && $user->addByVipGoods($goodsInfo->goods_id);

//                $order->total_frequency = config('site.vip' . $order->type . '_frequency');
                if ($order->type == 1) {
                    $user->is_buy_ordinary_vip = 1;
                }
                if ($user->pid != 0) {
                    $agent = User::get($user->pid);
                    $agent->count++;
                    $agent->save();
                }
                if ($order->type > $user->vip_type) {
                    $user->vip_type = $order->type;
                }
                $user->save();
            }
            //修改订单状态
            $order->zf_type = 20;
            $order->pay_price = 0;
            $order->pay_status = 20;
            $order->order_status = $order->is_status == 1 ? 20 : 30;
            $order->order_status = $order->activity_type == 4 ? 60 : $order->order_status;
            $order->pay_time = time();
            $config = new Config();
            $integal=$order->pay_price + $order->use_money;
            $scoreconfig=$config ->getConfigData(['name'=> 'score_ratio']);
            $integal=$integal * $scoreconfig;
            $order->integral=$integal;
            $scoreconfig=$this->config->getConfigData(['name'=> 'product_points']);
            $score= round($order->use_integral * $scoreconfig,2);
            $order->use_integral = $score;
            if (!empty($order->use_integral) && $order->use_integral!= 0 ){
                User::score($score, $order->user_id, '下单使用积分', 0);}
                if (!empty($order->use_money) && $order->use_money != 0){
                    \app\common\model\User::money($order->use_money, $order->user_id,'下单使用余额',20,$order->id);
//                    $order->integral = round($order->use_money * config('site.score_ratio'));
//                    User::score($order->integral, $order->user_id, '金额下单获得积分', 1);
                }
            $data=$order->save();
            if ($data == 1 ) {
                echo 111;
                Db::commit();
                $this->success('支付成功！');
            }
        } catch (\Exception $e) {
            file_put_contents('log.log', $e->getFile() . ":" . $e->getMessage());
        }
        Db::rollback();
        exit();
    }

    /**
     * 创建分销订单
     * @param Litestoreorder $order
     * @return bool
     * @throws \think\exception\DbException
     */
    public function createDistributionOrder(Litestoreorder $order)
    {
        if (UserRebateBack::get(['order_id' => $order->id])) {
            return false;
        }
        $userRebate = UserRebate::getByUid($order->user_id);
        //验证下单人信息
        if (!$userRebate) {
            return true;
        }

        $userInfo = User::get($order->user_id);

        //所有订单都会获得积分  只有在用户首次购买套餐时是固定积分
        $memo = '分佣积分';
        $score = round(config('site.distribution_score_ratio') * $order->pay_price);
        if ($userInfo->vip_type == 0 && $order->type != 0) {
            $memo = '会员注册';
            $score = config('site.register_member_score');
        }

        User::score($score, $userRebate->first_id, $memo, 1);

        if ($order->type != 1) {
            $commission = 0;
            $twoCommission = 0;
            foreach ($order->goods as $goods) {
                $money = (int)$goods->pay_price ? $goods->pay_price : $goods->total_price;
                switch ($goods->activity_type) {
                    case 1://正常商品
                        $commission += config('site.distribution_rate') * $money;
                        $twoCommission += config('site.two_distribution_rate') * $money;
                        break;
                    case 2://抢购商品
                        $commission += config('site.spike_distribution_rate') * $money;
                        $twoCommission += config('site.two_spike_distribution_rate') * $money;
                        break;
                    case 4://拼团商品
                    case 5:
                        $commission += config('site.group_distribution_rate') * $money;
                        $twoCommission += config('site.two_group_distribution_rate') * $money;
                        break;
                }
            }

            if ($commission <= 0) {
                return false;
            }
            //计算获利
            //一级分佣
            $commission = number_format($commission, 2);
            $data = [
                'superior_id' => $userRebate->first_id,
                'uid' => $order->user_id,
                'order_sn' => $order->order_no,
                'order_id' => $order->id,
                'money' => $commission,
                'act_pay_money' => $order->pay_price,
                'type' => 1,
                'status' => $order->type == 2 ? 2 : 1,
            ];
            $one = UserRebateBack::create($data);

            if (config('site.dealer_switch')) {
                //二级分佣
                $twoUserRebate = UserRebate::getByUid($userRebate->first_id);
                $two = null;
                if ($twoUserRebate) {
                    $twoCommission = number_format($twoCommission, 2);
                    $data['type'] = 2;
                    $data['money'] = $twoCommission;
                    $data['superior_id'] = $twoUserRebate->first_id;
                    $two = UserRebateBack::create($data);
                }
                if ($order->type == 2) {
                    Commission::balance($userRebate->first_id, $one->money, 1);
                    isset($two) && Commission::balance($twoUserRebate->first_id, $two->money, 1);
                }
            }
        }
    }

    /**
     * 订单分佣
     * @param Litestoreorder $order
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function commission(Litestoreorder $order)
    {
        $userRebateOrder = model('common/UserRebateBack');
        $list = $userRebateOrder->where(['order_sn' => $order->order_no, 'status' => 1])->select();
        foreach ($list as $item) {
            Commission::balance($item->superior_id, $item->money, 1);
            $item->status = 2;
            $item->save();
        }
    }

    /**
     * 退款方法
     * @param Litestoreorderrefund $refundOrder
     * @param Litestoreorder $order
     * @return bool
     * @throws \think\exception\PDOException
     */
    public static function refund($refundOrder, $order)
    {
        $config = new Config();
        $scoreconfig=$config ->getConfigData(['name'=> 'product_points']);
        if ($refundOrder && $order) {
            if ($refundOrder->money <= $order->pay_price) {
                if ($order->pay_price == 0 && $order->use_money != 0 && $order->use_integral != 0) {
                    $uid = \app\admin\model\User::get($refundOrder->uid)->id;
                    $score = $order->use_integral;
                    $order->use_integral = $score;
                    User::score($score, $uid, '退款返回积分', 1);
                    \app\common\model\User::money($order->use_money, $order->user_id, '退款返回余额', 70, $uid);
                    return '积分/抵扣金额返回成功';
                }
                if ($order->pay_price == 0 && $order->use_money != 0 && $order->use_integral == 0) {
                    $uid = \app\admin\model\User::get($refundOrder->uid)->id;
                    \app\common\model\User::money($order->use_money, $order->user_id, '退款返回余额', 70, $uid);
                    return '抵扣金额返回成功';
                }
                if ($order->pay_price == 0 && $order->use_money == 0 && $order->use_integral != 0) {
                    $uid = \app\admin\model\User::get($refundOrder->uid)->id;
                    $score = $order->use_integral;

                    $order->use_integral = $score;
                    User::score($score, $uid, '退款返回积分', 1);
                    return '积分返回成功';
                }
                if ($order->pay_price != 0 && $order->use_money != 0 && $order->use_integral == 0) {
                    $uid = \app\admin\model\User::get($refundOrder->uid)->id;
                    \app\common\model\User::money($order->use_money, $order->user_id, '退款返回余额', 70, $uid);
                    $pay = new \Yansongda\Pay\Pay(Service::getConfig('wechat'));
                    $params = [
                        'out_trade_no' => $order->order_no,//商户订单号
                        'out_refund_no' => $refundOrder->refund_no,//退款单号
                        'total_fee' => $order->pay_price * 100,//订单金额 必须是整数
                        'refund_fee' => $refundOrder->money * 100,//退款金额 必须是整数
                        'refund_desc' => $refundOrder->remark,//退款原因
                        'notify_url' => config('item_url') . '/api/pay/refundNotify',//退款原因
                    ];
                    $result = $pay->driver('wechat')->gateway('miniapp')->refund($params);

                    return $result && $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' ? true : false;
                }
                if ($order->pay_price != 0 && $order->use_money == 0 && $order->use_integral != 0) {
                    $uid = \app\admin\model\User::get($refundOrder->uid)->id;
                    $score = $order->use_integral ;
                    $order->use_integral = $score;
                    User::score($score, $uid, '退款返回积分', 1);
                    $pay = new \Yansongda\Pay\Pay(Service::getConfig('wechat'));
                    $params = [
                        'out_trade_no' => $order->order_no,//商户订单号
                        'out_refund_no' => $refundOrder->refund_no,//退款单号
                        'total_fee' => $order->pay_price * 100,//订单金额 必须是整数
                        'refund_fee' => $refundOrder->money * 100,//退款金额 必须是整数
                        'refund_desc' => $refundOrder->remark,//退款原因
                        'notify_url' => config('item_url') . '/api/pay/refundNotify',//退款原因
                    ];
                    $result = $pay->driver('wechat')->gateway('miniapp')->refund($params);

                    return $result && $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' ? true : false;
                }
                if ($order->pay_price != 0 && $order->use_money != 0 && $order->use_integral != 0) {
                    $uid = \app\admin\model\User::get($refundOrder->uid)->id;
                    $score = $order->use_integral ;
                    $order->use_integral = $score;
                    User::score($score, $uid, '退款返回积分', 1);
                    \app\common\model\User::money($order->use_money, $order->user_id, '退款返回余额', 70, $uid);
                    $pay = new \Yansongda\Pay\Pay(Service::getConfig('wechat'));
                    $params = [
                        'out_trade_no' => $order->order_no,//商户订单号
                        'out_refund_no' => $refundOrder->refund_no,//退款单号
                        'total_fee' => $order->pay_price * 100,//订单金额 必须是整数
                        'refund_fee' => $refundOrder->money * 100,//退款金额 必须是整数
                        'refund_desc' => $refundOrder->remark,//退款原因
                        'notify_url' => config('item_url') . '/api/pay/refundNotify',//退款原因
                    ];
                    $result = $pay->driver('wechat')->gateway('miniapp')->refund($params);

                    return $result && $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' ? true : false;
                }
                if ($order->pay_price != 0 && $order->use_money == 0 && $order->use_integral == 0) {
//                    $uid = \app\admin\model\User::get($refundOrder->uid)->id;
//                    $score = $order->use_integral ;
//                    $order->use_integral = $score;
//                    User::score($score, $uid, '退款返回积分', 1);
//                    \app\common\model\User::money($order->use_money, $order->user_id, '退款返回余额', 70, $uid);
                    $pay = new \Yansongda\Pay\Pay(Service::getConfig('wechat'));
                    $params = [
                        'out_trade_no' => $order->order_no,//商户订单号
                        'out_refund_no' => $refundOrder->refund_no,//退款单号
                        'total_fee' => $order->pay_price * 100,//订单金额 必须是整数
                        'refund_fee' => $refundOrder->money * 100,//退款金额 必须是整数
                        'refund_desc' => $refundOrder->remark,//退款原因
                        'notify_url' => config('item_url') . '/api/pay/refundNotify',//退款原因
                    ];
                    $result = $pay->driver('wechat')->gateway('miniapp')->refund($params);

                    return $result && $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS' ? true : false;
                }
            }
            return false;
        }
    }

    /**
     * 退款回调
     */
    public function refundNotify()
    {
        $data = $this->decode();
        !$data && die;
        if (Order::refundCallBack($data)) {
            $this->wxSuccess();
            exit();
        }
    }

    /**
     * 支付申请费用
     * @throws \think\exception\DbException
     */
    public function agentPay()
    {
        $id = $this->request->param('id');
        $agentInfo = UserAgentApply::get($id);
        !$agentInfo && $this->error('信息不存在');

        //拼接支付信息
        $params = ['openid' => $this->auth->wxopenid];
        $params['orderid'] = $agentInfo->order_no;
        $params['amount'] = $agentInfo->apply_money;
        $params['type'] = 'wechat';
        $params['title'] = '申请代理商';
        $params['method'] = 'miniapp';
        $params['notifyurl'] = $this->request->root(true) . '/api/pay/agentNotify/';
        $this->success($params, Service::submitOrder($params));
    }

    /**
     * 申请费用退款回调
     */
    public function agentRefund()
    {
        $data = $this->decode();
        !$data && die;

        $refundInfo = Litestoreorderrefund::getByRefundNo($data['out_refund_no']);
        !$refundInfo && die;

        $refundInfo->refund_money = $data['settlement_refund_fee'] / 100;
        $refundInfo->status = 2;
        $refundInfo->refund_time = strtotime($data['success_time']);

        $agentInfo = UserAgentApply::getByOrderNo($refundInfo->order_no);
        $userInfo = User::get($agentInfo->uid);

        $userInfo->apply_money -= $refundInfo->refund_money;

        if ($refundInfo->save() && $userInfo->save()) {
            $this->wxSuccess();
            exit();
        }
    }

    /**
     * 代理商申请支付回调
     * @throws \think\exception\DbException
     */
    public function agentNotify()
    {
        $pay = \addons\epay\library\Service::checkNotify('wechat');
        !$pay && die;
        $data = $pay->verify();
//        订单号
        $agentInfo = UserAgentApply::getByOrderNo($data['out_trade_no']);
        !$agentInfo && die;
        $agentInfo->pay_time = strtotime($data['time_end']);
        $agentInfo->pay_money = $data['cash_fee'] / 100;
        $userInfo = User::get($agentInfo->uid);
        $userInfo->apply_money += $data['cash_fee'] / 100;
        try {
            Db::startTrans();
            if ($userInfo->save() && $agentInfo->save()) {
                Db::commit();
                $this->wxSuccess();
                exit();
            }
        } catch (\Exception $e) {
        }
        Db::rollback();
    }


    /**
     * xml转数组
     * @param $xml
     * @return mixed
     */
    protected function fromXml($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * 解析退款回调数据
     * @return bool|mixed
     */
    protected function decode()
    {
        $data = file_get_contents("php://input");
        $data = $this->fromXml($data);
        if ($data['return_code'] == 'SUCCESS') {
            $dataStr = base64_decode($data['req_info']);
            $config = Service::getConfig('wechat')['wechat'];
            $key = md5($config['key']);
            $reqInfo = openssl_decrypt($dataStr, 'AES-256-ECB', $key, OPENSSL_RAW_DATA);
            return $this->fromXml($reqInfo);
        }
        return false;
    }

    /**
     * 通知微信回调成功
     */
    protected function wxSuccess()
    {
        echo '<xml>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>';
    }


    /**
     * 团购订单系列操作  支付回调  退款  按时未完成退款
     * @param $order_id  订单id
     * @param $activity_id  活动id
     * @param $goods_number 数量
     * @param $state true 退款  false 生成订单
     *
     */
    protected function group_buy_goods_order($order_id, $activity_id, $goods_number, $state)
    {
        //查询参与团购信息
        $join_groupbuy_info = $this->join_groupbuy_model->find_data(['order_id' => $order_id], 'pid,group_num,join_num ,type ,status');

        if (!$join_groupbuy_info)
            return false;

        $join_groupbuy_save['join_num'] = $join_groupbuy_info['status'] == 2 ?
            $this->join_groupbuy_model->where(['pid' => $join_groupbuy_info['pid']])->value('join_num') + 1 :
            $join_groupbuy_info['join_num']; //获取参与人数

        //修改支付状态
        $join_groupbuy_save['type'] = 1; //进行中
        if ($join_groupbuy_save['join_num'] == $join_groupbuy_info['group_num'])//团购完成
            $join_groupbuy_save['type'] = 2; //已完成 //修改支付状态

        if ($join_groupbuy_save['type'] == 2 || $join_groupbuy_info['status'] == 2)
            $where = ['pid' => $join_groupbuy_info['pid'], 'type' => ['IN', '1,2']];

        $join_groupbuy_save['type'] = $state ? 3 : $join_groupbuy_save['type'];
        switch ($join_groupbuy_save['type']) {

            case 1: //拼团中
                if ($join_groupbuy_info['status'] == 2) {//参与拼团 修改整体拼团数量
                    $save['join_num'] = $join_groupbuy_save['join_num'];
                    $this->join_groupbuy_model->update_data($where, $save);
                    unset($where);
                }
                $where['order_id'] = $order_id;
                break;
            case 2://订单已完成 修改整个拼团状态

                $this->join_groupbuy_model->update_data(['order_id' => $order_id], ['type' => 2]);
                $order_ids = $this->join_groupbuy_model->where($where)->column('order_id');
                $this->order->update_data(['id' => ['IN', $order_ids]], ['order_status' => 20]);

                $save['join_num'] = $join_groupbuy_save['join_num'];
                break;
            case 3:
                $where['order_id'] = $order_id;
                break;
        }

        $save['type'] = $join_groupbuy_save['type'];
        $update_join_groupbuy = $this->join_groupbuy_model->update_data($where, $save);//修改参团数据
        $update_group_buy_goods = $this->groupbuy_goods_model->updateSpec($activity_id, $goods_number, $state ? 2 : 1, $join_groupbuy_save['type']); //修改销量和库存

        return $update_join_groupbuy && $update_group_buy_goods ? $join_groupbuy_save['type'] : false;
    }

    /**
     * 拼团订单 退款 退货
     * @param $pid 拼团关联id
     * @param $status 1)定时任务 取消未分享拼团订单
     */
    public function refund_groupbuy($pid, $status)
    {   //获取拼团订单集合
        $order_ids = $this->join_groupbuy_model->where(['pid' => $pid, 'type' => ['in', '1,2']])->column('order_id');

        //获取所有拼团订单
        $list = $this->order->select_data(['id' => ['in', $order_ids]], 'order_no , zf_type');

        if ($list != null) {
            foreach ($list as $k => $row) {
                //退款退货
                $resrult = $this->setPaymentCallback($row['order_no'], $row['zf_type'], $status);
                if (!$resrult)
                    $this->error('操作失败1');
            }
            return true;
        } else {
            $this->error('操作失败2');
        }
    }
}