<?php

namespace app\api\controller;

use app\admin\model\cms\Page;
use app\common\controller\Api;
use app\api\controller\Item;


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
        $ad = model('Cmsblock')->find_field_data(['status' => 'normal', 'id' => $params['cate_id']], 'images,url,id');
        //$ad = $this->setPlitJointImages($ad['images']);

        //获取产品信息
        $where = [
            'start_time' => ['lt', time()],
            'end_time' => ['gt', time()]
        ];
        $field = 'limit_discount_id,goods_id,goods_price,line_price,start_time,
                   end_time,status,goods_name,image,stock_num,sales,total_stock';
        $list = $this->limit_discount_goods_model->select_page_data($where, $field, $params['page'], $params['pagesize']);

        if ($list != null) {
            foreach ($list as $k => $v) {
                $list[$k]['type'] = time() <= $v['start_time'] ? 1 : 2;  //1)未开始 2)进行中
                $list[$k]['miao'] = $list[$k]['type'] == 1 ? time() - $v['start_time'] : $v['end_time'] - time();
                $list[$k]['image'] = config('item_url') . $v['image'];
                $percentage = round($v['sales'] / $v['total_stock'], 2);
                $list[$k]['percentage'] = $percentage < 0 ? 0 : $percentage;
            }
        }

        $this->success('获取成功', ['ad' => $ad, 'list' => $list]);
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
        //获取分享口令
        $info = $this->getActivityInfo($itemdetail, $params['goods_id'], 1, $params['limit_discount_id'], $uid);
        if ($uid) {
            $user = controller('User');
            $cmmand = $user->addCmmand($uid, '', $params['goods_id'], 4);
            $info['cmmand'] = $this->getShareInfo($info['goods_name'], $cmmand);
        }

        $this->success('获取成功', $info);
    }


    /**
     * 2人团购 商品列表
     * @param cate_id 活动分类id
     * */
    public function getGroupbugGoods()
    {
        $params = $this->request->request();
        //获取轮播图
        $ad = model('Cmsblock')->find_field_data(['status' => 'normal', 'id' => $params['cate_id']], 'images,url,id');

        //获取产品信息
        $field = 'id,groupbuy_id,goods_id,goods_price,line_price,group_num,
                   group_nums,status,goods_name,image,stock_num';
        $list = $this->groupbuy_goods_model->select_page_data([], $field, $params['page'], $params['pagesize']);

        $this->success('获取成功', ['ad' => $ad, 'list' => $list]);
    }


    /**
     * 团购商品详情
     * @param $goods_id
     * @param $groupbuy_id
     */
    public function getGroupbuyDetail()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id为空');
        !$params['groupbuy_id'] && $this->error('groupbuy_id为空');
        $uid = $this->auth->id;

        //获取商品详情
        $model = new Item();
        $itemdetail = $model->ItemDetail($params['goods_id'], $uid);
        $info = $this->getActivityInfo($itemdetail, $params['goods_id'], 2, $params['groupbuy_id']);
        //开团人数
        $where = ['status' => 1, 'type' => 1, 'goods_id' => $params['goods_id']];
        $info['groupbuy_num'] = $this->join_groupbuy_model->getGroupbuyNum($where);
        if ($params['invite_id']) {
            //获取group_id
            $where['uid'] = $params['invite_id'];
            $group_id = $this->join_groupbuy_model->where($where)->value('id');
            $info['details_type'] = 1;
            $uuid = $params['invite_id'];
        } else {
            $info['details_type'] = 0;
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
            $field = 'avatar , add_time , group_num , join_num , hour , name , uid , id';
            unset($where['uid']);
            $list = $this->join_groupbuy_model->getPageList($where, $field, 'id desc', 1, 6);
        }
        //获取邀请好友拼团口令
        $user = controller('User');
        $cmmand = $user->addCmmand($uid, $info['activity_id'], $params['goods_id'], 3);
        $cmmand && $info['cmmand'] = $this->getShareInfo($info['goods_name'], $cmmand);

        //判断用户是否参加拼团
        if ($this->join_groupbuy_model->where(['uid' => $uid, 'goods_id' => $params['goods_id'], 'status' => 1, 'type' => 1])->count() > 0) {
            $info['assemble_status'] = 1;
        } else {
            $info['assemble_status'] = 0;
        }
        $list = $info['assemble_status'] == 1 ? [] : $list;
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
        !$uid && $this->error('请登录后操作');
        !$params['activity_id'] && $this->error('activity_id为空');

        //如果存在订单id
        if ($params['order_id']) {
            $order_id = $params['order_id'];
        } else {
            //用订单编号获取订单id
            $order_id = model('Litestoreorder')->where(['order_no' => $params['order_no']])->value('id');
            !$order_id && $this->error('订单不存在');
        }

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
        $info = $order_goods_model->find_data($where, 'id,images as image,goods_name,goods_price,total_num,line_price,key_name,createtime');
        $info['image'] = config('item_url') . $info['image'];
        $info['hour'] = $groupbuy_goods_info['hour'];
        $info['group_num'] = $groupbuy_goods_info['group_num'];

        //开团人数
        $count_where = ['type' => ['neq', 0], 'pid' => $pid];
        $groupbuy_num = $this->join_groupbuy_model->getGroupbuyNum($count_where);

        $groupbuy_status = $groupbuy_num >= $groupbuy_goods_info['group_num'] ? 1 : 0;
        //获取开团列表 2条数据
        $field = 'id ,avatar , group_num , join_num , uid';
        $list = $this->join_groupbuy_model->select_page($count_where, $field, 'add_time aec', 1, 2);

        //获取猜你喜欢
        $goods_model = model('Litestoregoods');
        $goods_list = $goods_model->like_goods($uid);
        //获取邀请好友拼团口令
        $user = controller('User');
        $cmmand = $user->addCmmand($uid, $groupbuy_goods_info['id'], $groupbuy_goods_info['goods_id'], 3);
        $cmmand && $info['cmmand'] = $this->getShareInfo($info['goods_name'], $cmmand);
        //获取倒计时

        $info['miao'] = $this->join_groupbuy_model->getMiao(['order_id' => $order_id]);

        if ($info['miao'] == 0 && $groupbuy_num < $groupbuy_goods_info['group_num']) {
            //活动已到期
            $groupbuy_status = 2;
//            $pay = controller('Pay');
//            $outdated = $pay->refund_groupbuy($pid, 1);
//            if (!$outdated) {
//                $this->error('操作失败');
//            }
        }
        if ($params['invite_id'] && $uid != $params['invite_id']) { //是否邀请人id存在
            $invite_id = $params['invite_id'];//分享用户id
            $info['details_type'] = 1;
        } else {
            $info['details_type'] = 0;
        }
        $info['invite_id'] = empty($invite_id) ? '' : $invite_id;
        if ($groupbuy_status == 1 && $info['details_type'] == 1) {
            $groupbuy_status = 3; //拼团成功他人视角状态
        }
        if ($info && $cmmand) {
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
        !$params['groupbuy_id'] && $this->error('groupbuy_id为空');

        $where = ['groupbuy_id' => $params['groupbuy_id'], 'status' => 1, 'type' => 1, 'goods_id' => $params['goods_id']];
        $field = 'id,avatar,add_time,group_num,join_num,hour,name,uid';
        $list = $this->join_groupbuy_model->getPageList($where, $field, 'id desc', $params['page'], $params['pagesize']);

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
                $activity = $this->limit_discount_goods_model->find_data(['goods_id' => $goods_id, 'limit_discount_id' => $activity_id], 'id,start_time ,end_time , stock_num , sales , upper_num');
                $item_info['type'] = time() <= $activity['start_time'] ? 1 : 2;  //1)未开始 2)进行中
                $item_info['miao'] = $item_info['type'] == 1 ? time() - $activity['start_time'] : $activity['end_time'] - time();
                $item_info['activity_id'] = $activity['id'];
                $item_info['miao'] = $item_info['miao'] <= 0 ? 0 : $item_info['miao'];
                $item_info['stock_num'] = $activity['stock_num'];
                $item_info['sales'] = $activity['sales'];
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
                if ($uid && $activity_id) {
                    //判断是否收藏
                    $count_collect = model('Collect')->getCount(['pid' => $activity_id, 'item_status' => 1]);
                    $item_info['is_collect'] = $count_collect > 0 ? 1 : 0;
                }
                break;

            case 3://砍价商品
                //$field = 'id as activity_id,cut_down_id,line_price,discount_price,stock,upper_num,goods_spec_id,
                // end_time,number,start_time,key_name,sales,floor_price,highest_price,goods_status';

                $activity = $this->cut_down_goods_model->getLimitDiscountGoods(['goods_id' => $goods_id, 'id' => $activity_id], '*');
                empty($activity) && $this->error('商品不存在');

                //判断该活动是否过期
                if ($activity['end_time'] - time() < 0) {
                    $save['status'] = 3;
                    $this->cut_down_goods_model->where(['goods_id' => $goods_id, 'id' => $activity_id])->update($save);
                }
                /*if ($activity['start_time'] - time() > 0){

                    $this->cut_down_goods_model->where(['goods_id' => $goods_id, 'id' => $activity_id])->update($save);
                }*/
                /*if ($activity['start_time'] < time() && $activity['end_time'] > time()){

                    $this->cut_down_goods_model->where(['goods_id' => $goods_id, 'id' => $activity_id])->update($save);
                }*/
                //砍价商品最低价
//                $item_info['min_price'] = empty($activity['min_price']) ? 0.00 : $activity['min_price'];
                //最低价
                /*$item_info['cut_down_id'] = $activity['cut_down_id'];
                $item_info['activity_id'] = $activity['id'];
                $item_info['floor_price'] = $activity['floor_price'];
                $item_info['highest_price'] = $activity['highest_price'];
                $item_info['line_price'] = $activity['line_price']; //市场价
                $item_info['discount_price'] = $activity['discount_price'];//折扣价
                $item_info['stock'] = $activity['stock'];
                //活动说明时间
                $item_info['start_time'] = empty($activity['start_time']) ? 0 : $activity['start_time'];
                $item_info['end_time'] = empty($activity['end_time']) ? 0 : $activity['end_time'];
                $item_info['number'] = empty($activity['number']) ? 0 : $activity['number']; //砍价人数
                $item_info['sales'] = empty($activity['sales']) ? 0 : $activity['sales'];

                //$item_info['miao'] = $activity['goods_status'] == 2 ? time() - $activity['start_time'] : $activity['end_time'] - time();//
                $item_info['miao'] = ($activity['end_time'] - time()) > 0 ? $activity['end_time'] - time() : 0;
                //砍价商品规格
                $item_info['key_name'] = $activity['key_name'];
                //获取商品规格值
                $item_info['item_sku'] = [];
                $item_info['goods_spec_id'] = $activity['goods_spec_id'];*/

                //活动说明时间
                $activity['start_time'] = empty($activity['start_time']) ? 0 : $activity['start_time'];
                $activity['end_time'] = empty($activity['end_time']) ? 0 : $activity['end_time'];
                $activity['number'] = empty($activity['number']) ? 0 : $activity['number']; //砍价人数
                $activity['sales'] = empty($activity['sales']) ? 0 : $activity['sales'];
                $activity['miao'] = ($activity['end_time'] - time()) > 0 ? $activity['end_time'] - time() : 0;
                $activity['item_sku'] = [];
                break;
        }
        $activity['upper_num'] = $activity['upper_num'] > 0 ? $activity['upper_num'] : 0;
        return array_merge($item_info, $activity);
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

        $field = 'id,cut_down_id,goods_id,line_price,discount_price,start_time,end_time,goods_name,image,number';
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
        if ($params['sponsor_id'] && $params['sponsor_id'] != $uid) {
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
            $cmmand && $info['cmmand'] = $this->getShareInfo($info['goods_name'], $cmmand);
        }
        $cut_down_list['info'] && $info = array_merge($info, $cut_down_list['info']);

        $cut_down_list['status'] = $cut_down_list['percentage'] == 1 ? 1 : 0;

        $info['cut_down_status'] = $cut_down_list['status'];
        //获取发起人的信息
        $info['list'] = $cut_down_list['list'];
        $info['details_type'] = $cut_down_list['details_type'];
        $info['percentage'] = $cut_down_list['percentage'];
        $info['details_status'] = $cut_down_list['status'];//0) 进行中 1）已完成 3）已过期
        $this->success('获取成功', $info);
    }

    /*
     * 砍价流程接口
     * @param additional_id 分享进来详情的分享人id;
     *
     */
    public function bargaining_info($uid, $id, $sponsor_id, $id_type)
    {

        //获取商品详情信息
        $cut_down_goods = $this->cut_down_goods_model->find_data(['id' => $id], 'id,discount_price,goods_price,line_price,min_price')->getData();
        $status = '';
        //判断是否是自己进入详情
        $where = [
            'uid' => $uid,
            'cut_down_goods_id' => $id,
        ];
        if ($id_type == 1) {
            $cut_down_list = $this->bargaining_process($id, $sponsor_id);
            //判断他人是否帮忙砍价
            $where['sponsor_id'] = $sponsor_id;
            $where['type'] = 0;
            $type = 2;//他人通过分享进入详情
            $status = $this->cut_down_record_model->where($where)->count() != 0 ? 1 : 0; //status=0 别人为帮忙砍价  status = 1 别人帮忙砍价
        } else {
            //判断该商品自己是否发起砍价
            $where['type'] = 1;
            $where['status'] = 0;

            if ($this->cut_down_record_model->where($where)->count() != 0) {
                $cut_down_list = $this->bargaining_process($id, $uid);
                $type = 1; //自己进去详情（已发起砍价）
            } else {
                $type = 0;//自己进去详情（未发起砍价）
                $cut_down_list = $this->bargaining_process($id, $uid);
            }
        }
        $where = ['cut_down_goods_id' => $id, 'sponsor_id' => $sponsor_id];
        $cut_down_info = $this->cut_down_record_model->find_data([$where, 'type' => 1], 'username , avatar , sponsor_id');

        $where['status'] = 0;
        //获取已砍价总额
        $cut_down_info['cut_prices'] = $this->cut_down_record_model->where($where)->sum('cut_price');
        //砍价人数
        $cut_down_info['cut_down_num'] = $this->cut_down_record_model->getCutDownNum($where);
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

//    public function test()
//    {
//        echo $this->randomFloat();
//        exit;
//    }

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

        $user_model = model('User');
        //添加砍价数据
        $add_array = [
            'uid' => $uid,
            'type' => 1,
            'avatar' => $user_model->getAvatar($uid),
            'cut_down_goods_id' => $params['cut_down_goods_id'],
            'add_time' => time(),
            'cut_price' => $cut_price,
            'sponsor_id' => $uid,
            'username' => $user_model->getField(['id' => $uid], 'username'),
        ];

        $add = $this->cut_down_record_model->add_data($add_array);
        $add_id = $this->cut_down_record_model->getLastInsID();
        //设置关联pid
        $update_ = $this->cut_down_record_model->update_data(['id' => $add_id], ['pid' => $add_id]);
        //添加砍价数据  number
        $set_cut_down_number = $this->cut_down_goods_model->where(['id' => $params['cut_down_goods_id']])->setInc('number');
        if ($add && $set_cut_down_number && $update_)
            $this->success('操作成功', ['cut_price' => $cut_price]);
        $this->error('操作失败');
    }

    /*
     * 取砍价流程信息(砍价过程)
     */
    public function bargaining_process($id, $uid)
    {
        //获取砍价流程信息
        $field = 'id,avatar,sponsor_id,status,uid';
        $where = ['cut_down_goods_id' => $id, 'sponsor_id' => $uid, 'status' => 0];
        return $this->cut_down_record_model->getCutList($where, $field, 'type desc', '', '');
    }

    /**
     * 砍价详情(点头像进去记录详情)
     * @param cut_down_record_id 砍价记录id
     */

    public function cut_down_record()
    {

        $params = $this->request->request();
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
        $record_list = $this->cut_down_record_model->getCutList($where, $field, '', $params['page'], $params['pagesize']);
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
        //获取商品已砍总金额
        $cut_prices = $this->cut_down_record_model->where(['cut_down_goods_id' => $params['cut_down_goods_id'], 'sponsor_id' => $params['sponsor_id'], 'status' => 0])->sum('cut_price');

        if ($this->cut_down_record_model->where($cut_where)->count()) {
            $this->error('已帮砍，不能重复砍价');
        }
        $where = [
            'id' => $params['cut_down_goods_id'],
            'status' => 1,
            'end_time' => ['EGT', time()],
        ];
        //获取活动信息
        $cut_goods_info = $this->cut_down_goods_model->find_data($where, 'floor_price , highest_price ,stock ,sales,goods_price,discount_price,min_price');
        !$cut_goods_info && $this->error('活动已结束');
        //获取商品已砍总金额
        $cut_prices = $this->cut_down_record_model->where(['cut_down_goods_id' => $params['cut_down_goods_id'], 'sponsor_id' => $params['sponsor_id'], 'status' => 0])->sum('cut_price');
        //获取未砍价格
        $uncut_price = ($cut_goods_info['goods_price'] - $cut_goods_info['discount_price']) - $cut_prices;

        if ($uncut_price <= 0) {
            $this->error('砍价已结束');
        }
        $cut_goods_info['stock'] <= $cut_goods_info['sales'] && $this->error('库存已售尽');

        $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $cut_goods_info['highest_price']);
        if ($cut_price > $uncut_price) {
            if ($cut_goods_info['floor_price'] > $uncut_price) {
                $cut_goods_info['floor_price'] = 0;
            }
            $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $uncut_price);
        }

        if ($cut_price == $cut_goods_info['highest_price']) {
            $cut_price = $this->randomFloat($cut_goods_info['floor_price'], $cut_goods_info['highest_price']);
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
//        $update_ = $this->cut_down_record_model->update_data(['id' => $add_id], ['pid' => $add_id]);
        //添加砍价数据  number
        $set_cut_down_number = $this->cut_down_goods_model->where(['id' => $params['cut_down_goods_id']])->setInc('number');
        if ($add && $set_cut_down_number)
            $this->success('操作成功', ['cut_price' => $cut_price]);
        $this->error('操作失败');
    }

    /**
     * 获取分享后跳出的商品信息
     * @param cmmand 口令
     */
    public function shareCutDownInfo()
    {
        $cmmand = $this->request->request('cmmand');
        $uid = $this->auth->id;
        !$cmmand && $this->error('cmmand不存在');
        !$uid && $this->error('请登录后操作');

        $user = controller('User');
        //解密传过来的口令
        $cmmand_info = $user->analysisCommand($cmmand); // 口令中包含的信息;
        $cmmand_info = empty($cmmand_info) ? '' : $cmmand_info;
        //获取口中分享人的信息
        $user_model = model('User');
        $user_info['avatar'] = $user_model->getAvatar($cmmand_info['uid']);
        $user_info['username'] = $user_model->getField(['id' => $cmmand_info['uid']], 'username');
        //判断跳拼团还是砍价
        $where = ['goods_id' => $cmmand_info['goods_id']];
        switch ($cmmand_info['type']) {
            case 1:
                $goods_infos = $this->item->find_data($where, 'goods_id,image,goods_name,goods_price')->toArray();
                $goods_infos['discount_price'] = $goods_infos['goods_price'];
                break;
            case 2://砍价
                $where['id'] = $cmmand_info['activity_id'];
                $goods_infos = $this->cut_down_goods_model->find_data($where, 'id ,image, goods_id, goods_name ,discount_price');
                //获取砍价状态（是否已帮砍价）
                $record_where =
                    [
                        'status' => 0,
                        'uid' => $uid,
                        'cut_down_goods_id' => $cmmand_info['activity_id'],
                        'sponsor_id' => $cmmand_info['uid'],
                    ];
                $goods_infos['cut_down_status'] = $this->cut_down_record_model->where($record_where)->count() > 0 ? 1 : 0;
                $status = $goods_infos['cut_down_status'] == 1 ? 1 : 0;
                break;
            case 3://拼团
                $goods_infos = $this->groupbuy_goods_model->find_data($where, 'id ,image ,goods_id ,goods_name ,goods_price');
                $where = ['groupbuy_id' => $cmmand_info['activity_id'], 'uid' => $cmmand_info['uid'], 'status' => 1];
                $status = $this->join_groupbuy_model->where($where)->value('type');
                $where['type'] = 1;
                $order_id = $this->join_groupbuy_model->where($where)->value('order_id');
                //获取拼团状态（是否已帮拼团）
                $record_where =
                    [
                        'uid' => $uid,
                        'groupbuy_id' => $cmmand_info['activity_id'],
                        'goods_id' => $cmmand_info['goods_id'],
                    ];
                $goods_infos['cut_down_status'] = $this->join_groupbuy_model->where($record_where)->count() > 0 ? 1 : 0;
                $goods_infos['discount_price'] = $goods_infos['goods_price'];
                $goods_infos['order_id'] = $order_id;
                break;
            case 4://限时抢购
                $goods_infos = $this->limit_discount_goods_model->find_data($where, 'id,image,goods_id,goods_name,goods_price');
                $goods_infos['discount_price'] = $goods_infos['goods_price'];
        }
        //获取商品的信息
        $goods_infos['image'] = config('item_url') . $goods_infos['image'];
        $goods_infos['type'] = $cmmand_info['type'];
        $goods_infos['uid'] = $cmmand_info['uid'];
        $goods_infos['activity_id'] = $cmmand_info['activity_id'];
        $goods_infos['status'] = $status;
        $goods_infos['type'] = $cmmand_info['type'];
        if ($user_info && $goods_infos) {
            $cmmand_info['type'] != 1 && $goods_infos = $goods_infos->toArray();
            //判断是否自己复制口令
            $info = array_merge($user_info, $goods_infos);
        }
        $info['is_share'] = $uid == $cmmand_info['uid'] ? 1 : 0;
        $this->success('成功', $info);
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

        //获取我砍价商品id;
        $cut_goods_id = $this->cut_down_record_model->where(['sponsor_id' => $uid, 'type' => 1])->column('cut_down_goods_id');

        $where = ['id' => ['IN', $cut_goods_id]];
        $field = 'id,image,goods_name,discount_price,line_price,start_time,end_time,number,status,goods_id';
        $cut_down_goods_list = $this->cut_down_goods_model->select_page_data($where, $field, $params['page'], $params['pagesize'], '')->toArray();
        $list = empty($cut_down_goods_list) ? [] : $cut_down_goods_list;

        if ($list)
            foreach ($list as $k => $v) {
                $cut_where = ['cut_down_goods_id' => $v['id'], 'type' => 1, 'sponsor_id' => $uid];
                $list[$k]['cut_down_status'] = $this->cut_down_record_model->where($cut_where)->value('status');
                $list[$k]['miao'] = $v['status'] == 1 ? time() - $v['start_time'] : $v['end_time'] - time();
                $list[$k]['miao'] = $list[$k]['miao'] < 0 ? 0 : $list[$k]['miao'];
            }

        $this->success('成功', ['list' => $list]);
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