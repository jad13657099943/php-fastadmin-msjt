<?php

namespace app\api\controller;

use addons\litestore\model\Litestoreorder;
use app\admin\model\cms\Page;
use app\common\controller\Api;
use app\common\model\Limitdiscount;
use think\Db;


/**
 * 营销活动控制器
 */
class Marketing extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->item = model('Litestoregoods');
        $this->limit_discount_model = model('Limitdiscount');
        $this->limit_discount_goods_model = model('Limitdiscountgoods');

        $this->groupbuy_model = model('Groupbuy');
        $this->groupbuy_goods_model = model('Groupbuygoods');
        $this->join_groupbuy_model = model('Joingroupbuy');

        $this->cut_down_model = model('Cutdown');
        $this->cut_down_goods_model = model('Cutdowngoods');
        $this->cut_down_record_model = model('Cutdownrecord');
        $this->item_spec = model('Litestoregoodsspec');
        $this->domain = config('url_domain_root');
    }


    /**
     * 限时抢购活动
     * @param cate_id 活动分类id
     * */
    public function getLimitDiscountGoods()
    {
        $params = $this->request->request();
        !$params['page'] && $this->error('page参数不能为空');
        !$params['pagesize'] && $this->error('pagesize参数不能为空');
        //获取轮播图
//        $ad = model('Cmsblock')->find_field_data(['status' => 'normal', 'id' => $params['cate_id']], 'images,url,id');
        //$ad = $this->setPlitJointImages($ad['images']);

        //获取产品信息
        $where = [
            'status'=> 10,
            'start_time' => ['lt', time()],
            'end_time' => ['gt', time()]
        ];
        $field = 'id acticity_id,limit_discount_id,goods_id,goods_price,line_price,start_time,
                   end_time,status,goods_name,image,stock_num,sales,total_stock';
        $list = $this->limit_discount_goods_model->select_page_data($where, $field, $params['page'], $params['pagesize']);

        if ($list != null) {
            foreach ($list as $k => $v) {
                $list[$k]['type'] = time() <= $v['start_time'] ? 1 : 2;  //1)未开始 2)进行中
                $list[$k]['miao'] = $list[$k]['type'] == 1 ? $v['start_time'] - time() : $v['end_time'] - time();
                $sales_initial = model('Litestoregoods')->where('goods_id',$v['goods_id'])->value('sales_initial');
                $list[$k]['sales'] = $v['sales'] + $sales_initial;
                $list[$k]['image'] = config('item_url') . $v['image'];
                $percentage = $v['sales'] == 0 ? 0 : round($v['sales'] / ($v['total_stock'] + $sales_initial), 2);
                $list[$k]['percentage'] = $percentage;
                if ($list[$k]['percentage'] >= 1 && $v['sales'] != 0) {
                    $list[$k]['type'] = 3;
                }
            }
        }
        $this->success('获取成功', ['list' => $list]);
    }

    /**
     * 限时抢购活动
     * @param cate_id 活动分类id
     * */
    public function getLimitDiscountGoodsT()
    {
        $params = $this->request->request();
        !$params['page'] && $this->error('page参数不能为空');
        !$params['pagesize'] && $this->error('pagesize参数不能为空');
        //获取轮播图
//        $ad = model('Cmsblock')->find_field_data(['status' => 'normal', 'id' => $params['cate_id']], 'images,url,id');
        //$ad = $this->setPlitJointImages($ad['images']);

        //获取产品信息
        $where = [
            'status'=> 10,
            'start_time' => ['lt', time()],
            'end_time' => ['gt', time()]
        ];
        $field = 'id acticity_id,limit_discount_id,goods_id,goods_price,line_price,start_time,
                   end_time,status,goods_name,image,stock_num,sales,total_stock';
        $list = $this->limit_discount_goods_model->select_page_data($where, $field, $params['page'], $params['pagesize']);

        if ($list != null) {
            foreach ($list as $k => $v) {
                $sales = $v['sales'];
                $list[$k]['type'] = time() <= $v['start_time'] ? 1 : 2;  //1)未开始 2)进行中
                $list[$k]['miao'] = $list[$k]['type'] == 1 ? $v['start_time'] - time() : $v['end_time'] - time();
                $sales_initial = model('Litestoregoods')->where('goods_id',$v['goods_id'])->value('sales_initial');
                $list[$k]['sales'] = $v['sales'] + $sales_initial;
                $list[$k]['image'] = config('item_url') . $v['image'];
                dump($list[$k]['sales']);dump($v['total_stock'] + $sales_initial);dump($v['sales'] / ($v['total_stock'] + $sales_initial));die;
                $percentage = $v['sales'] == 0 ? 0 : round($sales / $v['total_stock'], 2);
                $list[$k]['percentage'] = $percentage;
                if ($list[$k]['percentage'] == 1 && $v['sales'] != 0) {
                    $list[$k]['type'] = 3;
                }
            }
        }
        $this->success('获取成功', ['list' => $list]);
    }


    /**
     * 限时抢购商品详情
     * @param limit_discount_id 限时抢购活动id
     * @param goods_id 商品id
     */
    public function getLimitDiscountDetail()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id为空');
        !$params['limit_discount_id'] && $this->error('limit_discount_id为空');
        $uid = $this->auth->id;
        //获取商品详情
        $model = new Item();
        $itemdetail = $model->ItemDetail($params['goods_id'], $uid);
        $info = $this->getActivityInfo($itemdetail, $params['goods_id'], 1, $params['limit_discount_id'], $uid);


        $orders =new Litestoreorder();
        $huodong =new Limitdiscount();

        $activity_id = $orders->where('user_id',$uid)->where('order_status','neq',0)->field('activity_id')->select();

        foreach ($activity_id as $k=>$v){
            $data22[] =  $huodong->where('id',$v['activity_id'])->where('status',1)->find();
        }
        $data1 = implode('',$data22);

        if (!empty($data1)){
            $order_type = 0;
        }else{
            $order_type = 1;
        }
        $info['order_type']=$order_type;

        $this->success('获取成功', $info);
    }


    /**
     * 2人团购 商品列表
     * @param cate_id 活动分类id
     * */
    public function getGroupBuyGoods()
    {
        $params = $this->request->request();
        //获取轮播图
//        $ad = model('Cmsblock')->find_field_data(['status' => 'normal', 'id' => $params['cate_id']], 'images,url,id');

        $ad = model('Cmsblock')->find_data(['status' => 'normal', 'id' => 106], 'image,id');
        $ad['image'] = empty($ad['image']) ? '' : $this->domain . $ad['image'];
        //获取产品信息
        $field = 'id,groupbuy_id,goods_id,goods_price,line_price,group_num,group_nums,status,goods_name,image,stock_num';
        $list = $this->groupbuy_goods_model->select_page_data([], $field, $params['page'], $params['pagesize']);
        $this->success('获取成功', ['ad' => $ad , 'list' => $list]);
    }


    /**
     * 拼团商品详情
     * @param $goods_id
     * @param $groupbuy_id
     */
    public function getGroupBuyDetail()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id为空');
        !$params['id'] && $this->error('id为空');
        $uid = $this->auth->id;

        //获取商品详情
        $model = new Item();
        $itemdetail = $model->ItemDetail($params['goods_id'], $uid);

        $info = $this->getActivityInfo($itemdetail, $params['goods_id'], 2, $params['id']);
        //开团人数
        $where = ['type' => 1, 'goods_id' => $params['goods_id'], 'status' => 1];
        $info['groupbuy_num'] = $this->join_groupbuy_model->getGroupbuyNum($where);
        if (input('invite_id')) {
            //获取group_id
            $where['uid'] = input('invite_id');
            //获取拼团信息
            $join_group_info = $this->join_groupbuy_model->find_data($where, 'id,pid,status');
            $group_id = $join_group_info['status'] == 2 ? $join_group_info['pid'] : $join_group_info['id'];
            $info['details_type'] = 1; //他人复制口令进来
            $uuid = input('invite_id');
        } else {
            $info['details_type'] = 0;//自己复制口令进来
            $uuid = $uid;
        }
        $info['group_id'] = $group_id ? $group_id : 0;
        //获取团购商品倒计时
        $info['miao'] = $this->join_groupbuy_model->getMiao(['uid' => $uuid, 'groupbuy_id' => $params['groupbuy_id']
            , 'status' => 1, 'type' => 1]);
        $list = [];
        //开团列表
        if ($info['groupbuy_num'] > 0) {
            //获取开团列表 2条数据
            $field = 'avatar,add_time,group_num,join_num,hour,name,uid,id';
            unset($where['uid']);
            $list = $this->join_groupbuy_model->getPageList($where, $field, 'id desc', 1, 2, $uid);
        }

        //判断用户是否参加拼团
        $join_info = $this->join_groupbuy_model->find_data(['uid' => $uid, 'goods_id' => $params['goods_id'], 'status' => 1, 'type' => 1],'id,order_id');
        if ($join_info) {
            $info['assemble_status'] = 1;
            //已发起拼团传order_id
            $info['order_id'] = $join_info['order_id'];
            $info['order_no'] = model('common/Litestoreorder')->get($join_info['order_id'])->order_no;
        } else {
            $info['assemble_status'] = 0;
        }
        $this->success('获取成功', ['info' => $info, 'list' => $list]);
    }


    /**
     *拼团中 邀请好友
     * @param
     * groupbuy_status 1）拼团成功 0）拼团中
     */
    public function groupbuying()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
//        !$uid && $this->error('请登录后操作');
        !$params['activity_id'] && $this->error('activity_id为空');
        !$params['goods_id'] && $this->error('goods_id为空');

        //获取订单id
            $order_id = empty($params['order_id']) ? model('Litestoreorder')->getField(['order_no' => $params['order_no']], 'id') : $params['order_id'];
        !$order_id && $this->error('订单id不存在');

        //获取拼团记录pid
        $pid = $this->join_groupbuy_model->where(['order_id' => $order_id])->value('pid');
        //获取拼团商品拼团要求
        $group_where = ['goods_id' => $params['goods_id'], 'id' => $params['activity_id']];
        $field = 'id,group_num,hour,groupbuy_id,goods_id,group_num';
        $groupbuy_goods_info = $this->groupbuy_goods_model->find_data($group_where, $field);

        $where = [
            'order_id' => $order_id,
            'user_id' => empty($params['invite_id']) ? $uid : $params['invite_id'],
        ];
        //获取拼团商品信息
        $order_goods_model = model('Litestoreordergoods');
        $info = $order_goods_model->find_data($where, 'id,images,goods_name,goods_price,total_num,line_price,key_name,createtime,goods_id');

        $info['image'] = config('item_url') . $info['images'];
        $info['hour'] = $groupbuy_goods_info['hour'];
        $info['group_num'] = $groupbuy_goods_info['group_num'];
        unset($info['images']);

        //开团人数
        $count_where = ['type' => ['neq', 0], 'pid' => $pid];
        $groupbuy_num = $this->join_groupbuy_model->getGroupbuyNum($count_where);

        if ($groupbuy_num >= $groupbuy_goods_info['group_num']) {
            $groupbuy_status = 1; //人数拼满
        } else {
            $groupbuy_status = 0; //人数未满
        }
        //获取开团列表 2条数据
        $field = 'id ,avatar , group_num , join_num , uid';
        $list = $this->join_groupbuy_model->select_page($count_where, $field, 'add_time aec', 1, 2);

        //获取猜你喜欢
        $goods_model = model('Litestoregoods');
        $goods_list = $goods_model->like_goods($uid);

        //获取倒计时
        $info['miao'] = $this->join_groupbuy_model->getMiao(['order_id' => $order_id]);
        if ($info['miao'] == 0 && $groupbuy_num < $groupbuy_goods_info['group_num']) {
            //活动已到期
            $groupbuy_status = 2;
        }

        if ($params['invite_id'] && $uid != $params['invite_id']) {
            $invite_id = $params['invite_id'];//分享用户id
            $info['details_type'] = 1;//是否是开团人视角 0)是 1)不是
        } else {
            $info['details_type'] = 0;
        }
        $info['invite_id'] = empty($invite_id) ? '' : $invite_id;
        if ($groupbuy_status == 1 && $info['details_type'] == 1) {
            $groupbuy_status = 3; //拼团成功他人视角状态
        }
        //获取分享进入商品详情的字段 goods_id 、groupbuy_goods_id
        $info['groupbuy_goods_id'] = $groupbuy_goods_info['id'];
        if ($info) {
            $info['pid'] = $pid ? $pid : '';
            $this->success('成功', ['info' => $info, 'list' => $list, 'like_list' => $goods_list, 'status' => $groupbuy_status]);
        }
    }


    /**
     * 开团人列表
     * @param $groupbuy_id
     * @param page
     * @param pagesize
     * */
    public function getJoinGroupbuyList()
    {

        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id为空');
        !$params['activity_id'] && $this->error('activity_id为空');
//        $uid = $this->auth->id;

        $where = ['groupbuy_id' => $params['activity_id'], 'status' => 1, 'type' => 1, 'goods_id' => $params['goods_id']];
        $field = 'id,avatar,add_time,group_num,join_num,hour,name,uid';
        $list = $this->join_groupbuy_model->getPageList($where, $field, 'id desc', $params['page'], $params['pagesize'], $this->auth->id);

        $this->success('获取成功', ['list' => $list]);
    }


    /**
     * 添加团购信息
     * @param $uid 用户id
     * @param $goods_id 商品id
     * @param $activity_id 团购活动id
     * @param $group_id    开团列表id
     * @param $order_id    订单id
     */
    public function addJoinGroupbuy($uid, $goods_id, $activity_id, $order_id, $group_id = '')
    {
        $where = ['goods_id' => $goods_id, 'id' => $activity_id];

        $field = 'group_num , hour';
        $groupbuy_goods_info = $this->groupbuy_goods_model->find_data($where, $field);

        $user_info = model('User')->find_data(['id' => $uid], 'avatar,username');

        $add = [
            'groupbuy_id' => $activity_id,
            'goods_id' => $goods_id,
            'add_time' => time(),
            'status' => $group_id ? 2 : 1,//是否是团长 1）是  2)不是
            'avatar' => $user_info['avatar'],
            'order_id' => $order_id,
            'pid' => $group_id ? $group_id : 0,
            'group_num' => $groupbuy_goods_info['group_num'],
            'hour' => $groupbuy_goods_info['hour'],
            'uid' => $uid,
            'join_num' => 1,
            'name' => $user_info['username'],
        ];

        $add_join_groupbuy = $this->join_groupbuy_model->add_data($add);

        // 拼团 添加拼团人数
        return $group_id ? true : $this->join_groupbuy_model->update_data(['id' => $add_join_groupbuy], ['pid' => $add_join_groupbuy]);
    }


    /**
     * 获取商品参数
     * @param $item_info 商品信息集合
     * @param $status 1)限时抢购  2)2人团购  3）今日特价
     * @param $activity_id 活动id
     * @param $goods_id 商品id
     */
    public function getActivityInfo($item_info, $goods_id, $status, $activity_id, $uid = '')
    {
        switch ($status) {
            case 1://限时抢购
//                dump($goods_id);dump($activity_id);die;
                $activity = $this->limit_discount_goods_model->find_data(['goods_id' => $goods_id, 'limit_discount_id' => $activity_id], 'id,start_time ,end_time , stock_num , sales , upper_num ,total_stock');
                $item_info['type'] = time() <= $activity['start_time'] ? 1 : 2;  //1)未开始 2)进行中
                $item_info['miao'] = $item_info['type'] == 1 ? $activity['start_time'] - time() : $activity['end_time'] - time();
                $item_info['activity_id'] = $activity['id'];
                $item_info['miao'] = $item_info['miao'] <= 0 ? 0 : $item_info['miao'];
                $item_info['stock_num'] = $activity['stock_num'];
                $item_info['sales_initial'] = $item_info['sales_initial'] + $activity['sales'];
                $percentage = $activity['sales'] == 0 ? 0 : round($activity['sales'] / $activity['total_stock'], 2);
                if ($percentage == 1) {
                    $item_info['type'] = 3;//商品已抢完
                }
                break;

            case 2: //2人团购

                $activity = $this->groupbuy_goods_model->find_data(['goods_id' => $goods_id, 'id' => $activity_id], 'id, stock_num, group_nums ,group_num , upper_num');
                $item_info['group_nums'] = $activity['group_nums'];
                $item_info['activity_id'] = $activity['id'];
                $item_info['group_num'] = $activity['group_num'];
                $item_info['stock_num'] = $activity['stock_num'];
                //团购规则
                $this->archives = model('Cmsarchives');
                $info = $this->archives->find_data(['id' => 20], 'content');
                $item_info['rule'] = $info['content'];
                break;
        }
        $item_info['upper_num'] = $activity['upper_num'] > 0 ? $activity['upper_num'] : 0;
        return $item_info;
    }

    /**
     * 获取今日砍价商品列表
     * @param cate_id
     *
     */
    public function getCutDownGoodsList()
    {
        $params = $this->request->request();
        //获取轮播图
        $ad = model('Cmsblock')->find_field_data(['status' => 'normal', 'id' => $params['cate_id']], 'images,url,id');

        //获取产品信息
        $where = [
            'start_time' => ['lt', time()],
            'status' => 1,
            'end_time' => ['gt', time()]
        ];

        $field = 'id,cut_down_id,goods_id,line_price,discount_price,start_time,status,
                   end_time,goods_name,image,number';
        $list = $this->cut_down_goods_model->select_page_data($where, $field, $params['page'], $params['pagesize']);

        if ($list != null) {
            foreach ($list as $k => $v) {
                $list[$k]['miao'] = ($v['end_time'] - time()) > 0 ? $v['end_time'] - time() : 0;
                $list[$k]['image'] = config('qiniu_url') . $v['image'];
            }
        }
        $this->success('获取成功', ['ad' => $ad, 'list' => $list]);
    }


    /**
     * 砍价商品详情
     * @param cut_down_id 今日砍价活动id
     * @param goods_id 商品id
     * @param sponsor_id 发布人id
     */
    public function getCutDownDetail()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id为空');
        !$params['cut_down_goods_id'] && $this->error('cut_down_goods_id为空');
        $uid = $this->auth->id;

        //获取发布人id
        if (input('sponsor_id') && input('sponsor_id') != $uid) {
            $sponsor_id = $params['sponsor_id'];
            $id_type = 1;
        } else {
            $sponsor_id = $uid;
            $id_type = 2;
        }
        //获取商品详情
        $model = new Item();
        $itemdetail = $model->ItemDetail($params['goods_id'], $uid);
        $info = $this->getActivityInfo($itemdetail, $params['goods_id'], 3, $params['cut_down_goods_id'])->getData();
        $cut_down_list = $this->bargaining_info($uid, $params['cut_down_goods_id'], $sponsor_id, $id_type);


        if ($info) {
            $user = controller('User');
            $cmmand = $user->addCmmand($uid, $params['cut_down_goods_id'], $params['goods_id'], 2);
            if ($cmmand) {
                $info['cmmand'] = $this->getShareInfo($info['goods_name'], $cmmand);
            }

        }
        if ($cut_down_list['info']) {
            $info = array_merge($info, $cut_down_list['info']);
        }

        if ($cut_down_list['percentage'] == 1) {

            $cut_down_list['status'] = 1; //砍价结束
        } else {
            $cut_down_list['status'] = 0; //砍价未完成
        }

        /*$where = ['sponsor_id' => $sponsor_id, 'cut_down_goods_id' => $params['cut_down_goods_id'], 'type' => 1];
        $info['cut_down_status'] = $this->cut_down_record_model->where($where)->value('status');*/
        $info['cut_down_status'] = $cut_down_list['status'];
        //获取发起人的信息
        $info['list'] = $cut_down_list['list'];
        $info['details_type'] = $cut_down_list['details_type'];
        $info['percentage'] = $cut_down_list['percentage'];
        $info['details_status'] = $cut_down_list['status'];//0) 进行中 1）已完成 3）已过期
        $this->success('获取成功', $info);
    }

    /**
     * 砍价流程接口
     * @param additional_id 分享进来详情的分享人id;
     *
     */
    public function bargaining_info($uid, $id, $sponsor_id, $id_type)
    {

        //获取商品详情信息
        $field = 'id,discount_price,goods_price,line_price,min_price';
        $cut_down_goods = $this->cut_down_goods_model->find_data(['id' => $id], $field)->getData();
        $status = '';
        //判断是否是自己进入详情
        if ($id_type == 1) {
            $cut_down_list = $this->bargaining_process($id, $sponsor_id);
            //判断他人是否帮忙砍价
            $where = array(
                'uid' => $uid,
                'sponsor_id' => $sponsor_id,
                'cut_down_goods_id' => $id,
                'type' => 0,
            );
            $type = 2;//他人通过分享进入详情
            $status = $this->cut_down_record_model->where($where)->count() != 0 ? 1 : 0; //status=0 别人为帮忙砍价  status = 1 别人帮忙砍价
        } else {
            //判断该商品自己是否发起砍价
            $where = array(
                'uid' => $uid,
                'cut_down_goods_id' => $id,
                'type' => 1,
                'status' => 0,
            );

            if ($this->cut_down_record_model->where($where)->count() != 0) {
                $cut_down_list = $this->bargaining_process($id, $uid);
                $type = 1; //自己进去详情（已发起砍价）
            } else {
                $type = 0;//自己进去详情（未发起砍价）
                $cut_down_list = $this->bargaining_process($id, $uid);
            }
        }
        $cut_down_info = $this->cut_down_record_model->find_data(['cut_down_goods_id' => $id, 'type' => 1, 'sponsor_id' => $sponsor_id], 'username , avatar , sponsor_id');
        //获取已砍价总额
        $cut_down_info['cut_prices'] = $this->cut_down_record_model->where(['cut_down_goods_id' => $id, 'sponsor_id' => $sponsor_id, 'status' => 0])->sum('cut_price');
        //$cut_down_info['cut_prices'] = $cut_down_info['cut_prices'] >= ($cut_down_goods['goods_price'] - $cut_down_goods['discount_price']) ? $cut_down_goods['goods_price'] : $cut_down_info['cut_prices'];
        //砍价人数
        $cut_down_info['cut_down_num'] = $this->cut_down_record_model->getCutDownNum(['cut_down_goods_id' => $id, 'sponsor_id' => $sponsor_id, 'status' => 0]);
        //砍价进度
        $cut_down_info['minimum_price_status'] = 0;
        if ($cut_down_goods['discount_price'] < $cut_down_goods['min_price']) {
            $cut_down_goods['discount_price'] = $cut_down_goods['min_price'];
            $cut_down_info['minimum_price_status'] = 1; //最低价状态
        }

        $percentage = $cut_down_info['cut_prices'] / ($cut_down_goods['goods_price'] - $cut_down_goods['discount_price']);

        $cut_down_percentage = $percentage < 0 ? 0 : $percentage;
        $cut_down_percentage = $cut_down_percentage > 1 ? 1 : $cut_down_percentage;
        //判断砍价是否成功 （成功获取支付信息） 暂未做
        if ($type == 0) {
            $cut_down_list = [];
            $cut_down_info = '';
            $cut_down_percentage = '';
        }
        return [
            'list' => $cut_down_list,
            'info' => empty($cut_down_info) ? '' : $cut_down_info->getData(),
            'details_type' => $type, // 1)自己发起砍价 0）自己未发起砍价 2）他人通过分享进入详情
            'percentage' => $cut_down_percentage,
            'status' => $status,
        ];
    }

    public function test()
    {
        echo $this->randomFloat();
        exit;
    }


    /**
     * 发起砍价活动
     * @param $cut_down_goods_id
     * @param $goods_id
     */
    public function setCutDown()
    {
        $params = $this->request->request();
        unset($params['s']);
        !$params['goods_id'] && $this->error('goods_id为空');
        !$params['cut_down_goods_id'] && $this->error('cut_down_goods_id为空');
        $uid = $this->auth->id;

        $cut_where = [
            'sponsor_id' => $uid,
            'cut_down_goods_id' => $params['cut_down_goods_id'],
            'type' => 1,
            'status' => 0,
        ];
        if ($this->cut_down_record_model->where($cut_where)->count()) {
            $this->error('已发起砍价，不能重复砍价');
        }
        $where = [
            'id' => $params['cut_down_goods_id'],
            'status' => 1,
            'end_time' => ['EGT', time()],
        ];
        //获取活动信息
        $cut_goods_info = $this->cut_down_goods_model->find_data($where, 'floor_price , highest_price ,stock ,sales');
        !$cut_goods_info && $this->error('活动已结束');
        $cut_goods_info['stock'] <= $cut_goods_info['sales'] && $this->error('库存已售尽');

        $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $cut_goods_info['highest_price']);

        if ($cut_price == $cut_goods_info['highest_price']) {
            $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $cut_goods_info['highest_price']);
        }
        //添加砍价数据
        $add_array = [
            'uid' => $uid,
            'type' => 1,
            'avatar' => model('User')->getAvatar($uid),
            'cut_down_goods_id' => $params['cut_down_goods_id'],
            'add_time' => time(),
            'cut_price' => $cut_price,
            'sponsor_id' => $uid,
            'username' => model('User')->getField(['id' => $uid], 'username'),
        ];

        $add = $this->cut_down_record_model->add_data($add_array);
        $add_id = $this->cut_down_record_model->getLastInsID();
        //设置关联pid
        $update_ = $this->cut_down_record_model->update_data(['id' => $add_id], ['pid' => $add_id]);
        //添加砍价数据  number
        $set_cut_down_number = $this->cut_down_goods_model->where(['id' => $params['cut_down_goods_id']])->setInc('number');
        if ($add && $set_cut_down_number && $update_)
            $this->success('操作成功', ['cut_price' => $cut_price]);
        else
            $this->error('操作失败');
    }

    /*
     *
     * 取砍价流程信息(砍价过程)
     *
     */
    public function bargaining_process($id, $uid)
    {

        //获取砍价流程信息
        $field = 'id,avatar,sponsor_id,status,uid';
        $cut_down_list = $this->cut_down_record_model->getCutList(['cut_down_goods_id' => $id, 'sponsor_id' => $uid, 'status' => 0], $field, 'type desc', '', '');
        return $cut_down_list;
    }

    /**
     * 砍价详情(点头像进去记录详情)
     * @param cut_down_record_id 砍价记录id
     */

    public function cut_down_record()
    {

        $params = $this->request->request();
        $uid = $this->auth->id;
        !$params['cut_down_goods_id'] && $this->error('cut_down_goods_id不存在');
        !$params['sponsor_id'] && $this->error('sponsor_id不存在');
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');

        //记录列表
        $where = [
            'cut_down_goods_id' => $params['cut_down_goods_id'],
            'sponsor_id' => $params['sponsor_id'],
        ];
        $field = 'id,avatar,cut_price,add_time,username';
        $record_list = $this->cut_down_record_model->getCutList($where, $field, 'add_time desc', $params['page'], $params['pagesize']);
        $record_list = empty($record_list) ? [] : $record_list;

        $this->success('成功', ['list' => $record_list]);
    }

    /**
     * 分享后他人帮砍
     */
    public function shareCutDown()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id为空');
        !$params['cut_down_goods_id'] && $this->error('cut_down_goods_id为空');
        !$params['sponsor_id'] && $this->error('sponsor_id不存在');
        $uid = $this->auth->id;

        $cut_where = [
            'uid' => $uid,
            'cut_down_goods_id' => $params['cut_down_goods_id'],
            'type' => 0,
            'sponsor_id' => $params['sponsor_id'],
            'status' => 0,
        ];

        if ($this->cut_down_record_model->where($cut_where)->count()) {
            $this->error('已帮砍，不能重复砍价');
        }
        $where = [
            'id' => $params['cut_down_goods_id'],
            'status' => 1,
            'end_time' => ['EGT', time()],
        ];
        //获取活动信息
        $cut_goods_field = 'floor_price , highest_price ,stock ,sales,goods_price,discount_price,min_price';
        $cut_goods_info = $this->cut_down_goods_model->find_data($where, $cut_goods_field);
        !$cut_goods_info && $this->error('活动已结束');
        //获取商品已砍总金额
        $cut_price_where = ['cut_down_goods_id' => $params['cut_down_goods_id'], 'sponsor_id' => $params['sponsor_id'], 'status' => 0];
        $cut_prices = $this->cut_down_record_model->where($cut_price_where)->sum('cut_price');
        //获取未砍价格
        $uncut_price = ($cut_goods_info['goods_price'] - $cut_goods_info['discount_price']) - $cut_prices;

        if ($uncut_price <= 0) {
            $this->error('砍价已结束');
        }
        $cut_goods_info['stock'] <= $cut_goods_info['sales'] && $this->error('库存已售尽');
        //砍价金额
        $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $cut_goods_info['highest_price']);
        if ($cut_price >= $cut_goods_info['highest_price']) {
            $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $cut_goods_info['highest_price']);
        }
        if ($cut_price > $uncut_price) {
            if ($cut_goods_info['floor_price'] > $uncut_price) {
                $cut_goods_info['floor_price'] = 0.01;//$uncut_price 设置最低砍价金额
            }
            $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $uncut_price);
        }
        //添加砍价数据
        $add_array = [
            'uid' => $uid,
            'type' => 0,
            'avatar' => model('User')->getAvatar($uid),
            'cut_down_goods_id' => $params['cut_down_goods_id'],
            'add_time' => time(),
            'cut_price' => $cut_price,
            'sponsor_id' => $params['sponsor_id'],
            'username' => model('User')->getField(['id' => $uid], 'username'),
        ];

        $add = $this->cut_down_record_model->add_data($add_array);
        $add_id = $this->cut_down_record_model->getLastInsID();
        //设置关联pid
        $update_ = $this->cut_down_record_model->update_data(['id' => $add_id], ['pid' => $add_id]);
        //添加砍价数据  number
        $set_cut_down_number = $this->cut_down_goods_model->where(['id' => $params['cut_down_goods_id']])->setInc('number');
        if ($add && $set_cut_down_number)
            $this->success('操作成功', ['cut_price' => $cut_price]);
        else
            $this->error('操作失败');
    }

    /**
     * 获取我的中心我的砍价
     * @param page 分页
     * @param pagesize 分页
     *
     */
    public function my_cut_down()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        !$uid && $this->error('请登录后操作');
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');

        //视图查询
        $field = 'id,image,goods_name,discount_price,line_price,start_time,end_time,number,status,goods_id';
        $list = Db::view('cut_down_record c', 'cut_down_goods_id,pid') //查询出cut_down_record表的cut_down_goods_id跟pid字段
        ->where(['c.uid' => $uid, 'c.type' => 1])
            ->where(['g.status' => 10])
            ->view('cut_down_goods g', $field, 'c.cut_down_goods_id=g.id', 'left')//查询cut_down_goods表的$field字段,cut_down_goods表的cut_down_goods等于cut_down_goods表的cid,左查询
            ->select();
        if ($list)
            foreach ($list as $k => $v) {
                $list[$k]['image'] = config('item_url') . $v['image'];
                $cut_where = ['cut_down_goods_id' => $v['id'], 'type' => 1, 'uid' => $uid, 'pid' => $v['pid']];
                $list[$k]['cut_down_status'] = $this->cut_down_record_model->where($cut_where)->value('status');
                $list[$k]['miao'] = $v['status'] == 1 ? time() - $v['start_time'] : $v['end_time'] - time();
                $list[$k]['miao'] = $list[$k]['miao'] < 0 ? 0 : $list[$k]['miao'];
            }
        $this->success('success', ['list' => $list]);

    }

    /**
     * 获取拼团中页面（全部帮忙拼团信息）
     * @param Page 分页
     * @param pagesize 分页
     * @param pid 拼团记录id
     */

    public function getAllGroup()
    {
        $params = $this->request->request();
        !$params['pid'] && $this->error('pid为空');

        //开团人数
        $count_where = ['type' => ['neq', 0], 'pid' => $params['pid']];
        //获取开团列表
        $field = 'id,avatar,name,uid,add_time,group_num';
        $list = $this->join_groupbuy_model->select_page($count_where, $field, 'add_time aec');
        $list = $list ? $list : [];
        $this->success('成功', ['list' => $list]);
    }

}