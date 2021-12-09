<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Litestorefreight;
use app\common\model\Config;
use app\common\model\Joingroupbuy;
use app\common\model\Limitdiscount;
use app\common\model\Litestorecoupon;
use app\common\model\Litestorecoupondata;
use app\common\model\Litestoreorder;
use app\common\model\Litestoreorderrefund;
use app\common\model\Litestoreordergoods;
use app\common\model\Litestoreordership;
use app\common\model\Shoppickup;
use app\common\model\User;
use app\common\model\UserLevel;
use app\common\model\UserRebateBack;
use app\common\model\School as SchoolModel;
use app\common\model\Usertoken;
use fast\Date;
use think\Db;

/**
 * 订单控制器
 */
class Order extends Api
{
    protected $noNeedLogin = ['test', 'list_address', 'setOrderStatus', 'automaticRefund',
        'confirm_receipt', 'automatic_re_and_eva', 'setProductionOrder', 'setPaymentCallback',
        'getConfirmOrder', 'confirm_receipt', 'setAutomaticCancellation', 'refundGroupbuy', 'applyAfterSale', 'refund'];
    protected $noNeedRight = ['*'];

    /** @var Litestorecoupon */
    private $coupon = null;

    /** @var Litestorecoupondata */
    private $couponData = null;

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
        $this->user = model('User');
        $this->groupbuy_goods_model = model('Groupbuygoods');
        $this->user_agent_apply = model('Useragentapply');
        $this->coupon = model('common/Litestorecoupon');
        $this->config = model('common/Config');
        $this->couponData = model('common/Litestorecoupondata');
        $this->pickup=model('common/shoppickup');
    }

    /*
     *
     * @param $status 类型 1）从购物车点击  2）直接购买
     * @param $is_status 类型 1）配送商品  2）自取商品
     * @param $goods_id 商品ID
     * @param $goods_spec_id  多规格ID
     * @param $num 数量
     * @param $shoping_cart_ids
     * @param $activity_id 活动id
     * @param  type 1）正常商品  2）限时抢购  3）今日特价  4)2人团购-团购  5）团购单独购买 6)砍价购买
     * */
    public function getConfirmOrder()
    {
        $params = $this->request->request();
        !$params['status'] && $this->error('status不能为空');
        !$params['is_status'] && $this->error('is_status不能为空');
//        $params['is_status'] == 2 && !$params['lng'] && $this->error('lng不能为空'); //经度
//        $params['is_status'] == 2 && !$params['lat'] && $this->error('lat不能为空'); //纬度
        $params['uid'] = $this->auth->id;

        $total_num = $activity_money = 0;
        switch ($params['status']) {
            case 1:
                !$params['shoping_cart_ids'] && $this->error('shoping_cart_ids不能为空');
                $field = 'id,goods_id,goods_spec_id,goods_name,image,num,key_name,type';
                $list = $this->shoping_cart->select_data(['id' => ['IN', $params['shoping_cart_ids']], 'uid' => $this->auth->id], $field);
                !$list && $this->error('shoping_cart_ids参数有误');
                $totalWeight = 0;
                //单价 总价
                $totalprice = $commodity_amount = 0;
                foreach ($list as $k => $v) {
                    /*//判断库存是否充足
                    $where['goods_spec_id'] = $v['goods_spec_id'];
                    $goods_spec_info = $this->item_spec->find_data($where, 'stock_num,key_name,spec_sku_id,goods_price,spec_image,line_price');
                    $goods_spec_info['stock_num'] < $v['num'] && $this->error('商品库存不足');*/
                    //获取单价
                    $where['goods_id'] = $v['goods_id'];
                    $where['goods_spec_id'] = $v['goods_spec_id'];
                    $goods_spec = $this->item_spec->find_data($where, 'goods_price,vip_price,goods_weight,new_price,nums');
                    $wheres['goods_id'] = $v['goods_id'];
                    $goods_infos=new \app\common\model\Litestoregoods();
                    $goods_info = $goods_infos->find_data($wheres,'is_news');
                    if ($goods_info['is_news'] == '1'){
                        $goods_price = $this->auth->vip_type != 0 && $v['type'] == 1 ? $goods_spec['vip_price'] : $goods_spec['goods_price'];
                    }else  if($goods_info['is_news'] == 2){
                        if ($goods_spec['nums'] <= $v['num'] ){
                            $goods_price = $this->auth->vip_type != 0 && $v['type'] == 1 ? $goods_spec['vip_price'] : $goods_spec['new_price'];
                        }else{
                            $goods_price = $this->auth->vip_type != 0 && $v['type'] == 1 ? $goods_spec['vip_price'] : $goods_spec['goods_price'];
                        }
                    }
                    $list[$k]['goods_price'] = $goods_price;
                    $totalprice += $goods_price * $v['num'];
                    $commodity_amount += $goods_price * $v['num'];
                    $total_num += $v['num'];

                    $totalWeight += $goods_spec['goods_weight'] * $v['num'];

                    //购物车中有秒杀商品 记录秒杀商品价格 不能使用优惠券
                    if ($params['type'] == 1 && $v['type'] == 2) {
                        $activity_money += $goods_price * $v['num'];
                    }
                }
                break;
            case 2:
                $return = $this->BuyImmediately();
                $list = $return['list'];
                $commodity_amount = $return['totalprice'];
                $totalprice = $return['totalprice'];
                //订单总重量
                $totalWeight = $list[0]['goods_weight'];
                $total_num = $params['num'];
                break;
        }
        $address_where['user_id'] = $params['uid'];
        if ($params['is_status'] == 1) {
            //判断是否有默认地址
            $address = model('Litestoreaddress')->find_data($address_where, 'address_id,name,phone,site,isdefault,city_id');
            $address_type = !empty($address) && $address['isdefault'] == 1 ? 1 : 0; //1)有地址（是默认地址） 0）无默认地址
        } else {
            $address = $this->pickup->frist_data('',
                'id,address,store_name,opening_hours,closing_hours,mobile');
//            查询自提地址
//            $address = $this->user_agent_apply->find_data(["store_status" => 1, 'pay_time' => ['gt', 0], 'uid' => ['neq', $this->auth->id]],
//                'id,mobile,address,closing_hours,opening_hours,store_name,site', 'id asc');
            //1.获取用户的详细地址
            //2.获取所有商家的精度维度
            //3.获取用户的和商家的距离计算
            //4.获取最近的商家推荐
//            $address['float'] = $address['distance']; //计算距离
            $address_type = !empty($address) ? 1 : 0; //1)有地址（不是默认地址） 0）无地址
        }
        $coupon_where = [
            'litestorecoupondata.user_id' => $params['uid'],
            'litestorecoupondata.is_used' => 0,
            'litestorecoupondata.use_start_time' => ['ELT', time()],
            'litestorecoupondata.use_end_time' => ['EGT', time()]
        ];
        $coupon_list = [];
        $coupon_status = 0;
        if ($params['type'] == 1) {
            //判断是否扣掉秒杀价格
            $coupon_money = $activity_money > 0 ? $totalprice - $activity_money : $totalprice;
            $coupon_info = collection(model('common/Litestorecoupondata')->where($coupon_where)->field('id')
                ->with(['coupon' => function ($query) use ($coupon_money) {
                    $query->where(['enough' => ['ELT', $coupon_money], 'deduct' => ['ELT', $coupon_money]])->withField('name,deduct');
                }])->order('deduct desc')->select())->toArray();

            // $coupon_text = '没有可用优惠券';
            // $coupon_id = '';
            // $coupon_price = '';
            if ($coupon_info) {
                //$coupon_text = '请选择优惠券';//优惠券文字
                $coupon_status = 1;
                $coupon_list = $coupon_info;
            }
        }
        if ($params['is_status'] == 2) {
            $freight_name = "";
            $freight = "0.00";
        } else {
            if ($params['type'] == 6) {
                $freight = config('site.package_freight') * config('site.vip1_frequency');
                $freight_name = config('site.package_freight_text');
                $totalprice = $freight > 0 ? $totalprice + $freight : $totalprice;
            } else {
                $site = config('site.freight');
                $v = array_values($site)[0]; //运费
                $k = array_keys($site)[0];  //满
                //判断是否达到满免要求
                if ($totalprice > $k) {
                    $freight = "免运费";
                } else {
                    $freight = $v;
//                    $freight = $totalWeight > 0 ? $this->getWeightFreight($totalprice, $address->city_id, $totalWeight) : "0.00";
                    $totalprice = $freight > 0 ? $totalprice + $freight : $totalprice;
                    $freight_name = $freight > 0 || $totalWeight == 0 ? "订单金额满" . $k . "元免运费" : '您的订单消费已满' . $totalprice . '元,可享受免费配送';
                }
            }
        }

        //学校配送费
        $school_info = $params['school_id'] ? SchoolModel::find_data(['id' => $params['school_id']], 'school_name,school_freight,school_address') : [];
        //获取用户积分
        $params['score']=$this->auth->score;
        $scoreconfig=$this->config->getConfigData(['name'=> 'product_points']);
        $scoreMoney= round($params['score'] / $scoreconfig,2);
        //获取用户余额
        $money = $params['money']=$this->auth->money;
        //获取用户支付密码
        $pay_password=$this->auth->pay_password;
        if (!empty($pay_password)){
            $pay_password=2;
        }else{
            $pay_password=1;
        }


        $this->success('获取成功', ['list' => $list,
            // 'coupon_price' => $coupon_price,
             'money'=>$money,
            'pay_type'=>$pay_password,
            'scoreMoney'=>$scoreMoney,
            'totalprice' => $totalprice,
            'commodity_amount' => round($commodity_amount,2),
            'coupon_list' => $coupon_list,
            'address' => $address,
            'address_type' => $address_type,
            /*  'coupon_text' => $coupon_text,
              'coupon_id' => $coupon_id,*/
            'freight' => $freight,
            'freight_status' => "",
            'freight_name' => $freight_name,
            'activity_id' => $params['activity_id'] ? $params['activity_id'] : 0,
            'group_id' => $params['group_id'] ? $params['group_id'] : 0,
            'type' => $params['type'] ? $params['type'] : 1,
            'school_id' => $params['school_id'],
            'school_info' => $school_info,
            'coupon_status' => $coupon_status,
             'scoreconfig'=>$scoreconfig,
            'status' => $params['status']]);
    }


    /**
     * 满免按重量计费方式
     * @param $amount double 金额
     * @param $city string 市Id
     * @param $weight double 重量
     * @return double 邮费
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getWeightFreight($amount, $city, $weight)
    {
        $freight = 15;
        if (config('site.free_freight') > $amount) {
            $freightRule = model('common/Litestorefreightrule');
            $rule = $freightRule->where(['litestore_freight_id' => 36])->where("find_in_set($city,region)")->find();
            if ($rule && $city) {
                $weight -= $rule->first;
                $freight = $rule->first_fee + ceil(($weight / $rule->additional)) * $rule->additional_fee;
            }
        } else {
            $freight = 0;
        }
        return $freight;
    }


    /**
     * 自提地址列表
     * @param float 距离 单位公里
     */
    public function list_address()
    {
        $params = $this->request->post();
//        !$params['lng'] && $this->error('lng不能为空'); //经度
//        !$params['lat'] && $this->error('lat不能为空'); //纬度

        $data = $this->pickup->select_data('',
            'id,address,store_name,opening_hours,closing_hours,mobile','id asc', $params['page'], $params['pagesize']);

        foreach ($data as $k => $v) {
            $data[$k]['address'] = $v['address'];
//            $data[$k]['float'] = $v['distance'];
        }
        $this->success('获取成功', $data);
    }


    /**
     * 求两个已知经纬度之间的距离,单位为公里
     * @param lng1 $ ,lng2 经度
     * @param lat1 $ ,lat2 纬度
     * @return float 距离，单位公里
     * @author www.Alixixi.com
     */
    function getdistance($lng1, $lat1, $lng2, $lat2)
    {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137;
        return round($s, 2);
    }


    /*
     * 直接购买-获取订单页面信息
     * @param  goods_id 商品ID
     * @param  goods_spec_id  多规格ID
     * @param  type 1）正常商品  2）限时抢购  3）今日特价  4)2人团购-团购  5）团购单独购买 6)砍价购买
     * @param  num 数量
     * @param  activity_id 活动id
     * @param  group_id 开团信息id
     * */
    public function BuyImmediately()
    {

        $params = $this->request->except('s');
//        dump($params['goods_id']);
        !$params['goods_id'] && $this->error('goods_id不能为空');
        !$params['goods_spec_id'] && $this->error('goods_spec_id不能为空');


        !$params['num'] && $this->error('num不能为空');
        $where['goods_id'] = $params['goods_id'];
        //判断是否下架
        $goods_info = $this->item->find_data($where, 'goods_name,image,vip_receive,is_news');
        !$goods_info && $this->error('商品已下架');
        //判断库存是否充足
        $where['goods_spec_id'] = $params['goods_spec_id'];

        $goods_spec_info = $this->item_spec->find_data($where, 'goods_id,stock_num,key_name,spec_sku_id,goods_price,spec_image,line_price,vip_price,goods_weight ,new_price,nums');




//        $goods_spec_info['goods_price'] = judgess($this->auth->id, $goods_spec_info['goods_id'], $goods_spec_info['spec_sku_id'], $this->user, $this->item_spec,$params['num']);
        //$goods_spec_info['goods_price'] = $this->auth->vip_type != 0 && $params['type'] == 1 ? $goods_spec_info['vip_price'] : $goods_spec_info['goods_price'];
        if ($goods_info['is_news'] == 1){
            $goods_spec_info['goods_price'] = $this->auth->vip_type != 0 && $params['type'] == 1 ? $goods_spec_info['vip_price'] : $goods_spec_info['goods_price'];
        }elseif($goods_info['is_news'] == 2){
            if ($goods_spec_info['nums'] <=$params['num'] ){
                $goods_spec_info['goods_price'] = $this->auth->vip_type != 0 && $params['type'] == 1 ? $goods_spec_info['vip_price'] : $goods_spec_info['new_price'];
            }else{
                $goods_spec_info['goods_price'] = $this->auth->vip_type != 0 && $params['type'] == 1 ? $goods_spec_info['vip_price'] : $goods_spec_info['goods_price'];
            }
        }

        ($goods_spec_info['stock_num'] < $params['num'] && $params['type'] != 6) && $this->error('商品库存不足');
        $params['goods_name'] = $goods_info['goods_name'];
        $params['image'] = $goods_spec_info['spec_image'] ? config('item_url') . $goods_spec_info['spec_image'] : '';;
        $params['key_name'] = $goods_spec_info['key_name'];
        $params['goods_price'] = $params['type'] == 5 ? $goods_spec_info['line_price'] : $goods_spec_info['goods_price'];
        $params['vip_receive'] = $goods_info['vip_receive'] ? $goods_spec_info['vip_receive'] : '';
        $params['goods_weight'] = $goods_spec_info['goods_weight'] * $params['num'];
        $param[] = $params;//dump($params); exit();
        $totalprice = $params['goods_price'] * $params['num'];
//        $totalWeight = $params['goods_weight'] * $params['num'];
        return ['list' => $param, 'totalprice' => $totalprice];
//        return ['list' => $param, 'totalprice' => $totalprice, 'totalWeight' => $totalWeight];
    }


    /**
     * 生成订单
     * @param $status 类型 1）从购物车点击  2）直接购买
     * @param $is_status 类型 1）配送商品  2）自提商品
     * @param $shoping_cart_id购物车ID 多个逗号隔开
     * @param $goods_id 商品ID
     * @param $goods_spec_id  多规格ID
     * @param $num 数量
     * @param $coupon_id 优惠券 ID
     * @param $address_id 地址ID
     * @param $totalprice  总金额
     * @param string consignee  提货人
     * @param string reserved_telephone  预留电话
     * @param $freight 运费
     * @param $remark 备注
     * @param $type 1）正常商品  2）限时抢购 3）今日特价 4)2人团购-团购  5）团购单独购买 6)套餐下单
     * @param $activity_id  活动id
     * @param $group_id  拼团id
     * */
    public function setProductionOrder()
    {
        $params = $this->request->request();
        !$params['status'] && $this->error('status不能为空');
        $params['school_id'] = $params['school_id'] ? $params['school_id'] : 0;
        !$params['is_status'] && $this->error('is_status不能为空'); //新增
//        !$params['consignee'] && $this->error('consignee不能为空');
//        !$params['address_id'] && $this->error('address_id不能为空');
//        !$params['reserved_telephone'] && $this->error('reserved_telephone    不能为空');
        $params['totalprice'] = $this->request->request('totalprice', '0', 'trim');
        $params['totalprice'] < 0 && $this->error('totalprice参数错误');
        $params['uid'] = $this->auth->id;
        $totalprice = $total_num = 0;
        $order_no = order_sn($params['type']);
        $order_id = $this->order->add_data(['order_no' => $order_no]);

        switch ($params['status']) {
            case 1: //购物车下单支付
                !$params['shoping_cart_ids'] && $this->error('shoping_cart_ids不能为空');


                $shoping_cart_cout = $this->shoping_cart->getShopingCartNum(['id' => ['IN', $params['shoping_cart_ids']], 'uid' => $params['uid']] ,$params['school_id']);

                $shoping_cart_cout == 0 && $this->error('shoping_cart_ids参数有误');

                $field = 'goods_id,goods_spec_id,goods_name,image images,num total_num,key_name,activity_id,type';
                $list = $this->shoping_cart->select_data(['id' => ['IN', $params['shoping_cart_ids']], 'uid' => $params['uid']],
                    $field);
                //判断库存是否充足 获取订单详情数据
                foreach ($list as $k => $v) {
                    //获取单价
                    $where = [];
                    $where['goods_id'] = $v['goods_id'];
                    $goods_info = $this->item->find_data($where, 'deduct_stock_type ,spec_type,content,image,is_news');

                    $where['goods_spec_id'] = $v['goods_spec_id'];
                    $goods_spec = $this->item_spec->find_data($where, 'stock_num,vip_price,goods_price,spec_sku_id,goods_no,line_price,goods_weight,new_price,nums');

                    if ($goods_spec['stock_num'] < $v['total_num']){
                        $this->error('商品库存不足');
                    }
                    //获取订单商品数据
                    if ($goods_info['is_news'] == 1){
                        $goods_price = $this->auth->vip_type != 0 && $params['type'] == 1 ? $goods_spec['vip_price'] : $goods_spec['goods_price'];
                    }elseif($goods_info['is_news'] == 2){

                        if ($goods_spec['nums'] <=$v['total_num'] ){
                            $goods_price = $this->auth->vip_type != 0 && $params['type'] == 1 ? $goods_spec['vip_price'] : $goods_spec['new_price'];
                        }else {
                            $goods_price = $this->auth->vip_type != 0 && $params['type'] == 1 ? $goods_spec['vip_price'] : $goods_spec['goods_price'];
                        }
                    }
                    $list[$k]['goods_price'] = $goods_price;
                    $goods_spec['goods_price'] = $goods_price;
//                    $list[$k]['goods_price'] = $goods_spec['goods_price'];
                    $list[$k]['deduct_stock_type'] = $goods_info['deduct_stock_type'];
                    $list[$k]['spec_type'] = $goods_info['spec_type'];
                    $list[$k]['content'] = $goods_info['content'];
                    $list[$k]['total_price'] = $v['total_num'] * $list[$k]['goods_price'];
                    $list[$k]['pay_price'] = $list[$k]['total_price'];
                    $list[$k]['order_id'] = $order_id;
                    $list[$k]['user_id'] = $params['uid'];

                    $add_order_goods[] = array_merge($list[$k]->getData(), $goods_spec->getData());
                    $total_num += $v['total_num'];
                    $totalprice += $goods_price * $v['total_num'];
//                    dump($add_order_goods);die;
                }

                break;
            case 2: //直接支付
                !$params['goods_id'] && $this->error('goods_id不能为空');
                !$params['goods_spec_id'] && $this->error('goods_spec_id不能为空');
                !$params['num'] && $this->error('num不能为空');

                $where['goods_id'] = $params['goods_id'];
                $goods_info = $this->item->find_data($where, 'deduct_stock_type ,spec_type,content,goods_id,goods_name,vip_level,is_news');

                $where['goods_spec_id'] = $params['goods_spec_id'];
                $goods_spec = $this->item_spec->find_data($where, 'stock_num,goods_price,spec_sku_id,goods_no,goods_spec_id,line_price,goods_weight,key_name,spec_image,vip_price,new_price,nums');
                if ($goods_spec['stock_num'] < $params['num'] && $params['type'] != 6)
                    $this->error('商品库存不足');

                //获取订单商品数据
                $goods_info['total_num'] = $params['num'];

                switch ($params['type']) {
                    case 5: //拼团(单独购买)
                        $goods_spec['total_price'] = $params['num'] * $goods_spec['line_price'];
                        $goods_spec['goods_price'] = $goods_spec['line_price'];
                        break;
                    case 6: //套餐下单
                        $goods_spec['total_price'] = $params['num'] * $goods_spec['goods_price'];
                        break;
                    case 2://限时抢购商品
                        $goods_spec['total_price'] = $params['num'] * $goods_spec['marketing_price'];
                        $goods_spec['goods_price'] = $goods_spec['marketing_price'];
                        break;
                    default: //普通商品
                        if ($this->auth->vip_type != 0 && $params['type'] == 1) {
                            $goods_spec['total_price'] = $params['num'] * $goods_spec['vip_price'];
                            $goods_spec['goods_price'] = $goods_spec['vip_price'];
                        } else {

                            if ($goods_info['is_news'] == 1){
                                $goods_spec['total_price'] = $params['num'] * $goods_spec['goods_price'];
                            }elseif($goods_info['is_news'] == 2){
                                if ($goods_spec['nums'] <=$params['num'] ){
                                    $goods_spec['total_price'] = $params['num'] * $goods_spec['new_price'];
                                }else {
                                    $goods_spec['total_price'] = $params['num'] * $goods_spec['goods_price'];
                                }
                            }

                        }
                        break;
                }
                $goods_spec['order_id'] = $order_id;
                $goods_spec['user_id'] = $params['uid'];
                $goods_spec['activity_id'] = $params['activity_id'];
                $goods_spec['activity_type'] = $params['type'];
                $goods_spec['images'] = $goods_spec['spec_image'];
                $goods_spec['pay_price'] = $goods_spec['total_price'];
                $add_order_goods[] = array_merge($goods_info->getData(), $goods_spec->getData());
                $total_num = $params['num'];
                $totalprice = $goods_spec['total_price'];
                break;
        }
        $pay_price = $totalprice;
        if ($params['is_status'] == 1) {
            !$params['address_id'] && $this->error('address_id不能为空');
            //添加收货地址
            $address_info = model('Litestoreaddress')->getOneDate(['address_id' => $params['address_id']],
                'name,phone,province_id,city_id,region_id,detail,site');
            if (!$address_info){ $this->error('address_id错误');}
            $address_info['user_id'] = $params['uid'];
            $address_info['address_id'] = $params['address_id'];
            unset($address_info['id']);
            $address_info['order_id'] = $order_id;
            $address_info = $address_info->getData();
            $address_add = model('Litestoreorderaddress')->add_data($address_info);
        }else {
            !$params['apply_id'] && $this->error('apply_id不能为空');//新增时间2.13
//            $address_info = model('Useragentapply')->find_data(['id' => $params['apply_id'], 'store_status' => 1], 'address,store_name,mobile,site');
            $address_info=model('Shoppickup')->find_data(['id' => $params['apply_id']],'address,store_name,mobile');
            !$address_info && $this->error('代理商地址不存在');
            $address_add_info = [
                'user_id' => $params['uid'],
                'order_id' => $order_id,
                'name' => $address_info['store_name'],
                'phone' => $address_info['mobile'],
                'detail' => $address_info['address'],
                'site' => $address_info['address'],
                'createtime' => time(),
                'address_id' => $params['apply_id'],
            ];
            $address_add = model('Litestoreorderaddress')->add_data($address_add_info);
        }

        if (!$address_add) {
            $this->error('地址信息错误');
        }

        //开始事务 生成订单
        //判断优惠券
        $this->item->startTrans();
        if ($params['coupon_id'] > 0 && $params['type'] == 1) {
            $coupon_where = [
                'litestorecoupondata.id' => $params['coupon_id'],
                'litestorecoupondata.user_id' => $params['uid'],
                'litestorecoupondata.is_used' => 0,
                'litestorecoupondata.use_start_time' => ['ELT', time()],
                'litestorecoupondata.use_end_time' => ['EGT', time()]
            ];

            $coupon_info = model('common/Litestorecoupondata')->where($coupon_where)->field('id')->with('coupon')->find();

            $coupon_info->use_time = time();
            $coupon_info->is_used = 1;
            $coupon_info->allowField(true)->save();


            if ($coupon_info) {
                $this->calcGoodsPrice($pay_price, $coupon_info->coupon->deduct, $add_order_goods);
                $pay_price = $pay_price - $coupon_info->coupon->deduct;
            }
        }

        //运费
        $pay_price1 = $pay_price + $params['freight'];
        //减去积分换算的运费
        $pay_price2 = $pay_price1 - $params['score'];
        $pay_price= $pay_price2 - $params['use_money'];
//        $this->auth->score - $params['score'] * ;
        /*if ($pay_price * 100 > $params['totalprice'] * 100)
            $this->error('订单金额有误');*/
        //生成订单
        if ($pay_price <= 0){
            $pay_price = 0;
        }
        $order_array = [
            'total_price' => $params['totalprice'],
            'pay_price' => $pay_price,
            'use_integral'=> $params['score'],
            'use_money'=>$params['use_money'],
            'freight_price' => $params['freight'],//邮费2/20新增
            'express_price' => $params['freight'],
            'user_id' => $params['uid'],
            'coupon_id' => $coupon_info ? $coupon_info->id : 0,
            'total_num' => $total_num,
            'order_type' => $params['type'] == 4 ? 20 : 10,
            'remark' => $params['remark'],
            'coupon_price' => $coupon_info ? $coupon_info->coupon->deduct : 0,
            'activity_id' => $params['activity_id'] ? $params['activity_id'] : 0,
            'activity_type' => $params['type'] ? $params['type'] : 1,
            'createtime' => time(),
            'is_status' => $params['is_status'], //新增时间2.13
            'consignee' => empty($params['consignee']) ? '' : $params['consignee'],//新增时间2.13
            'reserved_telephone' => $params['reserved_telephone'],//新增时间2.13
            'apply_id' => $params['apply_id'],//新增时间2.13
            'nickname' => $this->auth->nickname,//新增时间3.18
            'mobile' => $this->auth->mobile,//新增时间3.18
            'school_name' => $params['school_id'] > 0 ? SchoolModel::getSchoolName($params['school_id']) :'',
            'school_id' => $params['school_id'],
            'type' => $params['status'] == 2 ? $goods_info['vip_level'] : 0 //vip套餐等级 0 普通商品 1普通VIp 2尊享VIP
        ];

        $add_order = $this->order->update_data(['id' => $order_id], $order_array);
        if (!$add_order) {
            $this->error('订单错误');
        }
        //添加订单明细
        $add_order_goods = $this->order_goods->add_data($add_order_goods);
        if (!$add_order_goods) {
            $this->error('订单明细错误');
        }

        //删除购物车
        $del_shoping_cart_ids = $params['status'] == 2 ? 1 : $this->shoping_cart->delete_data(['id' => ['IN', $params['shoping_cart_ids']], 'uid' => $params['uid']]);

        //团购数据添加
        if ($params['type'] == 4 && $params['activity_id']) {

            $model = new Marketing();
            $model->addJoinGroupbuy($params['uid'], $params['goods_id'], $params['activity_id'], $order_id, $params['group_id']);
        }
        if ($add_order_goods && $add_order && $del_shoping_cart_ids && $address_add) {
            $this->item->commit();
            $this->success('提交成功', ['order_no' => $order_no, 'totalprice' => round($order_array['pay_price'], 2),'use_score' =>$order_array['use_integral']]);
        } else {
            $this->item->rollback();
            $this->error('操作失败');
        }
    }

    /**
     * 计算商品除去优惠券价格
     * @param $totalGoodsPrice
     * @param $discount
     * @param $goodsList
     */
    public function calcGoodsPrice($totalGoodsPrice, $discount, &$goodsList)
    {
        foreach ($goodsList as $k => $goods) {
            $goodsList[$k]['pay_price'] = $goods['total_price'] - number_format($goods['total_price'] / $totalGoodsPrice * $discount, 2);
        }
    }

    /**
     * 获取运费
     * @param $totalprice 订单金额
     * @param $total_num 订单数量
     *
     * */
    public
    function getFreight($totalprice, $total_num)
    {
        $model = new Litestorefreight();
        $freight = $model->detail(config('site.freight'));
        if (!$freight)
            return 0;
        $data = $freight['rule'][0];

        switch ($freight['method']) {
            case 10: //件数
                if ($total_num <= $data['first']) {
                    $freight = $data['first_fee'];
                } else {
                    $total_num--;
                    $freight = $data['first_fee'] + intval($total_num / $data['additional']) * $data['additional_fee'];
                }
                break;
            case 20://重量

                break;
            case 30://金额

                if ($totalprice <= $data['first']) {
                    $freight = $data['first_fee'];
                } else {

                    $freight = intval($total_num / $data['additional']) * $data['additional_fee'];
                }
                break;
        }
        return number_format($freight, 2);
    }


    /*
     * 订单列表
     * @param int order_status 1）全部 10）待支付  20）待发货  30）待收货  40）待评价
     * 订单状态:0=已取消,10=待付款,20=待发货，30待收货，40待评价，50交易完成
     *  is_del = 0  order_type=10  refund_status=40
     * order_type = 10）商城订单 20）拼团订单
     * @param page
     * @param pagesize
     */
    public
    function getOrderLists()
    {

        $params = $this->request->request();//$this->error($params['token']);
        !$params['order_status'] && $this->error('order_status不能为空');
        $params['uid'] = $this->auth->id;
        $where['is_del'] = 0;
        $where['type'] = 0;
        $where['order_type'] = $params['order_type'] ? $params['order_type'] : '10' ;
        $where['user_id'] = $params['uid'];


        $where['is_status'] = $params['is_status'];
//        $where['order_refund'] = ['neq', 1];

        if ($params['order_status'] != 1) {
            $where['order_status'] = $params['order_status'];
        }
     //   pre($where);
        $field = 'id ,order_no,pay_price,total_num,order_status,refund_status,activity_id,order_type ,pay_status,is_status,type';
        $order_list = $this->order->getPageDate($where, $field, 'id desc', $params['page'], $params['pagesize']);

        // 判断是否为拼团商品
        if ($params['order_type'] == 20) {
            foreach ($order_list as $k => $v) {
                $group_num = model('Joingroupbuy')->where(['order_id' => $v['id'], 'type' => 1])->field('join_num,group_num')->find();
                $need_number = $group_num['group_num'] - $group_num['join_num'];
                $order_list[$k]['need_number'] = $need_number < 0 ? 0 : $need_number;
            }
        }
        $this->success('获取成功', ['list' => $order_list]);
    }


    /*
     * 订单详情
     * @param $order_id
     * */
    public
    function getOrderDetail()
    {
        $params = $this->request->request();
        $orderId = $this->request->request('order_id');
        !$orderId && $this->error('order_id不能为空');
        $params['uid'] = $this->auth->id;

        $field = 'id ,order_no,pay_price,order_status,freight_status,zf_type,order_type,is_status,consignee,reserved_telephone,apply_id,is_status,
        refund_status,express_price,coupon_price,total_price,createtime,remark,pay_status,freight_time,activity_id,pay_status,use_integral,use_money';

        $order_info = $this->order->find_data(['id' => $orderId], $field);
        //订单商品详情
        $order_info['sub'] = model('Litestoreordergoods')
            ->select_data(['order_id' => $params['order_id'], 'user_id' => $params['uid']],
                'goods_id,goods_name,images,key_name,is_refund,total_num,goods_price', $params['uid']);

        //查出团购数量（几人团购）
        if ($order_info['order_type'] == '20') {
            $order_info['group_num'] = $this->groupbuy_goods_model
                ->where(['id' => $order_info['activity_id']])->value('group_num');
            //查看已团人数
            $join_num = model('Joingroupbuy')->where(['order_id' => $orderId])->value('join_num');
            //还需多少人
            $order_info['need_number'] = $order_info['group_num'] - $join_num;
        }
        //收货地址
        $address_where = [ //where条件
            'user_id' => $this->auth->id,
            'order_id' => $orderId
        ];

        $address_info = model('Litestoreorderaddress')->find_data($address_where, 'id,name,phone,site');
        if ($order_info['is_status'] == 2) {
            $userAgentInfo = model('Useragentapply')->get($order_info['apply_id']);
            $address_info['latitude'] = $userAgentInfo->latitude;//纬度
            $address_info['longitude'] = $userAgentInfo->longitude;//经度
        }
        $order_info['address'] = !empty($address_info) ? $address_info : '';
        $order_info['count_down'] = 0;

        //支付倒计时
        $configlist = $this->order_config_model->column('name,value');
        if ($order_info['order_status'] == 10) {
            $order_info['count_down'] = $this->getCountDown($order_info['createtime'], $configlist['un_order_time'], 60);
        }
        //收货倒计时
        if ($order_info['order_status'] == 30) {  //删除 && $order_info['order_status'] == 20;
            $order_info['count_down'] = $this->getCountDown($order_info['freight_time'], $configlist['confirm_order_time'], 86400);
        }
        //客服电话
        $order_info['kf_phone'] = config('site.kf_phone');
        $scoreconfig=$this->config->getConfigData(['name'=> 'product_points']);
        $order_info->use_integral=$order_info->use_integral/$scoreconfig;
        $this->success('获取成功', $order_info);
    }


    /*
     * 倒计时
     *
     * */
    public
    function getCountDown($order_time, $config_time, $miao)
    {
        $end_time = $order_time + $config_time * $miao;
        $count_down = $end_time - time();
        return $count_down > 0 ? $count_down : 0;
    }


    /*
     * 取消订单
     *  @param  $order_id 订单ID
     * */
    public
    function setCancelOrder()
    {
        $params['order_id'] = $this->request->request('order_id');
        !$params['order_id'] && $this->error('order_id不能为空');

        $where['user_id'] = $this->auth->id;
        $where['id'] = $params['order_id'];
        $order_info = $this->order->find_data($where, 'order_status, coupon_id');
        $order_info['order_status'] != '10' && $this->error('order_id参数错误');

        if ($order_info['coupon_id'])
            model('Litestorecoupondata')->update_coupon_status($order_info['coupon_id'], $this->auth->id, 2);

        if ($this->order->update_data($where, ['order_status' => 0])) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }


    /*
     * 删除订单
     *  @param  $order_id 订单ID
     * */
    public
    function setDelOrder()
    {
        $orderId = $this->request->request('order_id');
        !$orderId && $this->error('order_id不能为空');

        $where['user_id'] = $this->auth->id;
        $where['id'] = $orderId;
        $order_info = $this->order->find_data($where, 'order_status');

        $this->order->update_data($where, ['is_del' => -1]) ? $this->success('操作成功') : $this->error('操作失败');
    }

    /*
     * 查看物流
     * @param  $order_id 订单ID
     * */
    public
    function lookLogistics()
    {
        $orderId = $this->request->param('order_id');
        $id = $this->request->param('id');

        !$orderId && $this->error('order_id不能为空');

        $orderInfo = Litestoreorder::get($orderId);

        !$orderInfo && $this->error('订单信息不存在');
//        ($orderInfo->order_status != 30 || $orderInfo->freight_status != 20) && $this->error('订单状态有误');

        if ($orderInfo->type != 0) {
            if ($orderInfo->total_frequency == 1) {
                $writeOffInfo = Litestoreordership::getByOrderId($orderInfo->id);
            } else {
                !$id && $this->error('id不能为空');
                $writeOffInfo = Litestoreordership::get($id);
            }
            !$writeOffInfo && $this->error('id有误');
            $expressNo = $writeOffInfo->express_no;
            $company = $writeOffInfo->express_company;
        } else {
            $expressNo = $orderInfo->express_no;
            $company = $orderInfo->express_company;
        }
        $code = \app\common\model\Kdniao::getByCompany($company);

        //获取手机号后四位
        $orderAddress = \app\common\model\Litestoreorderaddress::getByOrderId($orderId);
        $phone = substr($orderAddress->phone, -4);
        //获取详情图片
        $order_good = $this->order_goods->find_data(['order_id' => $orderId], 'images');
        $img = $order_good['images'] ? config('item_url') . $order_good['images'] : '';

        //查看物流
        $express_model = controller('Express');
        $express_model->getOrderTracesByJson($code->code, $expressNo, $company, $img, $phone);
    }

    /**
     * 获取套餐订单列表
     * @param int type 1配送订单 2自提订单
     * @param int page
     * @param int page_size
     */
    public function getPackageList()
    {
        $type = $this->request->param('type');
        $page = $this->request->param('page');
        $pageSize = $this->request->param('page_size');

        !$type && $this->error('type不能为空');
        !$page && $this->error('page不能为空');
        !$pageSize && $this->error('page_size不能为空');

        $where = [
            'type' => ['neq', 0],
            'user_id' => $this->auth->id,
            'is_status' => $type,
            'pay_status' => 20
        ];
        $field = 'id,order_no,current_frequency,total_num,pay_price,total_frequency,freight_status';
        $list = $this->order->getPageDate($where, $field, 'id desc', $page, $pageSize);
        $this->success('获取成功', ['list' => $list]);
    }

    /**
     * 获取套餐订单发货记录
     * @param int order_id 订单id
     */
    public
    function getDeliveryRecord()
    {
        $orderId = $this->request->param('order_id');
        !$orderId && $this->error('order_id不能为空');
        $list = model('common/Litestoreordership')->select_data(['order_id' => $orderId]);
        $this->success('获取成功', ['list' => $list]);
    }

    /**
     * 获取套餐订单详情
     * @param int order_id 订单id
     */
    public
    function getPackageDetail()
    {
        $orderId = $this->request->param('order_id');
        $field = 'id,order_no,pay_price,zf_type,createtime,is_status,pay_time,consignee,reserved_telephone,apply_id,total_price,current_frequency,total_frequency,freight_status';
        $info = $this->order->field($field)->where(['id' => $orderId])
            ->with(['address' => function ($query) {
                $query->field('order_id,name,phone,site,detail');
            }, 'goods' => function ($query) {
                $query->field('order_id,goods_name,images,total_num,total_price');
            }])
            ->find();
        $this->success('获取成功', ['info' => $info]);
    }

    /**
     * 获取提货记录
     * @param int order_id
     */
    public
    function getWriteOffRecord()
    {
        $orderId = $this->request->param('order_id');
        !$orderId && $this->error('order_id不能为空');
        $list = model('common/Litestoreorderwriteoff')->select_data(['order_id' => $orderId]);
        $this->success('获取成功', ['list' => $list]);
    }


    /*
     * 获取申请售后页面信息
     * @param $order_id
     * */
    public function getApplyAfterSaleInfo()
    {
        $orderId = $this->request->request('order_id');
        !$orderId && $this->error('order_id不能为空');

        $field = 'id,coupon_price,order_no,pay_price,total_num,express_price,refund_money,use_money,use_integral';
        $orderInfo = $this->order->where("id=$orderId")->field($field)->with(['goods' => function ($query) {
            $query->field('id,order_id,total_price,total_num,key_name,goods_name,is_refund,images')->where(['is_refund' => ['in', '0,4']]);
        }])->find();
        $orderInfo->goods_price = $orderInfo->pay_price + $orderInfo->coupon_price - $orderInfo->express_price;
        $orderInfo->pay_price = $orderInfo->pay_price - $orderInfo->refund_money;
        foreach ($orderInfo->goods as $k => $goods) {
            if ($orderInfo->goods_price ==0 ){
                $orderInfo->goods[$k]->refund_money=0;
            }else{
                $orderInfo->goods[$k]->refund_money = round($goods->total_price - $goods->total_price / $orderInfo->goods_price * $orderInfo->coupon_price, 2);
            }
        }
        $scoreconfig=$this->config->getConfigData(['name'=> 'product_points']);
        $orderInfo->use_integral= round($orderInfo->use_integral / $scoreconfig,2);

        $this->success('获取成功', $orderInfo);
    }

    /**
     * 申请售后，申请退款
     * @param int order_id 订单id
     * @param string order_goods_id 订单商品id
     * @param string remark 退款理由
     * @throws \think\exception\PDOException
     */
    public
    function applyAfterSale()
    {
        $orderId = $this->request->request('order_id');
        $orderGoodsId = $this->request->request('order_goods_id');
        $remark = $this->request->request('remark');
        $img = $this->request->request('img');
        $refund = $this->request->request('status'); //后台退款
        $description = $this->request->request('description');
        $user_money=$this->request->request('use_money');
        $use_qrcode=$this->request->request('use_integral');
        // dump($this->request->param(''));exit();
        !$orderId && $this->error('order_id不能为空');
        !$orderGoodsId && $this->error('order_goods_id不能为空');

        //验证订单状态
        $orderInfo = $this->order->get($orderId);
        !$refund && $orderInfo->user_id != $this->auth->id && $this->error('你没有权限执行此操作');
        !$refund && $orderInfo->pay_status != 20 && $this->error('订单状态有误');
        $orderInfo->refund_status == 10 && $this->error('订单已在售后中，请勿重复提交');

        //计算总退款金额
        $where = ['order_id' => $orderId, 'id' => ['in', $orderGoodsId], 'is_refund' => ['in', '0,4']];

        //计算全部退款商品的总金额
        $totalPrice = $this->order_goods->where($where)->sum('total_price');

        //用支付金额减去邮费加上优惠金额 计算出订单商品总金额
        $goodsPrice = $orderInfo->total_price - $orderInfo->express_price + $orderInfo->coupon_price;

        /*
         * 使用优惠券时计算退款金额方法
         * 退款金额 - 退款金额 / 商品总金额 * 优惠金额 = 实际退款金额
         */
        $refundMoney = round($totalPrice - $totalPrice / $goodsPrice * $orderInfo->coupon_price, 2);

        //退回优惠券id 0代表不退优惠券
        $refundCouponId = 0;

        //如果退款金额大于或等于商品总价，则代表全款退
        if ($totalPrice + $orderInfo->express_price - $orderInfo->coupon_price >= $orderInfo->pay_price) {
            //退款金额为支付金额
            $refundMoney = $orderInfo->pay_price;
            $refundCouponId = $orderInfo->coupon_id;
            $orderInfo->order_refund = 1;

            //如果退款金额加上已退款金额，大于等于商品总金额，则代表订单全部退款
        } elseif ($refundMoney + $orderInfo->refund_money + $orderInfo->express_price >= $orderInfo->pay_price) {
            //退回金额为 支付金额-已退金额
            $refundMoney = $orderInfo->pay_price - $orderInfo->refund_money;
            $refundCouponId = $orderInfo->coupon_id;
            $orderInfo->order_refund = 1;

        } /*elseif ($orderInfo->coupon_id != 0) {
            //用户领取优惠券信息
            $coupon = $this->couponData->find_data(['id' => $orderInfo->coupon_id]);
            //优惠券完整信息
            $couponInfo = $this->coupon->find_data(['id' => $coupon->litestore_coupon_id]);
            if ($orderInfo->total_price - $totalPrice < $couponInfo->enoug) {//除去退款商品 不满足优惠条件时
                $refundMoney = $totalPrice - $orderInfo->coupon_price;
                $refundCouponId = $orderInfo->coupon_id;
            }
        }*/

        //退款记录
        $data = [
            'use_money'=>$user_money,
            'use_qrcode'=>$use_qrcode,
            'order_no' => $orderInfo->order_no,
            'order_goods_id' => $orderGoodsId,
            'refund_no' => order_sn(7),
            'money' => $refundMoney,
            'remark' => $remark,
            'coupon_id' => $refundCouponId,
            'uid' => $orderInfo->user_id,
            'order_id' => $orderInfo->id,
            'img' => $img,
            'description' => $description,
        ];

        //修改订单为售后订单
        $orderInfo->refund_status = 10;
        $orderInfo->apply_after_sale_time = time();
        $this->coupon->startTrans();

        //写入退款数据
        $orderRefund = Litestoreorderrefund::create($data);

        //修改订单商品的退款状态和订单状态
        if ($this->order_goods->save(['is_refund' => 1], $where) && $orderRefund && $orderInfo->save()) {
            $this->coupon->commit();
            if ($refund) {
                $this->request->get(['ids' => $orderRefund->id]);
                return true;
            }
            $this->success('申请退款成功');
        }
        $this->coupon->rollback();
        if ($refund)
            return false;
        $this->error('申请退款失败');
    }

    /**
     * 退款回调
     * @param $result
     * @return bool
     */
    public
    static function refundCallBack($result)
    {
        //查询退款订单信息
        $refundInfo = model('common/Litestoreorderrefund')->find_data(['refund_no' => $result['out_refund_no']]);

        if ($refundInfo->refund_time && $refundInfo->status == 2 && $refundInfo->refund_money) {
            return true;
        }

        //查询订单信息
        $orderInfo = Litestoreorder::getByOrderNo($refundInfo->order_no);

        $where = ['order_id' => $orderInfo->id, 'id' => ['in', $refundInfo->order_goods_id], 'is_refund' => '1'];
        $orderGoods = model('common/Litestoreordergoods');
        $groupGoods = model('Groupbuygoods');

        //退款回调成功时
        if ($result['refund_status'] == 'SUCCESS') {
            //修改退款订单表状态和数据
            $refundMoney = $result['settlement_refund_fee'] / 100;
            $refundInfo->status = 2;
            $refundInfo->refund_money = $refundMoney;
            $refundInfo->refund_time = strtotime($result['success_time']);

            //订单全额退款时，修改订单状态
            if ($orderInfo->pay_price == $refundMoney || $orderInfo->pay_price == $refundMoney + $orderInfo->refund_money) {
                $orderInfo->order_status = 0;
                $orderInfo->refund_money = $orderInfo->pay_price;
            } else {
                $orderInfo->refund_money += $refundMoney;
            }

            $goodsList = $orderGoods->where($where)->select();

            Db::startTrans();

            switch ($orderInfo->activity_type) {
                case 1://普通订单
                case 2://限时抢购
                    foreach ($goodsList as $item) {
                        if ($item->goods->is_marketing == 2) {
                            $item->discount->stock_num += $item->total_num;
                            $item->discount->sales -= $item->total_num;
                            $item->discount->save();
                        }
                        $item->spec->stock_num += $item->total_num;
                        $item->spec->goods_sales -= $item->total_num;
                        $item->goods->sales_actual -= $item->total_num;
                        $item->goods->stock_num += $item->total_num;
                        $item->spec->save();
                        $item->goods->save();
                    }
                    break;
                case 4://拼团订单
                    foreach ($goodsList as $item) {
                        $pay = controller('pay');
                        $pay->group_buy_goods_order($orderInfo->id, $item->activity_id, $item->total_num, 1);
                    }
                    break;
                case 5://拼团单独购买
                    foreach ($goodsList as $item) {
                        $groupGoods->updateSpec($item->activity_id, $item->total_num, 2);//执行退款 减销量 加库存操作
                    }
                    break;
            }
            //需要退还优惠券时
            if ($refundInfo->coupon_id) {
                //修改优惠券状态
                $data = ['is_used' => 0, 'use_time' => null];
                model('common/Litestorecoupondata')->save($data, ['id' => $refundInfo->coupon_id]);
            }

            $orderInfo->order_refund == 1 && $orderInfo->order_refund = 0;
            if ($orderGoods->save(['is_refund' => 2], $where) && $orderInfo->save() && $refundInfo->save()) {
                Db::commit();
                return true;
            }
        } elseif ($result['refund_status'] == 'CHANGE') {
            $orderGoods->save(['is_refund' => 3], $where);
            $refundInfo->save(['status' => 3]);
            Db::commit();
            return true;
        }
        Db::rollback();
        return false;
    }

    /*
     * 获取售后订单列表
     * @param $refund_status 0 全部  1 申请中 2已通过 3 已拒绝 4 已取消
     * */
    public
    function getAfterSaleLists()
    {
        $refundStatus = $this->request->param('refund_status');
        $page = $this->request->param('page');
        $pageSize = $this->request->param('page_size');
        is_null($refundStatus) && $this->error('refund_status不能为空');
        !$page && $this->error('page不能为空');
        !$pageSize && $this->error('page_size不能为空');

        $field = 'id,order_goods_id,order_no,create_time,money,refund_time,review_time,apply_status,order_id,review_remark';
        $where = [
            'uid' => $this->auth->id,
            'apply_status' => $refundStatus,
        ];
        if ($refundStatus == 0)
            $where['apply_status'] = ['IN', '1,2,3,4'];
//        dump($where);die;
        $list = $this->order_refund->where($where)->field($field)->page($page, $pageSize)->order('id desc')->select();

        $goodsField = 'id,goods_name,goods_price,images,total_num,total_price,key_name,is_refund';

        foreach ($list as $i => $info) {
            $goodsList = $this->order_goods->where(['id' => ['in', $info->order_goods_id]])->field($goodsField)->select();
            foreach ($goodsList as $k => $goods) {
                $goodsList[$k]->images = $goods->images ? config('item_url') . $goods->images : '';
            }
            $list[$i]['goods'] = $goodsList;
        }

        $this->success('获取成功', ['list' => $list]);
    }


    /*
     * 获取售后详情
     * @param $refund_id 售后ID
     * */
    public
    function getAfterSaleDetail()
    {
        $refundId = $this->request->param('refund_id');
        !$refundId && $this->error('refund_id不能为空');

        $where = [
            'uid' => $this->auth->id,
            'id' => $refundId,
        ];

        $refundTime = config('site.refund_review_time') * Date::DAY - time();
        $filed = "*,(create_time+$refundTime) as countdown";

        $info = $this->order_refund->where($where)->field($filed)->find();

        $goodsField = 'id,goods_name,goods_price,images,total_num,total_price,key_name,is_refund';
        $goodsList = $this->order_goods->where(['id' => ['in', $info->order_goods_id]])->field($goodsField)->select();

        $info->img = $info->img ? config('item_url') . $info->img : '';
        foreach ($goodsList as $k => $goods) {
            $goodsList[$k]->images = $goods->images ? config('item_url') . $goods->images : '';
        }
        $info['goods'] = $goodsList;
        $this->success('获取成功', ['info' => $info, 'kf' => config('site.kf_phone')]);
    }

    /**
     * 取消售后订单
     * @throws \think\exception\DbException
     */
    public
    function cancelRefund()
    {
        $refundId = $this->request->param('refund_id');
        !$refundId && $this->error('refund_id不能为空');

        $refundInfo = $this->order_refund->get($refundId);
        (!$refundId || $refundInfo->uid != $this->auth->id) && $this->error('售后记录不存在');

        $order = Litestoreorder::get($refundInfo->order_id);
        $refundInfo->apply_status = 4;
        $order->refund_status = 40;
        $order->order_refund == 1 && $order->order_refund = 0;
        $this->order_refund->startTrans();
        $result = \app\common\model\Litestoreordergoods::update(['is_refund' => 0], ['id' => ['in', $refundInfo->order_goods_id]]);
        if ($refundInfo->save() && $order->save() && $result) {
            $this->order_refund->commit();
            $this->success('取消成功');
        }
        $this->error('取消失败');
        $this->order_refund->rollback();

    }

    /**
     *删除售后订单
     * @param int refund_id 售后订单id
     */
    public
    function delete_refund_order()
    {
        $refundId = $this->request->param('refund_id');
        !$refundId && $this->error('refund_id不能为空');

        $refundInfo = $this->order_refund->get($refundId);
        (!$refundId || $refundInfo->uid != $this->auth->id) && $this->error('售后记录不存在');
        if ($refundInfo->delete()) {
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }


//***********************定时任务*********************************//

    /*
     * 未支付订单，自动取消
     * 每分钟走一次
     *    * * * * * /usr/local/php5.6.31/bin/php  /home/webroot/yanyu.0791jr.com/public/index.php /addons/cront
    ab/autotask/index >> /var/www/fastadmin/runtime/log/crontab.`date +\%Y-\%m-\%d`.log 2>&1
     * */

    /**
     * 套餐订单修改状态
     */
    public
    function setOrderStatus()
    {
        $time = time() + 3 * Date::DAY;
        $where = [
            'order_status' => '30',
            'type' => ['in', '1,2'],
            'total_frequency' => ['gt', 'current_frequency'],
            'ship_time' => ['lt', $time]
        ];
        Litestoreorder::update(['order_status' => 20], $where);
    }

    /**
     * 自动通过退款申请
     * @throws \think\exception\DbException
     */
    public
    function automaticRefund()
    {
        $time = time() - config('site.refund_review_time') * Date::DAY;
        $where = [
            'create_time' => $time,
            'apply_status' => 1,
        ];

        $refundList = $this->order_refund->where($where)->select();

        foreach ($refundList as $refund) {
            Db::startTrans();
            $order = Litestoreorder::get($refund->order_id);
            $refund->apply_status = 2;
            $refund->review_time = time();
            if ($refund->save() && Pay::refund($refund, $order)) {
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
                    $goodsList = Litestoreordergoods::all($refund->order_goods_id);
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
                Db::commit();
                continue;
            }
            Db::rollback();
        }
    }

    /**
     * 自动收货与评价
     * 走确认收货和发表评价接口
     * 每15天未收货自动收货和评价
     *
     */
    public
    function automatic_re_and_eva()
    {
        $end_time = time() - config('site.automatic_re_and_eva') * 86400;
        $evaluate = controller('Evaluate');
        $where = [
            'freight_status' => '20',
            'order_status' => '30',
            'freight_time' => ['lt', $end_time]
        ];
        $orderList = $this->order->select_all($where, 'id,user_id');
        foreach ($orderList as $order) {
            $this->confirm_receipt($order['id'], 1);
            $evaluate->addevaluate($order['id'], $order['user_id'], 1);
        }
        echo '成功';
        die;
    }


    public
    function setAutomaticCancellation()
    {
        $un_order_time = Config('site.un_order_time');
        $end_time = time() - $un_order_time * 60;
        $where = [
            'pay_status' => 10,
            'order_status' => 10,
            'createtime' => ['lt', $end_time]
        ];

        $order_list = $this->order->select_data($where, 'coupon_id ,user_id');
        if ($order_list != null) {
            foreach ($order_list as $k => $v) {
                $set_coupon_record = model('Couponrecord')->update_coupon_status($v['coupon_id'], $v['user_id'], 2);
            }
        }

        $updata_order_status = $this->order->update_data($where, ['order_status' => 0]);
        if ($updata_order_status)
            echo '成功';
    }


    /**
     * 商家已发货，一直没收货自动收货
     * 每分钟走一次
     * */
    public
    function setConfirmOrder()
    {
        $this->model = model('Config');
        $configlist = $this->model->where(['id' => 67])->column('name,value');

        $end_time = time() - $configlist['confirm_order_time'] * 86400;
        $where = [
            'freight_status' => 20,
            'order_status' => 30,
            'freight_time' => ['lt', $end_time]
        ];

        $updata_order_status = $this->order->update_data($where, ['order_status' => 40, 'receipt_time' => time()]);
        if ($updata_order_status)
            echo '成功';
    }

    /**
     * 用户已收货，一直未评价自动评价
     * 每分钟走一次
     */
    public
    function setCommentOrder()
    {
        $this->model = model('Config');
        $configlist = $this->model->where(['id' => 89])->column('name,value');
        $end_time = time() - $configlist['automatic_evaluation'] * 86400;

        $where = [
            'receipt_status' => '20',
            'order_status' => '40',
            'freight_time' => ['et', $end_time]
        ];
        $updata_order_status = $this->order->update_data($where, ['order_status' => '50']);
        if ($updata_order_status)
            echo '成功';
    }

    /**
     * 确认收货
     * @param int order_id 订单id
     * @param int user_id 用户id
     * @param int $status 1)自动收货 0）正常收货
     */
    public
    function confirm_receipt($order_id = '', $status ='')
    {
        $params = $this->request->request();
//        !$params['order_id'] && $this->error('order_id不存在');
        $uid = $this->auth->id;

        if (!$status) {
            $where['user_id'] = $uid;
            $where['id'] = $params['order_id'];
        } else {
            $where['id'] = $order_id;
        }

        //获取待收货订单信息
        $order_info = $this->order->find_data($where, '*');
        $order_info['order_status'] != '30' && $this->error('订单状态错误');
//        $addDistribution = $this->addDistributionOrder($order_info, $uid, $params['order_id']);
        $addDistribution = 1;
        $order_info->type == 0 && Pay::commission($order_info);

         //获取等级限制
        $leve=UserLevel::where('id',2)->find();
        $leve->upgrade_price;

        if ($this->order->receipt($where) && $addDistribution) {
            if ($order_info->pay_price >= $leve->upgrade_price){
                $data =\app\common\model\User::get($uid);
                if ($data->vip_type==0){
                    $data->vip_type=1;
                    $now = date('Y-m-d H:i:s',time());
                    $data->expiration_time=strtotime("+1years",strtotime($now));
                    $data->save();
                }
            }
            \app\common\model\User::score($order_info->integral, $uid, '下单获得积分', 1);

            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    public
    function addDistributionOrder($order_info, $uid, $order_id)
    {
        //判断是否有上级分销商
        $user_rebate_model = model('UserRebate');
        $rebate_info = $user_rebate_model->find_data(['uid' => $uid], 'first_id,second_id');

        if ($rebate_info) {
            model('UserRebate')->startTrans();
            //获取佣金比率
            $first_rate = model('config')->getConfigData(['name' => 'commission_rate']);
            $second_rate = model('config')->getConfigData(['name' => 'second_commission_rate']);
            $rebate_info['second_id'] = empty($rebate_info['second_id']) ? 0 : $rebate_info['second_id'];
            //获取订单商品信息
            $order_sku_info = $this->order_goods->where(['order_id' => $order_id])->find();
            //获取一级分销金额
            $first_money = $order_info['pay_price'] * $first_rate; //？？？？
            //获取二级分销金额
            $second_money = empty($rebate_info['second_id']) ? 0 : $order_info['pay_price'] * $second_rate; //？？？
            //添加分成记录
            $rebate_data = [
                'superior_id' => $rebate_info['first_id'],
                'order_sn' => $order_info['order_no'],
                'title' => $order_sku_info['goods_name'],
                'image' => $order_sku_info['images'],
                'create_time' => time(),
                'money' => $first_money,
                'act_pay_money' => $order_info['pay_price'],
                'uid' => $uid,
            ];
            $add_first = model('UserRebateBack')->insert($rebate_data);
            //$add_first_balance = $this->user->where(['id' => $rebate_info['first_id']])->setInc('balance', $first_money);
            $add_money = $this->user->balance($first_money, $rebate_info['first_id'], '分成佣金', '50');
            if ($rebate_info['second_id']) {
                $rebate_data['superior_id'] = $rebate_info['second_id'];
                $rebate_data['type'] = 2;
                $rebate_data['money'] = $second_money;
                $add_second = model('UserRebateBack')->insert($rebate_data);
//                $add_second_balance = $this->user->where(['id' => $rebate_info['second_id']])->setInc('balance', $second_money);
                !$add_second && model('UserRebateBack')->rollback();
                //用户佣金增加
                if (!$this->user->balance($second_money, $rebate_info['second_id'], '分成佣金', '50')) {
                    $this->user->rollback();
                    $this->error('增加佣金失败');
                }
            }
            if ($add_first && $add_money) {
                model('UserRebate')->commit();
                return true;
            } else {
                model('UserRebate')->rollback();
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 团购未成功，自动退款
     *
     */
    public
    function refundGroupbuy()
    {
        $this->join_groupbuy_model = new Joingroupbuy();
        $where = ['status' => 1, 'type' => 1];

        $list = $this->join_groupbuy_model->select_data($where, 'hour, add_time , pid');
        if ($list != null) {

            $model = new Pay();
            foreach ($list as $k => $v) {
                if (($v['add_time'] + $v['hour'] * 3600) < time()) {

                    //退货 退款
                    $model->refund_groupbuy($v['pid'], 1);
                }
            }
        }

        echo 'ok';
        exit;
    }


}
