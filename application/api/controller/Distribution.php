<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\UserRebate;
use app\common\model\UserAgentApply;
use app\api\controller\View;
use app\common\model\User;
use fast\Http;


class Distribution extends Api
{
    protected $noNeedLogin = ['addCommission', 'rule_url', 'myInformation', 'invite','applyForAgentSave','applyForAgent'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {

        parent::_initialize();
        $this->user = model('User');
        $this->userRebate = model('UserRebateBack');
        $this->commission = model('Commission');
        $this->user_money_log = model('Usermoneylog');
        $this->article = model('Cmsarchives');
        $this->Rebate = model('UserRebate');
        $this->RuleList = model('RuleList');
        $this->withdraw = model('Withdraw');
        $this->user_agent_apply = model('Common/Useragentapply');

    }

    /**
     * 我的信息
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function myInfo()
    {
        $field = 'identity_front,identity_reverse,username,business_license,mobile,address,site,store_name,
        opening_hours,closing_hours,id_card';
        $where = ['uid' => $this->auth->id, 'store_status' => 1];
        $info = model('Useragentapply')->where($where)->field($field)->order('create_time desc')->find();
        $identity_front = explode(',', $info->identity_front);
        $images = [];
        foreach ($identity_front as $item) {
            $images[] = $item ? config('item_url') . $item : '';
        }
        $info->identity_front = $images;
        $info->identity_reverse = config('item_url') . $info->identity_reverse;
        $info->business_license = $info->business_license ? config('item_url') . $info->business_license : '';
        $this->success('获取成功', ['info' => $info]);
    }

    /*
     * 分销中心首页
     * @param user_id
     */
    public function center()
    {
        $where['id'] = $this->auth->id;
        $field = 'id,username,avatar,balance,vip_type,invite_num,subordinates_is_order,distributor';
        //获取用户信息
        $user_info = $this->user->getUserInfo($where, $field);
        $user_info['avatar'] = $this->user->getAvatar($this->auth->id);
        //已提现佣金
        $user_info['commission_withdrawn'] = $this->withdraw->where(['uid' => $this->auth->id, 'status' => 5])->sum('money');
        //总分销金额
        $user_info['generalCommission'] = $this->auth->total_balance;//总分销金额

        //获取商家id
        $model = new UserAgentApply();
        $store = $model->find_data(['uid' => $this->auth->id, 'store_status' => 1]);
        $user_info['store_id'] = $store ? $store->id : '';
        $info = $user_info ? $user_info : '';

        //邀请规则
        $content = $this->article->find_data(['id' => 31], 'title,image,description,content');
        $content['image'] != null && $content['image'] = config('item_url') . $content['image'];

        $this->success('获取成功', [
            'info' => $info,
            'invite' => $content,
            'info_status' => $store->id_card ? 1 : 0,
        ]);

    }

    /**
     * @throws \think\exception\DbException
     * 申请成为代理商
     *
     */
    public  function applyForAgent(){
        $where['id'] = $this->auth->id;
        $field = 'id,username,mobile';
        //获取用户信息
        $user_info = $this->user->getUserInfo($where, $field);
        !$user_info && $this->error('用户信息错误');
        $this->success('获取成功', [
            'user_info'=>$user_info,
        ]);
    }
    public function applyForAgentSave(){
        $params = $this->request->post();
        !$params['uid'] && $this->error('uid不能为空');
        !$params['username'] && $this->error('username不能为空');
        !$params['mobile']&& $this->error('mobile不能为空');
        !$params['address']&& $this->error('address不能为空');
//        !$params['opening_hours']&& $this->error('opening_hours不能为空');
//        !$params['closing_hours']&& $this->error('closing_hours不能为空');
//        !$params['store_name']&& $this->error('closing_hours不能为空');
        $agent = $this->user_agent_apply->find_data(['uid'=>$params['uid']]);
        $agent && $this->error('您已提交请,勿重复提交');
        $data=$this->user_agent_apply->save_data('',[
            'uid'  =>  $params['uid'],
            'username' =>  $params['username'],
            'mobile'=>$params['mobile'],
            'address'=>$params['address'],
//            'opening_hours'=>$params['opening_hours'],
//            'closing_hours'=>$params['closing_hours'],
//            'store_name'=>$params['store_name'],
            'create_time'=>time(),
            'store_status'=>0,
        ]);
        if ($data){
            $this->success('申请成功');
        }else{
            $this->success('申请失败');
        }
    }


    public function updateStore()
    {
        $params = $this->request->post();
        $agent = UserAgentApply::get($params['id']);
        !$agent && $this->error('代理商信息不存在');
        $agent->is_message = 1;
        if ($agent->allowField(true)->save($params)) {
            $this->success('修改成功');
        }
        $this->error('修改成功');
    }

    /*
     * 我的团队
     *  @param uid 用户id
     *  @param second_id //二级用户id
     */
    public function team()
    {
        $data = $this->request->request();
        $data['uid'] = $this->auth->id;
        $where['first_id'] = empty(input('second_id')) ? $data['uid'] : input('second_id');

        $rebate = new UserRebate();
        $team_id = $rebate->where($where)->column("uid");
        $user_where['id'] = array('in', $team_id);

        //获取下级用户信息
        $member_list = $this->user->select_data(['id' => $user_where['id']], 'id,avatar,username,mobile');
        $uid = $data['second_id'] ? $data['second_id'] : $data['uid'];
        $count = $rebate->where(['first_id' => $uid])->count() + $rebate->where(['second_id' => $uid])->count();

        if ($member_list) {
            $this->success('成功', ['list' => $member_list, 'count' => $count]);
        }
    }

    /**
     * 我的团队 新
     */
    public function newTeam()
    {
        $firstId = $this->request->param('first_id');
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('pagesize', 10);
        $uid = $firstId ? $firstId : $this->auth->id;
        $user = User::get($uid);
        $list = $this->Rebate->where('first_id', $uid)->page($page, $pageSize)->order('add_time desc')->with('info')->field('uid')->select();
        if ($list != null) {
            foreach ($list as $k => $v) {
                $sign = $v['vip_type'] > 0 ? ($v['distributor'] > 0 ? "代理商" : "VIP") : "";
                $list[$k]['nickname'] = $v['nickname'] . ' ' . $v['mobile'] . ' ' . $sign;
            }
        }

//        $count = $this->Rebate->where('first_id', $uid)->count();
        $this->success('获取成功', [
            'list' => $list,
            'count' => $user->invite_num,
        ]);
    }


    /*
     * 分销订单
     *@ param user_id  //用户id
     * @ item_id 商品id
     */
    public function rebateOrder()
    {
        $page = $this->request->param('page', 1);
        $pageSize = $this->request->param('page_size', 10);
        $field = 'id,uid,order_id,order_sn,create_time,money,act_pay_money,type,status';
        $where = [
            'superior_id' => $this->auth->id,
            'status' => ['neq', 3]
        ];
        $list = collection($this->userRebate->field($field)->page($page, $pageSize)->where($where)->order('create_time desc')
            ->with(['user', 'goods' => function ($query) {
                $query->field('order_id,goods_name,images,goods_price,total_num,total_price,is_refund');
            }])->select())->toArray();

        foreach ($list as $k => $order) {
            foreach ($order['goods'] as $v => $goods) {
                $list[$k]['goods'][$v]['images'] = $goods['images'] ? config('item_url') . $goods['images'] : '';
            }
        }
        $this->success('获取成功', ['list' => $list]);

    }

    /*
     * 提现明细
     */
    public function detailedCommission()
    {
        $page = $this->request->param('page');
        $pageSize = $this->request->param('page_size');
        $where = ['uid' => $this->auth->id];
        $field = 'price,add_time,type';
        $list = model('common/Commission')->select_page($where, $field, 'add_time desc', $page, $pageSize);
        $this->success('获取成功', ['list' => $list]);
    }

    /**
     * 分享有礼
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function invite()
    {
        $param = $this->request->request();
        $user = new User();
        //获取邀请的人数
        $subordinate = $this->Rebate->where(['first_id' => $this->auth->id])->count();
        //获取分销订单下单成功数量
        $field = 'id,pid,username';
        $list = $user->where('pid', $this->auth->id)->field($field)->withCount(['order' => function ($query) {
            $query->where('pay_status', '20');
        }])->select();
        $count = 0;
        foreach ($list as $item) {
            if ($item['order_count'] > 0) {
                $count = $count + 1;
            }
        }
//        $rebate = $this->userRebate->where(['superior_id' => $this->auth->id])->count();
//        $userList = $this->Rebate->where(['first_id' => $this->auth->id])->culmn('uid');
        //https://ggzp.0791jr.com/assets/img/share-bg-two.png
        $this->success('获取成功', [
            'subordinate' => $subordinate,
            'order' => $count,
            'share_background_picture' => config('url_domain_root') . Config('site.share_background_picture'),
            'share_poster' => config('url_domain_root') . Config('site.share_poster'),

        ]);
    }

    /**
     * 获取规则
     */
    public function rule()
    {
//        $param = $this->request->post();
//        !$param['id'] && $this->error('id不能为空');
        $data = $this->RuleList->where(['id' => 31])->field('title,content')->find();
        $data ? $this->success('获取成功', $data) : $this->error('获取失败');
    }

    /**
     * 获取分享海报
     */
    public function invitePoster()
    {
        //用户存在二维码则直接返回
        if ($this->auth->invite_qrcode) {
            $this->success('获取成功', config('url_domain_root') . $this->auth->invite_qrcode);
        }

        $qr_code = $this->GenerateQRcode();
        $this->success('获取成功', $qr_code);
    }


    /**
     * 不是接口  生成二维码 保存
     */
    public function GenerateQRcode()
    {

        //请求微信获取小程序二维码
        $params = json_encode(['scene' => $this->auth->invitation_code]);
        $result = Http::post('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . getAccessToken(), $params);

        //有返回errcode则表明请求失败
        $error = json_decode($result, true);
        if (!isset($error['errcode'])) {
            //判断保存路径是否存在
            $path = 'uploads/inviteQRCode/';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            //将二维码保存到本地
            $file = $path . time() . $this->auth->id . '.jpg';
            if (file_put_contents($file, $result)) {
                $user = $this->auth->getUser();
                $user->invite_qrcode = '/' . $file;
                $user->save();
                return config('url_domain_root') . '/' . $file;
            }
        }
        $this->error('获取二维码失败', $error);
    }


    /**
     * 我的资料
     */
    public function myInformation()
    {
        $model = new UserAgentApply();
        $storeId = $this->request->request('store_id');
        !$storeId && $this->error('store_id不存在');
        $field = 'id,identity_front,identity_reverse,username,mobile,address,business_license,id_card,opening_hours,closing_hours,site,store_name';
        $store_info = $model->find_data("id=$storeId", $field);


        $store_info['identity_front'] = empty($store_info['identity_front']) ? '' : $this->setCommaImages($store_info['identity_front']);
        $store_info['identity_reverse'] = empty($store_info['identity_reverse']) ? '' : config('item_url') . $store_info['identity_reverse'];
        $store_info['business_license'] = !$store_info['business_license'] ? '' : config('item_url') . $store_info['business_license'];
        $this->success('success', ['info' => $store_info]);

    }
}