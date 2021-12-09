<?php

namespace app\api\controller;

use app\admin\model\sign\Records;
use app\common\controller\Api;
//use Boris\Config;
use think\Db;
use Think\Config;

/**
 * 积分商城
 * Class Integral
 * @package app\api\controller
 */
class Integral extends Api
{
//    protected $noNeedLogin = ['get_order_status,logistics', 'integral', 'signIn', 'score_log', 'integral_details', 'getRule'];
    protected $noNeedRight = ['*'];

    protected $integral = null;
    protected $user = null;
    protected $score_log = null;
    protected $integral_order = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->integral = model('Integralitem');
        $this->user = model('User');
        $this->score_log = model('ScoreLog');
        $this->integral_order = model('Integralorder');
        $this->banner = model('Cmsblock');
        $this->address = model('Litestoreaddress');
        $this->archives = model('Cmsarchives');
    }

    /**
     * 积分商城首页
     */
    public function integral()
    {
        //用户积分
        $score = !$this->auth->id ? 0 : $this->auth->score;
//        $score = 10000;

        //积分商品列表
        $field = 'id,title,img,integral';
        $integral_list = $this->integral->select_page(['status' => 1], $field);

        $rule = $this->archives->find_one_data("id=43", 'content');
        $this->success('成功', ['user_score' => $score, 'integral_list' => $integral_list, 'rule' => $rule]);
    }

    /**
     * 获取积分规则
     */
    public function getRule()
    {
        $config = new \app\common\model\Config;
        $rule = $config->getConfigData(['name' => 'sign_rule']);

        $this->success('success', ['info' => $rule]);
    }

    /**
     * 签到
     */
    public function signIn()
    {
        //签到总次数
        $sign_sum = $this->auth->sign_sum;
        $uid = $this->auth->id;

        //判断是否开始签到
        $is_open_sign = model('Config')->where('name', 'eq', 'is_open_sign')->value('value');
        $is_open_sign != 1 && $this->error('积分签到未开启');

        //上次签到时间
        $last = $this->auth->last_sign_time;
        //今天凌晨时间
        $time = strtotime(date('Y-m-d'));
        //昨天凌晨时间
        $lastTime = strtotime('-1 day', $time);

        //签到时间 小于 今天凌晨时间，代表未签到
        if ($last < $time) {
            //积分
            $score = model('Config')->where('name', 'eq', 'sign_reward_default_day')->value('value');
            $memo = '签到送积分';

            $data = [
                'last_sign_time' => time(),
                'sign_sum' => $sign_sum + 1,
            ];

            Db::startTrans();
            //更新用户签到信息
            $edit_res = $this->user->edit_data(['id' => $uid], $data);

            empty($edit_res) && $this->error('签到失败');

            //积分变更
            $score_res = $this->user->score($score, $uid, $memo, 1)->toArray();
            if (empty($score_res)) {
                Db::rollback();
                $this->error('签到失败');
            }
            Records::create([
                'user_id' => $uid,
                'create_time' => time(),
                'ip' => $this->request->ip(),
                'credit' => $score,
                'username' => $this->auth->username,
            ]);
            Db::commit();
            $this->success('签到成功奖励' . $score . '积分', $score);
        }
        $this->error('已签到');
    }

    /**
     * 兑换记录
     */
    public function exchange_log()
    {
        $uid = $this->auth->id;
//        $uid = 16;
        $field = 'id,goods_id,pay_integral,add_time,num,image,pay_integral,title';
        $list = $this->integral_order->select_data(['uid' => $uid, 'is_del' => 1], $field)->toArray();

//        dump($this->integral_order);die;

        $this->success('成功', ['list' => $list]);
    }

    /**
     * 积分明细
     */
    public function score_log()
    {
        $uid = $this->auth->id;
        $type = $this->request->request('type');
//        $uid = 16;
        //获取用户积分
//        $user_score = $this->auth->score;
        //获取积分明细
        $field = 'id,score,memo,createtime,type';
        $score_log = $this->score_log->select_data(['user_id' => $uid,
            'type'=>$type,
            ], $field);

        $this->success('成功', ['list' => $score_log->toArray()]);
    }


    /**
     * 积分商品详情
     * @param goods_id 商品id
     * @param uid 用户id
     */
    public function integral_details()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id不存在');

        //获取详情信息
        $where = ['id' => $params['goods_id']];
        $field = 'id,title,content,intro,img,integral,inventory,status'; //参数信息包括在里面
        $integral_info = $this->integral->find_data($where, $field)->toArray();

        0 == $integral_info['inventory'] && $this->error('库存不足');

//        $integral_info['img'] = $this->set_img($integral_info['img']);
        //运费暂设为0.00
        $integral_info['expressige'] = '0.00';
        //获取参数信息
        $integral_info['arr'] = model('Itemattr')->select_data(['goods_id' => $params['goods_id'], 'type' => 2], 'name,value');

        $this->success('成功', ['info' => $integral_info]);
    }

    /**
     * 生成订单
     * @param goods_id 商品id
     * @param num 购买数量
     * @param remark 备注
     *
     */
    public function getIntegralOrder()
    {
        $data = $this->request->request();
        $data['uid'] = $this->auth->id;
        !$data['goods_id'] && $this->error('商品信息不存在');
        $data['num'] = 1;

        $add_time = $_SERVER['REQUEST_TIME'];

        //获取商品信息并判断是否下架
        $item_info = $this->integral->find_data(['id' => $data['goods_id']]);
        if ($item_info['img']) {
            $img = explode(',', $item_info['img']);
        }
//        dump( $item_info['img'][0]);
//        die;
        if (empty($item_info) || $item_info['status'] != 1)
            $this->error('商品已下架');

        //判断用户输入密码是否正确
//        $userinfo = $this->user->getUserInfo(['id' => $data['uid']], 'pay_password');
//        if (md5($data['pwd']) != $userinfo['pay_password']) {
//            $this->error('输入密码错误');
//        }
        //判断库存
        if ($item_info['inventory'] < $data['num'] || empty($item_info['inventory'])) {
            $this->error('库存不足');
        }
        //用户扣积分
        Db::startTrans();
        //判断用户积分是否不足
        $integral = $this->user->getField(['id' => $data['uid']], 'score');
        if ($integral < $item_info['integral'] || empty($integral)) {
            $this->error('用户积分不足');
        }
        //消耗积分数量
        $pay_integral = $item_info['integral'] * $data['num'];

        //用户减少积分
        $user_score = $this->user->score($pay_integral, $data['uid'], '积分兑换商品', 0);
//        //获取用户地址信息
//        $address_info = $this->address->find_data(['address_id' => $data['address_id']], 'address_id,name,phone,site');
//        if (empty($address_info)) {
//            $this->error('地址信息为空');
//        }
        //订单信息
        $add_r = [
            'goods_id' => $data['goods_id'],
            'order_sn' => order_sn(2),
            'order_status' => 0,
            'image' => $item_info['img'][0],
            'title' => $item_info['title'],
            'integral' => $item_info['integral'],
            'pay_integral' => $item_info['integral'] * $data['num'],
            'add_time' => $add_time,
//            'receiver_name' => $address_info['name'],
//            'receiver_phone' => $address_info['phone'],
//            'receiver_site' => $address_info['site'],
            'uid' => $data['uid'],
            'remark' => $data['remark'],
            'distributionfee' => 0.00,
            'distributionstyle' => '自取',
        ];

        if ($this->integral_order->insert($add_r) && $user_score) {
            //减积分商品库存
            $this->integral->where(['id' => $data['goods_id']])->setDec('inventory');
            //增加销量
            $this->integral->where(['id' => $data['goods_id']])->setInc('sales', 1);
            Db::commit();
            $this->success('生成订单成功');
        } else {
            Db::rollback();
            $this->error('生成订单失败');
        }
    }

    /*
     * 积分商品填写订单
     * @param goods_id 商品id
     * @ param uid 用户id
     * @param total_integral 实付积分
     * @param address 地址信息
     * @param $address_type 1)有地址 2)无地址
     *
     */
    public function setIntegralInfo()
    {
        $data = $this->request->request();
        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
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
        $item_info['address_type'] = !empty($address_type) ? 1 : 0;
        $item_info['num'] = 1;
        $item_info['total_integral'] = $item_info['integral'] * $item_info['num'];
        $item_info['address'] = $address_info;
        $item_info['distributionfee'] = '0.00'; //暂时未做
        $item_info['distributionstyle'] = "快递配送"; //暂时未做
        $item_info['total_price'] = $item_info['total_integral'] + $item_info['distributionfee'];//总支付价格
        //判断用户是否设置支付密码
        $item_info['pay_status'] = collection($this->user->getField(['id' => $data['uid']], 'pay_password'))->isEmpty() ? 0 : 1; //0未设置密码 1)已设置密码
        $this->success('成功', ['info' => $item_info]);
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
        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
        //获取订单信息
//        $where['order_type'] = $data['order_type'];
        if ($data['order_status'] != 7) {
            $where['order_status'] = $data['order_status'];
        }
        $where['uid'] = $data['uid'];
        $where['is_del'] = '1';
        $field = 'id,order_sn,title,pay_integral,order_status,num,add_time,uid,image,goods_id';
        $list = $this->integral_order->select_page($where, $field, 'add_time desc', $data['page'], $data['pagesize']);

        foreach ($list as $k => $v) {
            $list[$k]['unit'] = '';
            $list[$k]['status'] = $this->get_order_status($v['order_status']);
            $list[$k]['image'] = config('item_url') . $v['image'];
        }
        $this->success('', ['list' => $list]);
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
                return '待收货';
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
        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
        empty($data['order_id']) && $this->error('order_id为空');

        //判断推送表中是否已读
        $jpush_where = ['type' => 3, 'order_id' => $data['order_id']];
        $is_look = model('Jpushlog')->where($jpush_where)->value('is_look');
        if ($is_look == 1) {
            //修改成为已读
            if (!model('Jpushlog')->update_data($jpush_where, ['is_look' => 2])) {
                $this->error('修改已读失败');
            }
        }
        //获取订单信息
        $where = ['id' => $data['order_id'], 'is_del' => 1];
        $order_info = $this->integral_order->find_data($where);
        $order_info['status'] = $this->get_order_status($order_info['order_status']);
        //商品规格（积分商城没有规格）
        $order_info['unit'] = '';
        $order_info['image'] = config('item_url') . $order_info['image'];
        $order_info = empty($order_info) ? [] : $order_info;

        $this->success('', ['info' => $order_info]);

    }

    /**
     * 确认收货
     * @param order_id
     */
    public function confirmOrder()
    {
        $data = $this->request->request();
        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
        $where['uid'] = $data['uid'];
        $where['id'] = $data['order_id'];
        if ($this->integral_order->receipt($where)) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 轮播图
     */
    public function set_img($img)
    {
        $imgs = explode(',', $img);
        if ($imgs) {
            foreach ($imgs as $k => $v) {
                $img1[$k] = config('item_url') . $v;
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
        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
        !$data['order_id'] && $this->error('order_id不存在');
        //获取积分订单信息
        $field = 'id,shipper_code,express_no,delivery_company,image,num';
        $order_info = $this->integral_order->find_data(['uid' => $data['uid'], 'id' => $data['order_id']], $field);
        $order_info['image'] = config('item_url') . $order_info['image'];
        //查看物流
        $express_model = controller('Express');
        $express_model->getOrderTracesByJson($order_info['shipper_code'], $order_info['express_no'], $order_info['delivery_company'], $order_info['image']);
    }

}