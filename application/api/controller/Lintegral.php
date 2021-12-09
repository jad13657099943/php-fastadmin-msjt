<?php

namespace app\api\controller;

use app\common\controller\Api;
//use Boris\Config;
use think\Db;
use Think\Config;

class Lintegral extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->integral = model('Integralitem');
        $this->user = model('User');
        $this->banner = model('Cmsblock');
        $this->score_log = model('ScoreLog');
        $this->address = model('Litestoreaddress');
        $this->integral_order = model('Integralorder');
        $this->archives = model('Cmsarchives');


    }

    /*
     * 积分商城首页
     * @param uid 用户id
     * @param
     */
    public function Lintegral()
    {

        $data['uid'] = $this->auth->id;
        $data = $this->request->request();

        !$data['uid'] && $this->error('请登录后操作!');
        //积分商城详情图
        $integral_banner = $this->banner->find_data(['cate_id' => 20],'images');
        $integral_banner = $this->setPlitJointImages($integral_banner['images']);

        //积分规则
        $intergialUrl = $this->archives->find_data(['id' => 12],'title,content');
        //用户积分
        $integral = $this->user->where(['id' => $data['uid']])->value('score');
        $integral = empty($integral) ? 0 : $integral;

        //积分商品
        $integral_list = $this->integral->select_page('','title,img,integral,id,inventory');
        if (!empty($integral_list)){
            foreach ($integral_list as $k => $v){
                $imgs = explode(',',$v['img']);
                $integral_list[$k]['img'] = config('item_url').$imgs[0];
            }
        }
        $this->success('成功',['ad' => $integral_banner,'integral' => $integral,'integral_list' => $integral_list,'intergialUrl'=>$intergialUrl]);

    }

    /*
     * 获取积分商品详情
     * @param goods_id 商品id
     * @param uid 用户id
     */
    public function integral_details()
    {

        $data = $this->request->request();
        !$data['uid'] && $this->error('请登录后操作');
        !$data['goods_id'] && $this->error('goods_id不存在');
        //获取详情信息
        $where = ['id' => $data['goods_id']];
        $field = 'img,integral,sales,title,content,inventory,add_time,goods_price';
        $integral_info = $this->integral->find_data($where,$field);
        if ($integral_info['inventory'] == 0){
            $this->error('库存不足');
        }
        $integral_info['img'] = $this->set_img($integral_info['img']);
        //获取商家地址
        $integral_info['address'] = config('shop_address');
        //运费暂设为0.00
        $integral_info['expressige'] = '0.00';
        //获取参数信息
        $integral_info['arr'] = db('item_arr')->field('name,value')->select();
        $integral_info = empty($integral_info) ? '' : $integral_info;
        $this->success('',['info' => $integral_info]);
    }

    /*
     * 积分明细
     * @param uid 用户id
     */
    public function score_log()
    {
        $data = $this->request->request();
        $integral = $this->user->where(['id' => $data['uid']])->value('score');
        $integral = empty($integral) ? 0 : $integral;
        $data = $this->request->request();
        !$data['uid'] && $this->error('请登录后操作');
        $score_log = $this->score_log->getScoreId($data['uid'],'score,memo,createtime','id desc');
        $score_log = empty($score_log) ? [] : $score_log;

        $this->success('',['list' => $score_log,'integral' => $integral]);
    }

    /*
     * 积分商品填写订单
     * @param goods_id 商品id
     * @ param uid 用户id
     * @param total_integral 实付积分
     * @param address 地址信息
     *
     */
    public function setIntegralInfo()
    {
        $data = $this->request->request();
        !$data['goods_id'] && $this->error('goods_id为空');

        //获取商品信息
        $where['id'] = $data['goods_id'];
        $field = 'title,img,integral,status';
        $item_info = $this->integral->find_data($where, $field);
        $item_info['img'] = $this->set_img($item_info['img']);

        //判断是否下架
        if ($item_info['status'] == 0 || empty($item_info)) {
            $this->error('该商品已下架');
        }
        //判断是否有默认地址
        $address_info = $this->address->get_default($data['uid'], 'address_id,name,phone,site');
        //判断用户是否有地址
        $address_where['isdefault'] = array('neq', '-1');
        $address_where['user_id'] = $data['uid'];
        $address_type = model('Litestoreaddress')->where($address_where)->value('address_id');
        if ($address_type) {
            $item_info['address_type'] = 1;
        } else {
            $item_info['address_type'] = 0;
        }
        $item_info['num'] = 1;
        $item_info['total_integral'] = $item_info['integral'] * $item_info['num'];
        $item_info['address'] = $address_info;
        $item_info['distributionfee'] = '0.00'; //暂时未做
        $item_info['distributionstyle'] = "快递配送"; //暂时未做
        $item_info['total_price'] = $item_info['total_integral'] + $item_info['distributionfee'];

        $this->success('', ['info' => $item_info]);


    }
    /*
     * 生成订单
     * @param goods_id 商品id
     * @param num 购买数量
     * @param remark 备注
     * @param address_id 地址id
     */
    public function getIntegralOrder()
    {
        $data = $this->request->request();
        !$data['goods_id'] && $this->error('商品信息不存在');
        $data['num'] = 1;
        if (!$data['address_id']){
            $this->error('地址信息不全');
        }

        $add_time = $_SERVER['REQUEST_TIME'];


        //获取商品信息并判断是否下架
        $item_info = $this->integral->find_data(['id' => $data['goods_id']], '*');
        if ($item_info['img']){
            $img = $this->set_img($item_info['img']);
        }

        if (empty($item_info) || $item_info['status'] != 1)
            $this->error('商品已下架');

        /*//判断用户输入密码是否正确
        $userinfo = $this->user->getUserInfo(['id' => $data['uid']],'pay_password');
        if ($data['pwd'] !=$userinfo['pay_password']){
            $this->error('输入密码错误');
        }*/
        //判断库存
        if ($item_info['inventory'] < $data['num'] || empty($item_info['inventory'])) {
            $this->error('库存不足');
        }
        //用户扣积分
        Db::startTrans();
        //判断用户积分是否不足
        $integral = db('User')->where(['id' => $data['uid']])->value('score');
        if ($integral < $item_info['integral'] || empty($integral)){
            $this->error('用户积分不足');
        }
        //用户减少积分
        $user_info = db('User')->where(['id' => $data['uid']])->setDec('score',$item_info['integral']);
        if (!$user_info){
            $this->error('用户信息错误');
        }
        $pay_integral = $item_info['integral'] * $data['num'];
        //生成积分记录
        $add_rr = [
            'user_id' => $data['uid'],
            'score' => $pay_integral,
            'before' => $integral,
            'after' => $integral - $pay_integral,
            'memo' => '购买商品消耗积分',
            'createtime' => time(),
        ];
        db('user_score_log')->insert($add_rr);
        //获取用户地址信息
        $address_info = $this->address->where(['address_id' => $data['address_id']])->field('name,phone,site')->find();

        if (empty($address_info)){
            $this->error('地址信息为空');
        }
        //订单信息
        $add_r = [
            'goods_id' => $data['goods_id'],
            'order_sn' => order_sn(2),
            'order_status' => 0,
            'image' => $img[0],
            'title' => $item_info['title'],
            'integral' => $item_info['integral'],
            'pay_integral' => $item_info['integral'] * $data['num'],
            'add_time' => $add_time,
            'receiver_name' => $address_info['name'],
            'receiver_phone' => $address_info['phone'],
            'receiver_site' => $address_info['site'],
            'uid' => $data['uid'],
            'remark' => $data['remark'],
            'distributionfee' => 0.00,
            'distributionstyle' => '快递配送',
        ];

        if (db('integral_order')->insert($add_r)){
            //减积分商品库存
            $this->integral->where(['id' => $data['goods_id']])->setDec('inventory');
            //增加销量
            $this->integral->where(['id' => $data['goods_id']])->setInc('sales',1);
            Db::commit();
            $this->success('生成订单成功');
        }else{
            Db::rollback();
            $this->error('生成订单失败');
        }
    }
    /*
         * 获取订单列表
         * @param page
         * @page pagesize
         * @param uid 用户id
         * @param order_status 0)待发货 1)待收货 2)已完成 7)全部
         */
    public function integral_order()
    {
        $data = $this->request->request();
        !$data['uid'] && $this->error('请登录后操作');
        //获取订单信息
//        $where['order_type'] = $data['order_type'];
        if ($data['order_status'] != 7)
            $where['order_status'] = $data['order_status'];
        $where['uid'] = $data['uid'];
        $where['is_del'] = '1';
        $field = 'id,order_sn,title,pay_integral,order_status,num,add_time,uid,image,goods_id';

        $list = $this->integral_order->select_page($where,$field,'add_time desc',$data['page'],$data['pagesize']);


        foreach ($list as $k => $v){
            $list[$k]['unit'] = '';
            $list[$k]['status'] = $this->get_order_status($v['order_status']);
        }
        $this->success('',['list' => $list]);
    }

    /**
     * 获取订单状态-中文  不是接口
     * @param order_status 0为待发货，1待收货，2为已完成
     */
    public function get_order_status($order_status)
    {
        switch ($order_status) {
            case 0:
                return '待发货';
            case 1:
                return '确认收货';
            case 2:
                return '交易成功';
        }
    }

    /*
     * 获取订单详情
     * @order_id  订单id
     *
     */
    public function integral_oreder_details()
    {
        $data = $this->request->request();
        empty($data['order_id']) && $this->error('order_id为空');
        //获取订单信息
        $where = ['id' => $data['order_id'],'is_del' => 1];
        $order_info = $this->integral_order->find_data($where,'*');
        $order_info['status'] = $this->get_order_status($order_info['order_status']);
        //商品规格（积分商城没有规格）
        $order_info['unit'] = '';
        $order_info = empty($order_info) ? []:$order_info;

        $this->success('',['info' => $order_info]);

    }

    /**
     * 确认收货
     * @param order_id
     */
    public function confirmOrder()
    {
        $data = $this->request->request();
        $where['uid'] = $data['uid'];
        $where['id'] = $data['order_id'];
        if ($this->integral_order->receipt($where)) {
            $this->success( '操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 轮播图
     */
    public function set_img($img)
    {
        $imgs = explode(',',$img);
        if ($imgs){
            foreach ($imgs as $k => $v){
                $img1[$k] = config('item_url').$v;
            }
        }
        return $img1;
    }

    /** 查看物流
     * @param order_id 订单id
     * @param shipper_code 快递公司编码
     * @param express_no 物流单号
     * @param express_company 快递公司
     * @param item_img 商品图片
     * @param type     商品类型 0)普通商品 1）积分商品
     */
    public function check_logistics()
    {

        $data = $this->request->request();
        !$data['order_id'] && $this->error('order_id不存在');
        //获取积分订单信息
        $order_info = $this->integral_order->find_data(['uid'=>$data['uid'],'id'=>$data['order_id']],'id,shipper_code,express_no,delivery_company,image,num');
        $express_model = controller('Express');
        $list = $express_model->getOrderTracesByJson($order_info);
    }

    /**
     * 物流公司
     */
    public function logistics(){
        $data = I('param.');
        $list = D('Logistics')->select_data(['status'=>1],'id,name','ordid desc',$data['page'],$data['pagesize']);
        $this->responseShow(200, '操作成功',['list'=>$list]);
    }
}