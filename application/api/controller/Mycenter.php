<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Usermoneylog;
use think\cache\driver\Redis;
use think\Db;
use fast\Random;
use think\Validate;
use app\common\library\Sms as Smslib;

class Mycenter extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->user = model('User');
        $this->config = model('Config');
        $this->block = model('Cmsblock');
        $this->article = model('Cmsarchives');
        $this->redis = new Redis();
    }

    /*
     * 用户个人资料显示
     * @param uid 用户id
     */

    public function userInfo()
    {

        $data['uid'] = $this->auth->id;
        !$data['uid'] && $this->error('请登录后操作');
        $where = ['id' => $data['uid']];
        $field = 'id,username,mobile,avatar,gender,birthday';
        $user_info = $this->user->getUserInfo($where, $field);
        $user_info['avatar'] = $this->user->getAvatar($data['uid']);
        //默认返回空数值
        $user_info['birthday'] = empty($user_info['birthday']) ? '' : $user_info['birthday'];
        $this->success('获取成功', $user_info);
    }


    /**
     * 个人中心我的设置
     */
    public function member_set()
    {

        $uid = $this->auth->id;
        !$uid && $this->error('请登录后操作');
        $condition['id'] = $uid;
        $field = 'password,pay_password';
        $info = $this->user->getUserInfo($condition, $field);

        $list['password'] = empty($info['password']) ? 0 : 1;
        $list['pay_password'] = empty($info['pay_password']) ? 0 : 1;
        //关于我们
        $article = $this->article->find_data(['id' => 30], 'content');
        $list['about_us'] = $article;
        $version = \db('Version')
            ->where(['status' => 'normal'])
            ->field('newversion,packagesize,content,enforce,downloadurl')
            ->order('id desc')->find();
        $version['downloadurl'] = $version['downloadurl'] ? config('item_url') . $version['downloadurl'] : 0;

        $this->success('获取成功', ['list' => $list, 'version' => $version]);
    }


    public function my_center()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;

//        if ($uid && $this->redis->has("user_info" . $uid)) {
//            $user_info = $this->redis->get("user_info" . $uid);
//        } else {
            //获取用户信息
            $user_info = $this->user->getUserInfo(['id' => $uid], 'id,avatar,nickname,vip_type,mobile,invitation_code,invite_num,distributor,expiration_time');
            //$user_info = $user_info ? $this->redis->set("user_info" . $uid, $user_info->toArray(), 3600) : '';
       // }
        $where = [
            'is_del' => 0,
            'user_id' => $this->auth->id,
            'order_type' => 10,
            'type' => 0,
        ];
        //我的中心功能类
        $block_where = ['cate_id' => 29, 'status' => 'normal', 'is_show' => 0];
        $functional_class = $this->block->select_data($block_where, 'id,name,image');
        if ($functional_class) {
            foreach ($functional_class as $module) {
                switch ($module['id']) {
                    case 71://套餐订单
                        $where['pay_status'] = 20;
                        $where['type'] = ['neq', 0];
                        $order_count = model('Litestoreorder')->where($where)->count();
                        break;
                    default:
                        $order_count = '';
                        break;
                }
                $module['badge'] = empty($order_count) ? '0' : $order_count;
            }
        }
        //获取我的订单模块
        $order_module = $this->block->select_data(['cate_id' => 27, 'status' => 'normal'], 'id,name,image');
        if ($order_module) {
            foreach ($order_module as $k => $v) {
                unset($where['pay_status']);
                //$where['type'] = 0; 订单状态:0=已取消,10=待付款,20=待发货，30待收货，40待评价，50交易完成 ,60 待分享
                $order_count = 0;
                switch ($v['id']) {
                    case 59:
                        $where['order_status'] = 10;
                        break;
                    case 60:
                        $where['order_status'] = 20;
                        break;
                    case 61:
                        $where['order_status'] = 30;
                        $where['order_type'] = 10;
                        $where['is_status'] = 1;
                        break;
                    case 62:
                        $where['order_status'] = 40;

                        break;
                    case 104:
                        $order_count = model('Litestoreorderrefund')->where(['uid' => $this->auth->id])->count();
                        break;
                }
                $order_count = $order_count >0 ? $order_count : model('Litestoreorder')->where($where)->count();

                $order_module[$k]['image'] = config('item_url') . $v['image'];
                $order_module[$k]['badge'] = empty($order_count) ? '0' : $order_count;
            }
        }

        //获取我的中心 我的服务
        $service_module = $this->block->select_data(['cate_id' => 26, 'status' => 'normal'], 'id,name,image' ,'weigh asc');
        if ($service_module) {
            foreach ($service_module as $module) {
                switch ($module['id']) {
                    case 71://套餐订单
                        $where['type'] = ['neq', 0];
                        $order_count = model('Litestoreorder')->where($where)->count();
                        break;
                    default:
                        $order_count = '';
                        break;
                }
                $module['badge'] = empty($order_count) ? '0' : $order_count;
            }
        }

        //获取客服号码
        $customer_tel = config('site.kf_phone');

        //获取邀请码和邀请链接
        $field = 'title,image,description,content';
        $content = $this->article->find_data(['id' => 31], $field);

        $content['image'] != null && $content['image'] = config('item_url') . $content['image'];

        //获取购物车数量
        $item_info = model('Shopingcart')->getShopingCartNum(['uid' => $uid]);
        //优惠券背景图
        $couponImage = $this->block->get('122')->image ? config('url_domain_root') . $this->block->get('122')->image : '';
        $password = $this->user->getUserInfo(['id' => $uid], 'pay_password');
        if (empty($password->pay_password)){$type=1;}else{$type=2;}
        $this->success('获取成功', [
            'password_type'=>$type,
            'info' => empty($user_info) ? '' : $user_info,
            'functional_class' => $functional_class,
            'order_module' => $order_module,
            'service_module' => $service_module,
            'customer_tel' => $customer_tel,
            'couponImage' => $couponImage,
            'invite' => ['content' => $content],
            'shopping_cart_num' => $item_info,
        ]);
    }

    /**
     * 我的中心 做了redis存储
     * @param integer type
     */
    public function my_center2()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;

        //获取用户信息
        $user_info = $uid && $this->redis->has("user_info" . $uid) ?$this->redis->get("user_info" . $uid)
            : $this->user->getUserInfo(['id' => $uid], 'id,avatar,username,vip_type,mobile,invitation_code,distributor,expiration_time');

        !$uid && !$this->redis->has("user_info" . $uid) && $user_info && $this->redis->set("user_info" . $uid, $user_info->toArray(), 3600);


        //redis 有直接取redis
        if ($this->redis->has('my_center')) {
            $my_center = $this->redis->get("my_center");

            $functional_class = $my_center['functional_class'];
            $order_module = $my_center['order_module'];
            if(count($functional_class) == 0 || count($order_module) == 0){
                $this->redis->rm('my_center');
                $this->my_center2();
            }

        } else {

            //我的中心功能类
            $block_where = ['cate_id' => 29, 'status' => 'normal', 'is_show' => 0];
            $functional_class = $this->block->select_data($block_where, 'id,name,image');

            //获取我的订单模块
            $order_module = $this->block->select_data(['cate_id' => 27, 'status' => 'normal'], 'id,name,image');

            //获取我的中心 我的服务
            $service_module = $this->block->select_data(['cate_id' => 26, 'status' => 'normal'], 'id,name,image');

            //获取客服号码
            $customer_tel = config('site.kf_phone');

            //获取邀请码和邀请链接
            $field = 'title,image,description,content';
            $content = $this->article->find_data(['id' => 31], $field);

            $content['image'] != null && $content['image'] = config('item_url') . $content['image'];

            //优惠券背景图
            $couponImage = $this->block->get('47')->image ? config('url_domain_root') . $this->block->get('47')->image : '';
        }

        //获取购物车数量
        $item_info = model('Shopingcart')->getShopingCartNum(['uid' => $uid]);

        $where = ['is_del' => 0, 'user_id' => $uid, 'order_type' => 10, 'type' => 0];

        //我的中心功能类
        if ($functional_class) {
            foreach ($functional_class as $k => $module) {
                if ($uid) {
                    switch ($module['id']) {
                        case 71://套餐订单
                            $where['pay_status'] = 20;
                            $where['type'] = ['neq', 0];

                            $key = "order_count" . $module['id'] . $uid;
                            $order_count = $this->redis->has($key) ? $order_count = $this->redis->get($key) :
                                model('Litestoreorder')->where($where)->count();

                            !$this->redis->has($key) && $this->redis->get($key, $order_count, 60);
                            break;
                        default:
                            $order_count = '0';
                            break;
                    }
                    $functional_class[$k]['badge'] = $order_count;
                } else
                    $functional_class[$k]['badge'] = '0';
            }
        }


        //获取我的订单模块
        if ($order_module) {
            $preg = "/^http(s)?:\\/\\/.+/";
            foreach ($order_module as $k => $v) {
                if (!$uid)
                    $order_count = '0';
                else {
                    unset($where['pay_status']);
                    $where['type'] = 0;
                    switch ($v['id']) {
                        case 61:
                            $where['is_status'] = 1;
                            $where['order_refund'] = 0;

                            $key = "order_count" . $v['id'] . $uid;
                            $order_count = $this->redis->has($key) ? $order_count = $this->redis->get($key) :
                                model('Litestoreorder')->where($where)->count();

                            !$this->redis->has($key) && $this->redis->get($key, $order_count, 60);

                            break;
                        case 62:
                            $where['is_status'] = 2;
                            $where['order_refund'] = 0;

                            $key = "order_count" . $v['id'] . $uid;
                            $order_count = $this->redis->has($key) ? $order_count = $this->redis->get($key) :
                                model('Litestoreorder')->where($where)->count();

                            !$this->redis->has($key) && $this->redis->get($key, $order_count, 60);

                            break;
                        case 104:
                            $key = "order_count" . $v['id'] . $uid;
                            $order_count = $this->redis->has($key) ? $order_count = $this->redis->get($key) :
                                model('Litestoreorderrefund')->where(['uid' => $uid])->count();

                            !$this->redis->has($key) && $this->redis->get($key, $order_count, 60);
                            break;
                        default:
                            $order_count = '';
                            break;
                    }
                }

                $order_module[$k]['image'] = preg_match($preg,$v['image']) ? $v['image']:config('item_url') . $v['image'];
                $order_module[$k]['badge'] = empty($order_count) ? '0' : $order_count;
            }
        }


        //存储redis
        if (!$this->redis->has('my_center')) {
            $my_center = [
                'functional_class' => $functional_class,
                'order_module' => $order_module,
                'service_module' => $service_module,
                'customer_tel' => $customer_tel,
                'couponImage' => $couponImage,
                'invite' => ['content' => $content],
                'shopping_cart_num' => $item_info,
            ];
            $this->redis->set('my_center', $my_center, 3600);
        } else {
            $my_center['functional_class'] = $functional_class;
            $my_center['order_module'] = $order_module;
            $my_center['shopping_cart_num'] = $item_info;
        }

        $my_center['info'] = empty($user_info) ? '' : $user_info;

        $this->success('获取成功', $my_center);
    }


    /**
     * @param url 二维码内参数
     * 生成二维码
     * @param $url 跳转地址
     * @param $type 1）小程序端 2）app端
     */
    public function create_code($url = '', $type, $goods_id, $uid)
    {
        Vendor('phpqrcode.phpqrcode');
        if (strpos($url, 'http') === false && $type == 1) {
            $url = 'http://' . $url;
        }

        $url = $type == 1 ? $url : json_encode(['goods_id' => $goods_id, 'invite_id' => $uid]);
        $path = "Uploads/QRcode/";//生成的二维码所在目录
        if (!file_exists($path)) {
            mkdir($path, 0700, true);
        }
        $time = time() . '.png';//生成的二维码文件名
        $fileName = $path . $time;//1.拼装生成的二维码文件路径
        \QRcode::png($url, $fileName, QR_ECLEVEL_L, 10, 3, false, 0xFFFFFF, 0x000000);
        return $fileName;

    }

    /**
     * 关于我们
     */
    public function getRule()
    {
        $data = $this->article->where(['id' => 44])->field('title,content')->find();
        $data ? $this->success('获取成功', $data) : $this->error('获取失败');
    }
    /**
     * 我的钱包
     */
    public function moneylist(){
        $params = $this->request->request();
        $type = $params['type']; //1 收入 2 支出
        $creatime=strtotime($params['createtime']);

        $uid = $this->auth->id;
        $money = \app\common\model\User::get($uid);

        $where = [];
        if($type==1){
            $where['type'] = '70';
        }else if($type==2){
            $where['type'] = ['<>',70];
        }
        $money_log=Usermoneylog::where(['user_id'=>$uid])->where($where)->select();
//        $money_log=[];
//        foreach ($creastime as $k =>$v){
//            strtotime(date("Y-m-d",$v['createtime']));
//
//            if ($creatime == $v['createtime']){
//                $money_log['']
//            }
//        }


        $list=[
            'money'=>$money->money,
            'money_log'=>$money_log,
        ];
        $this->success('获取成功', $list);
    }
    /**
     * 设置支付密码/修改密码
     */
    public function set_password(){
        $params = $this->request->request();
        $uid = $this->auth->id;
        $user_info = \app\common\model\User::get($uid);
        $pay_password=$user_info->pay_password;
        if (empty($params['newpassword'])){$this->error('密码不能为空');}
        if (!empty($pay_password)){
            if (empty($params['pay_password'])){$this->error('旧密码不能为空');}
            if ($pay_password != md5($params['pay_password'])){$this->error('旧密码不对');}
        }
        if (!is_numeric($params['newpassword'])){$this->error('不能是除数字以外的东西');}
        $user_info->pay_password=md5($params['newpassword']);
        if ($user_info->save()){
            $this->success('设置密码成功');
        }
    }
    public function zyPassword(){
        $params = $this->request->request();
        $uid = $this->auth->id;
        $user_info = \app\common\model\User::get($uid);
        if(md5($params['pay_password'])==$user_info->pay_password){
            $this->success('验证成功');
        }else{
            $this->error('验证失败');
        }
    }

    public function update_password()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        $user_info = \app\common\model\User::get($uid);
//        if ($user_info->mobile != $params['mobile']) {$this->error('手机号与信息手机号不符');}
//        if (empty($params['code'])) {$this->error('code不存在');}
//        if (empty($params['pay_password'])) {$this->error('pay_password不存在');}
//        if (!empty($params['pay_password']) && $params['pay_password'] == $params['pay_passwords']){
//        $check_code = \app\common\model\Sms::check_code($user_info->mobile, 6);
//        if ($check_code->code != $params['code']) {$this->error('code错误');}
        if (!is_numeric($params['pay_password'])){$this->error('密码只可以是数字');}
            $user_info->pay_password=md5($params['pay_password']);
            if ($user_info->save()){
                $this->success('设置新密码成功');
            }
        }
//    }



}