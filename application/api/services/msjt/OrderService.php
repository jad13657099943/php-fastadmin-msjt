<?php


namespace app\api\services\msjt;


use addons\epay\library\Service;
use app\api\model\msjt\Config;
use app\api\model\msjt\Goods;
use app\api\model\msjt\Order;
use app\api\model\msjt\OrderGoods;
use app\api\model\msjt\Region;
use app\api\model\msjt\Users;
use app\api\services\msjt\common\AgencyOrderService;
use app\api\services\PublicService;
use think\Db;
use think\Request;

class OrderService extends PublicService
{
    /**
     * 下单
     * @param $uid
     * @param $params
     * @return mixed
     */
    public function set($uid, $params)
    {
        return Db::transaction(function () use ($uid, $params) {

            $order_no = $this->orderNo('A');
            $goods_num = $this->getPrice($uid, $order_no, $params);
            $freight = $this->getFreight($goods_num);

            $status = Order::whereInsert([
                'user_id' => $uid,
                'order_no' => $order_no,
                'money' => $goods_num + $freight,
                'goods_num' => $goods_num,
                'freight' => $freight,
                'site' => $this->getSite($uid, $params['site']),
                'remarks' => $params['remarks']
            ]);

            return $this->statusReturn($status, '下单成功', '下单失败', ['order_no' => $order_no]);
        });

    }

    /**
     * 微信支付
     * @param $uid
     * @param $order_no
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function pay($uid, $order_no)
    {
        $info = $this->checkOrder($uid, $order_no);
        $openid = Users::whereValue(['id' => $uid], 'openid');
        $params = [
            'amount' => $info->money,
            'orderid' => $info->order_no,
            'type' => 'wechat',
            'title' => '购买商品',
            'notifyurl' => Request::instance()->root(true) . '/order/notifyurl',
            'method' => 'mp',
            'openid' => $openid,
        ];
        $data = Service::submitOrder($params);
        return $this->success('wx支付', $data);
    }

    /**
     * 支付回调
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function notifyurl()
    {
        $result = $this->xmlJson();
        $result = $this->isSuccess($result);
        if ($result) {
            $model = Order::whereFind(['order_no' => $result->out_trade_no]);
            if (empty($model) || $model->status != 1) {
                $this->wxSuccess();
                exit();
            }
            $model->pay_money = $result->total_fee / 100;
            $model->status = 2;
            $model->pay_time = strtotime($result->time_end);
            $model->save();
            $service=new AgencyOrderService();
            $service->addAgencyOrder($result->out_trade_no,1,1);
            $this->wxSuccess();
        }
    }



    /**
     * 验证订单
     * @param $uid
     * @param $order_no
     * @return array|bool|\PDOStatement|string|\think\Model|null
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkOrder($uid, $order_no)
    {
        $info = Order::whereFind(['user_id' => $uid, 'order_no' => $order_no]);
        if (empty($info) || $info->status != 1 || $info->money <= 0) $this->error('订单异常');
        return $info;
    }

    /**
     * 获取收货地址
     * @param $uid
     * @param $site
     * @return false|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getSite($uid, $site)
    {
        $info = Region::whereFind(['user_id' => $uid, 'id' => $site]);
        if (empty($info)) {
            $info = Region::whereFind(['user_id' => $uid, 'is_default' => 2]);
            if (empty($info)) $this->error('请选择地址');
        }
        return json_encode($info);
    }

    /**
     * 运费
     * @return false|string
     */
    public function freight(){
        $info = Config::whereValue(['name' => 'freight'], 'value');
        $info = json_decode($info, true);
        $key=array_keys($info);
        $array=[
          'mz'=>  array_keys($info)[0],
          'sub'=>  $info[$key[0]]
        ];
        return $this->success('运费',$array);
    }

    /**
     * 计算运费
     * @param $goods_num
     * @return int|mixed
     */
    private function getFreight($goods_num)
    {
        $info = Config::whereValue(['name' => 'freight'], 'value');
        $info = json_decode($info, true);
        $key = array_keys($info);
        if ($goods_num >= $key[0]) {
            return 0;
        } else {
            return $info[$key[0]];
        }
    }

    /**
     * 统计商品价格并记录
     * @param $json
     * @param $sku_id
     * @param $price_text
     * @return mixed
     */
    private function getPrice($uid, $order_no, $prams)
    {
        $price_text = $this->getPriceText($uid);
        foreach ($prams['goods'] as $item) {
            $info = Goods::whereFind(['id' => $item['goods_id']]);
            $sku = $this->getKeyValue($info['configjson'], 'id', $item['sku_id']);
            $unit_price = json_decode($sku)->$price_text;
            $data[] = [
                'order_no' => $order_no,
                'goods_id' => $item['goods_id'],
                'info' => json_encode($info),
                'sku' => $sku,
                'sku_id' => $item['sku_id'],
                'num' => $item['num'],
                'unit_price' => $unit_price,
                'money' => $item['num'] * $unit_price,
                'createtime' => time()
            ];
        }

        OrderGoods::AllInsert($data);
        return array_sum(array_column($data, 'money'));

    }

    /**
     * 获取身份价格
     * @param $uid
     * @return string
     */
    private function getPriceText($uid)
    {
        if ($this->isVip($uid)) {
            $price_text = 'user_price';
        } else {
            $price_text = 'shop_price';
        }
        return $price_text;
    }

    /**
     * 是否vip
     * @param $uid
     * @return bool
     */
    private function isVip($uid)
    {
        $grade = Users::whereValue(['id' => $uid], 'grade');
        if ($grade > 1) return true;
        return false;
    }

}