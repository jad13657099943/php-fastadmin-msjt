<?php

namespace app\api\controller;

use addons\epay\library\Service;
use addons\epay\library\Wechat;
use app\common\controller\Api;
use fast\Http;
use fast\Random;

/**
 * 示例接口
 */
class Demo extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['setPaymentCallback', 'test1', 'transfers', 'demo', 'test3'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2'];

    public function _initialize()
    {
        parent::_initialize();
        $this->item = model('Litestoregoods');
        $this->shoping_cart = model('Shopingcart');
        $this->order = model('Litestoreorder');
        $this->order_goods = model('Litestoreordergoods');
        $this->item_spec = model('Litestoregoodsspec');
        $this->order_config_model = model('Orderconfig');
        $this->order_refund = model('Litestoreorderrefund');
    }

    public function test3()
    {
        dump($this->order->where(['id' => 751])->with(['address' => function ($query) {
            dump($query);
            die;
        }])->find());
    }

    /**
     * 企业付款到微信
     */
    public function transfers()
    {
        $url = "https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $config = Service::getConfig('wechat')['wechat'];
        $params = [
            'mch_appid' => $config['miniapp_id'],
            'mchid' => $config['mch_id'],
            'nonce_str' => Random::numeric(10),
            'partner_trade_no' => 'GGZP123456789',
            'openid' => 'o0Zx85aabL5RrkII2od1JUwnFRz0',
            'check_name' => 'FORCE_CHECK',
            're_user_name' => '姚静',
            'desc' => '国赣臻品分销佣金',
            'amount' => 30,
            'spbill_create_ip' => $this->request->ip(),
        ];
        $params['sign'] = $this->sign($params, $config['key']);
        $options = [
            CURLOPT_SSLCERTTYPE => 'PEM',
            CURLOPT_SSLCERT => $config['cert_client'],
            CURLOPT_SSLKEYTYPE => 'PEM',
            CURLOPT_SSLKEY => $config['cert_key'],
        ];
        $result = fromXml(Http::post($url, $this->toXml($params), $options));

        if (isset($result['return_code']) && $result['return_code'] == 'SUCCESS') {
            switch ($result['result_code']) {
                case 'SUCCESS'://付款成功

                    break;
            }
        }
    }


    /**
     * 签名
     * @param $data
     * @param $key
     * @return string
     */
    private function sign($data, $key)
    {
        ksort($data);
        $sign = '';
        foreach ($data as $k => $value) {
            $sign .= "$k=$value&";
        }
        $sign .= "key=$key";
        return strtoupper(md5($sign));
    }

    protected function toXml($data)
    {
        $xml = '<xml>';
        foreach ($data as $key => $val) {
            $xml .= is_numeric($val) ? '<' . $key . '>' . $val . '</' . $key . '>' :
                '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
        }
        $xml .= '</xml>';

        return $xml;
    }

    protected function fromXml($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }


    /**
     * 支付回调
     * 1)修改订单状态
     * 2)修改库存
     * 3)增加销量
     * 4)添加金额记录
     * 5)查询团购订单状态，判断团购是否完成 pay_time  pay_status
     * @param $order_sn pay_price order_type 10 商城订单 ，20拼团订单
     *
     * 砍价没做
     *
     */
    public function setPaymentCallback()
    {
        $order_no = 'A19091036492055';
        $zf_type = '1';
        $transaction_id = '2222';
        //查询订单
        $field = 'order_type ,user_id , id, pay_price , activity_id , activity_type';
        $order_where = ['order_no' => $order_no, 'pay_status' => 10, 'order_status' => 10];
        $order_info = $this->order->find_data($order_where, $field);

        if (!$order_info) {
            echo 4;
            exit;
        }

        $this->order->startTrans();

        //修改订单状态  10)支付宝 20）微信 transaction_id
        $update_order_save = ['pay_time' => time(), 'pay_status' => 20, 'transaction_id' => $transaction_id,
            'order_status' => $order_info['order_type'] == 20 ? 60 : 20, 'zf_type' => $zf_type];

        $update_order = $this->order->update_data($order_where, $update_order_save);

        //添加消费记录
        $account_logs_model = model('Accountlogs');
        $account_logs_arr = [
            'uid' => $order_info['user_id'],
            'amount' => $order_info['pay_price'],
            'add_time' => time(),
            'desc' => '购买商品',
            'type' => 2,
            'order_sn' => $order_no,
            'zf_type' => $zf_type,// 1余额 2微信 3支付宝
        ];

        $add_account = $account_logs_model->add_data($account_logs_arr);

        //团购  //砍价  activity_type 1)正常商品  2)限时抢购  3)今日特价  4)2人团购-团购 5)团购单独购买 6）砍价


        $order_goods_info = $this->order_goods->select_data(['order_id' => $order_info['id']], 'total_num , goods_spec_id ,goods_id,images');

        //扣除库存 增加销量
        $this->limit_discount_goods_model = model('Limitdiscountgoods');
        $this->cut_down_goods_model = model('Cutdowngoods');

        if ($order_goods_info) {
            foreach ($order_goods_info as $k => $v) {
                //增加销量 减少库存
                $update_spec_stock_num = $this->item_spec->updateSpec($v['goods_spec_id'], $v['goods_id'], $v['total_num'], 1);

                //限时抢购  添加销量 减少库存
                if ($order_info['activity_type'] == 2) {
                    $this->limit_discount_goods_model->updateSpec($order_info['activity_id'], $v['goods_id'], $v['total_num'], 1);

                } elseif ($order_info['activity_type'] == 6) { //砍价

                    $this->cut_down_goods_model->updateSpec($order_info['activity_id'], $v['goods_id'], $v['total_num'], 1);

                }

                if (!$update_spec_stock_num) {
                    echo 3;
                    exit;
                }
            }
        }


        if ($order_info['activity_type'] == 4) { //团购数量
            $this->join_groupbuy_model = model('Joingroupbuy');
            $join_groupbuy_info = $this->join_groupbuy_model
                ->find_data(['order_id' => $order_info['id'], 'type' => 0],
                    'groupbuy_id ,pid,goods_id,group_num,status,join_num');

            if (!$join_groupbuy_info)
                return -1;

            //修改支付状态
            $join_groupbuy_save['type'] = 1;
            $join_groupbuy_save['join_num'] = $this->join_groupbuy_model->getGroupbuyNum(['pid' => $join_groupbuy_info['pid']]);

            if (($join_groupbuy_info['join_num'] + 1) == $join_groupbuy_info['group_num']) {//团购完成
                $join_groupbuy_save['type'] = 2;

                //添加团购销量 group_nums
                $this->groupbuy_goods_model->where(['groupbuy_id' => $order_info['groupbuy_id'], 'goods_id' => $join_groupbuy_info['goods_id']])->setInc('group_nums', $join_groupbuy_info['group_num']);

            }

            //修改参加团购信息
            $update_join_groupbuy = $this->join_groupbuy_model->update_data(['order_id' => $order_info['id']], $join_groupbuy_save);

        }


        if ($update_order && $add_account) {
            $this->order->commit();
            echo 1;
        } else {
            $this->order->rollback();
            echo 2;
        }

        exit;
    }


    /**
     * 测试方法
     *
     * @ApiTitle    (测试名称)
     * @ApiSummary  (测试描述信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/demo/test/id/{id}/name/{name})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type="integer", required=true, description="会员ID")
     * @ApiParams   (name="name", type="string", required=true, description="用户名")
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function test()
    {
        $curl = curl_init();

        $url = 'http://k3wisedemo.kingdee.com:81/K3API/Token/Create?authorityCode=d947613eec0ad8f583e3e7946cac0db0a9735393b7e6e76f';

        curl_setopt($curl, CURLOPT_URL, $url);
// CURLOPT_RETURNTRANSFER  设置是否有返回值
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//执行完以后的返回值
        $response = curl_exec($curl);
//释放curl
        curl_close($curl);


        $this->success('返回成功', $response);
    }

    /**
     * 无需登录的接口
     *
     */
    public function test1()
    {
        dump(strtotime('20141030133525'));
    }

    /**
     * 需要登录的接口
     *
     */
    public function test2()
    {
        $this->success('返回成功', ['action' => 'test2']);
    }

}
