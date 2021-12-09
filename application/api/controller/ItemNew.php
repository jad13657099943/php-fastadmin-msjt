<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Litestorefreight;
use think\Db;
use think\Controller;


/**
 * 商品控制器
 */
class Item extends Api
{
    protected $noNeedLogin = ['getAllEvaluate', 'getItemLists', 'getItemDetail', 'getItemSku', 'ItemDetail', 'getGroupbuyDetail', 'getLimitDiscountDetail', 'getCateList', 'checkGoodsSpec'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        $this->item = model('Litestoregoods');
        $this->item_discount = model('Limitdiscountgoods');
        $this->item_groupbuy = model('Groupbuygoodsrecord');
        $this->item_cut_down = model('Cutdowngoods');
        $this->item_groupbuy_sku = model('Groupbuygoods');
        $this->item_spec = model('Litestoregoodsspec');
        $this->item_cate = model('Litestorecategory');
        $this->search = model('Searchrecord');
        $this->user = model('User');
        $this->limit_discount_goods_model = model('Limitdiscountgoods');
    }

    /**
     * 根据选择的规格，判断另外的规格是否为空
     * @param $goods_id
     * @param $sku_id
     */
    public function checkGoodsSpec()
    {
        $goods_id = $this->request->request('goods_id');
        $sku_id = $this->request->request('sku_id');
        !$goods_id && $this->error('goods_id不能为空');
        !$sku_id && $this->error('sku_id不能为空');

        //判断商品是否存在
        $where = ['goods_id' => $goods_id, 'spec_sku_id' => ['like', '%' . $sku_id . '%']];
        $goods_specs = $this->item_spec->select_data($where, 'stock_num,spec_sku_id');

        if ($goods_specs != null) {
            foreach ($goods_specs as $k => $v) {
                $v['stock_num'] = 0;
//                if($v['stock_num'] == 0){
                $position = strpos($v['spec_sku_id'], $sku_id);
                $skus_id = $position == 0 ? $sku_id . '_' : '_' . $sku_id;
                $row[] = str_replace($skus_id, '', $v['spec_sku_id']);
//                }
            }
        } else
            $this->error('商品不存在');

        $this->success('获取成功', $row);
    }

    /*
     * 获取商品列表 专区搜索 今日特价搜索 分类首页 首页搜索
     * 需要字段
     * 图片 名称 价格
     * 搜索进入 人气排序 销量排序 价格排序
     *
     * @param  $home_cate_id  38）畅饮新品  39）女士专区  41）儿童专区 40）乳糖不耐受  34）今日特价
     * @param  $page
     * @param  $pagesize
     * @param  $keyword 搜索字段
     * @param  $sales_order  1）销量降序
     * @param  $price_order  1）价格降序  2）价格升序
     * @paaram $goods_sort_order 1）人气降序
     * @param  $category_id  分类ID
     *
     * 砍价进入 市场价 倒计时 参与人数
     * 专区进入 图片
     * 限时抢购进入 轮播图 时间分类 市场价 剩余数量 倒计时  已经采购数量
     * 今日特价 轮播图 市场价
     * 2人拼团  轮播图 库存 已团人数 状态
     */

    public function getItemLists()
    {
        $data = $this->request->request();

        !is_numeric($data['page']) && $this->error('page不能为空');
        !is_numeric($data['pagesize']) && $this->error('pagesize不能为空');

        $where = $cate_list = [];
        $order = 'goods_price asc';

        if ($data['home_cate_id'] == 38 || $data['home_cate_id'] == 39 || $data['home_cate_id'] == 40 || $data['home_cate_id'] == 41 || $data['home_cate_id'] == 34) {
            //获取展示图片或者轮播图
            $banner = model('Cmsblock')->find_data(['status' => 'normal', 'id' => $data['home_cate_id']], 'id,url,images');
            $banner['images'] = $this->setPlitJointImages($banner['images']);

            $where['status'] = ['like', '%' . $data['home_cate_id'] . '%'];
        } else {

            if ($data['keyword']) { //首页搜索
                $where['goods_name'] = ['LIKE', '%' . $data['keyword'] . '%'];
                $this->auth->id && $this->addKeyword($data['keyword'], $this->auth->id);
            }

            //排序
            $sales_order = $data['sales_order']; //销量排序
            $order = $sales_order == 1 ? 'sales_initial desc' : $order;

            $data['price_order'] && $order = $data['price_order'] == 1 ? 'goods_price desc' : 'goods_price asc';

            $order = $data['goods_sort_order'] == 1 ? 'goods_sort asc' : $order;

            $data['category_id'] && $where['category_id'] = $data['category_id'];
            //获取分类
            $cate_list = $this->item_cate->getLitestoreCategoryList(['pid' => 0], 'id,name')->toArray();
            array_unshift($cate_list, ['id' => 0, 'name' => '全部']);
        }

        //获取商品信息
        $field = 'goods_id,goods_name,image,goods_price,line_price , marketing_goods_price , is_marketing ,marketing_id';
        $list = $this->item->getLitestoreGoods($where, $field, $order, $data['page'], $data['page']);
        $banner = '';
        $this->success('获取成功', ['banner' => $banner, //专区 今日特价轮播图
            'list' => $list, //商品列表
//                'cate_list' => $cate_list //分类列表
        ]);
    }

    /**
     * 获取商品分类
     * @param $keyword
     * @param $uid
     * @return
     */
    public function getCateList()
    {
        //获取分类
        $cate_list = $this->item_cate->getLitestoreCategoryList(['pid' => 0], 'id,name')->toArray();
        array_unshift($cate_list, ['id' => 0, 'name' => '全部']);
        $cate_list = empty($cate_list) ? [] : $cate_list;

        $this->success('成功', ['list' => $cate_list]);
    }

    /*
     * 添加搜索记录
     * @param $keyword
     * @param $uid
     * */
    public function addKeyword($keyword, $uid)
    {

        $arr = ['uid' => $uid, 'search_name' => $keyword, 'type' => 1];

        //添加修改自己搜索记录
        if ($this->search->where($arr)->count())
            $this->search->where($arr)->setField('add_time', time());
        else {
            $arr['add_time'] = time();
            $this->search->insert($arr);
        }

        //记录热门搜索
        unset($arr['uid']);
        $arr['type'] = 2;
        if ($this->search->where($arr)->count()) {
            $this->search->where($arr)->setInc('ordid');
        } else {
            $hot_where['add_time'] = time();
            $hot_where['ordid'] = 1;
            $this->search->insert($arr);
        }
        return true;
    }

    /*
     * 商品详情
     * @param goods_id 商品id
     * */
    public function getItemDetail()
    {
        $data = $this->request->request();
        $data['uid'] = $this->auth->id;

        $item_info = $this->ItemDetail($data['goods_id'], $data['uid']);
        //获取分享口令
        if ($data['uid']) {
            $cmmand = controller('User')->addCmmand($data['uid'], '', $data['goods_id'], 1);
            $item_info['cmmand'] = $this->getShareInfo($item_info['goods_name'], $cmmand);
        }
        $this->success('获取成功', $item_info);
    }


    //************************************商品详情函数开始*************//

    /**
     * 商品详情- 不是接口
     * @param $goods_id 商品id
     * @param $uid 用户id
     * @param $marketing_type 1) 团购  2）限时秒杀
     */
    public function ItemDetail($goods_id, $uid = '')
    {

        !$goods_id && $this->error('goods_id为空');

        $field = 'category_id,deduct_stock_type,sales_actual,goods_sort,is_delete,createtime,updatetime,stock_num';

        $item_info = $this->item->find_field_data(['goods_id' => $goods_id], $field);
        !$item_info && $this->error('商品已下架');

        if ($item_info['is_marketing'] == 1 || $item_info['is_marketing'] == 2) {
            $item_info['goods_price'] = $item_info['marketing_goods_price'] > 0 ? $item_info['marketing_goods_price'] : $item_info['goods_price'];
            $item_info['goods_price_section'] = $item_info['marketing_goods_price_section'] > 0 ? $item_info['marketing_goods_price_section'] : $item_info['goods_price_section'];
        }

        $this->item->where(['goods_id' => $goods_id])->setInc('hits');

        $item_info['param'] = model('Itemattr')->select_data(['goods_id' => $goods_id, 'type' => 1], 'name title ,value');

        $item_info['video'] = $item_info['video'] ? config('item_url') . $item_info['video'] : '';


        $item_info['spec_image'] = $item_info['image'] ? config('item_url') . $item_info['image'] : '';

        $item_info['images'] = $item_info['images'] ? $this->setPlitJointImages($item_info['images'], $goods_id, $item_info['video']) : '';

        //获取基础保障数据
        $item_info['basic_security'] = model('Config')->where(['id' => ['IN', '48,49,50']])->field('title,value')->select();

        //规则模型
        $item_info['spec_name'] = $item_info['spec_type'] == 20 ? model('Litestoregoodsspecrel')->fieid_spec_names(['goods_id' => $goods_id], 'spec_id') . '选择' : '';

        //统计总数
        $item_info['count_comment_number'] = model('Comment')->count_comment_number(['goods_id' => $goods_id]);

        //最新一条评论
        $comment_info = model('Comment')->find_data(['goods_id' => $goods_id], 'id,uid,content,images,add_time,goods_sku');
        if ($comment_info) { //头像
            $comment_info['user_name'] = $this->user->getField(['id' => $comment_info['uid']], 'username');
            $comment_info['user_head'] = $this->user->getAvatar($comment_info['uid']);
            $comment_info['images'] = $comment_info['images'] ? $this->setPlitJointImages($comment_info['images']) : [];
        }
        $item_info['comment'] = $comment_info;
        //客服电话
        $tel = model('Config')->where(['id' => '47'])->column('value');
        $item_info['tel'] = $tel[0];

        $item_info['is_collect'] = $item_info['shopping_cart_num'] = 0; //默认没有收藏
        if ($uid) {
            //判断是否收藏
            $count_collect = model('Collect')->getCount(['uid' => $uid, 'goods_id' => $goods_id]);
            $item_info['is_collect'] = $count_collect > 0 ? 1 : 0;

            //获取购物车数量
            $item_info['shopping_cart_num'] = model('Shopingcart')->getShopingCartNum(['uid' => $uid]);
        }

        $coupon_info = model('Couponrecord')->find_data(['status' => 1], 'title', 'coupon_price desc');
        $item_info['coupon_info'] = $coupon_info ? $coupon_info['title'] : '暂无优惠券';

        //获取商品库存 价格
        // $item_info['spec'] = $this->item_spec->getLitestoreGoodsSpec($data['goods_id'],
        // 'goods_spec_id,goods_id,goods_price,line_price,stock_num,goods_sales,spec_sku_id,key_name');

        if ($item_info['spec_type'] == 20) { //获取多规格信息
            //  $item_infos = $this->item->get($data['goods_id'], ['spec','specRel', 'spec_rel.spec']);
            // $item_info['spec'] = $item_infos['spec'];
            // $item_info['item_sku'] = $this->edit_spec_array($item_infos['spec_rel']);

            $item_info['item_sku'] = model('Litestoregoodsspecrel')->select_spec_names(['goods_id' => $item_info['goods_id']]);
        } else
            $item_info['spec_rel'] = [];
        //获取商品规格总库存
        $item_info['stock_nums'] = model('Litestoregoodsspec')->where(['goods_id' => $item_info['goods_id']])->sum('stock_num');

        //运费
        $item_info['freight'] = $item_info['freight_desc'] = model('Litestorefreight')->find_one_data(config('site.freight'));
        return $item_info;
    }

    //****************************结束****************************//


    /*
     * 编辑多规格数组
     * */
    public function edit_spec_array($array)
    {
        $map = [];
        foreach ($array as $k => $v) {
            $map[] = $v['spec'];
            unset($array[$k]['pivot'], $v['createtime'], $array[$k]['spec']);
        }

        $map = $this->assoc_unique($map, 'id');
        foreach ($map as $key => $value) {
            unset($map[$key]['createtime']);

            $sub = [];
            foreach ($array as $kk => $vv) {
                $vv['spec_id'] == $value['id'] && $sub[] = $vv;
                $map[$key]['sub'] = $sub;
            }
        }
        return $map;
    }

    /*
     * [array_group_by ph]
     * @param  [type] $arr [二维数组]
     * @param  [type] $key [键名]
     * @return [type]      [新的二维数组]
     */
    function assoc_unique($arr, $key)
    {
        $tmp_arr = array();
        foreach ($arr as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($arr[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        rsort($arr); //sort函数对数组进行排序
        return $arr;
    }

    /*
     * 根据规格ID 获取多规格库存 单价
     * */
    public function getItemSku()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id不能为空');
        !$params['sku_id'] && $this->error('sku_id不能为空');

        $where['goods_id'] = $params['goods_id'];
        $spec_sku_ids = explode('_', $params['sku_id']);
        sort($spec_sku_ids);
        $where['spec_sku_id'] = implode('_', $spec_sku_ids);

        $item_sku = $this->item_spec->find_data($where, '*');
        $params['type'] == 5 && $item_sku['goods_price'] = $item_sku['line_price'];

        $item_sku['spec_image'] = $item_sku['spec_image'] ? config('item_url') . $item_sku['spec_image'] : '';
        $this->success('获取成功', $item_sku);
    }


    /*
     * 加入购物车 ---目前只做普通商品
     * @param $goods_id 商品ID
     * @param $num 加入数量
     * @param $goods_spec_id 规格ID
     * @param $type 1）正常商品  2）限时抢购 3）今日特价
     * @param $activity_id
     * */
    public function addShopingCart()
    {
        $params = $this->request->request();

        !$params['goods_id'] && $this->error('goods_id不能为空');
        !$params['num'] && $this->error('num不能为空');
        !$params['goods_spec_id'] && $this->error('good_spec_id不能为空');
        !$params['type'] && $this->error('type不能为空');
        if ($params['type'] == 2)
            !$params['activity_id'] && $this->error('activity_id 不能为空');

        $shoping_cart_model = model('Shopingcart');
        //查看购物车中是否有该商品
        $where = ['goods_id' => $params['goods_id'], 'goods_spec_id' => $params['goods_spec_id'], 'uid' => $this->auth->id];
        $cart_info = $shoping_cart_model->find_data($where, '');
        //获取规格信息
        $spec_info = $this->item_spec->find_data(['goods_spec_id' => $params['goods_spec_id']], '*');
        if ($cart_info) {
            if ($spec_info) {
                $update_item_nums = $cart_info['num'] + $params['num']; //同一商品,原有的数量+新传递过来的数量。
                $update_item_nums > $spec_info['stock_num'] && $this->error('商品库存不足!');
                $data['num'] = $update_item_nums;
                $result = $shoping_cart_model->where(array('id' => $cart_info['id']))->update($data);
            }
        } else {
            $where['goods_id'] = $params['goods_id'];
            switch ($params['type']) { //1）正常商品  2）限时抢购 3）今日特价
                case 2: //限时抢购
                    //判断该活动商品是否取消
                    $goods_info = $this->item_discount->find_data($where, 'status,image,goods_name');
                    $goods_info['status'] == 0 && $this->error('活动已到期');
                    break;
                default:
                    //判断是否下架
                    $goods_info = $this->item->find_data($where, 'goods_name,image');
                    !$goods_info && $this->error('商品已下架');
                    break;
            }
            $where['goods_spec_id'] = $params['goods_spec_id'];
            $goods_spec_info = $this->item_spec->find_data($where, 'stock_num,key_name,spec_sku_id,spec_image');
            //判断库存是否充足
            $goods_spec_info['stock_num'] < $params['num'] && $this->error('商品库存不足');

            $goods_spec_info['uid'] = $this->auth->id;
            $goods_spec_info['goods_name'] = $goods_info['goods_name'];
            unset($goods_spec_info['stock_num']);
            $result = $shoping_cart_model->add_data($goods_spec_info);
        }
        $result && $this->success('添加成功');
        $this->error('提交失败');
    }

    /*
     * 查看全部评论
     *
     */
    public function getAllEvaluate()
    {
        $params = $this->request->request();
        $where = array();
        switch ($params['type']) {
            case 1:
                $where['star_num'] = ['GT', 3];
                break;
            case 2:
                $where['star_num'] = 3;
                break;
            case 3:
                $where['star_num'] = array('LT', 3);
                break;
        }

        $where['goods_id'] = $params['goods_id'];
        $field = 'id,content,add_time,images,uid';
        $list = model('Comment')->select_page($where, $field, '', $params['page'], $params['pagesize']);
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['images'] = $this->setPlitJointImages($v['images']);
                $list[$k]['user_name'] = $this->user->getField(['id' => $v['uid']], 'username');
                $avatar = $this->user->getField(['id' => $v['uid']], 'avatar');
                $list[$k]['avatar'] = config('item_url') . $avatar;
            }
        }
        $where = ['goods_id' => $params['goods_id'], 'status' => 0];
        $all_count = model('Comment')->where($where)->count();
        $where['star_num'] = array('GT', 3);
        $praise_count = model('Comment')->where($where)->count();
        $where['star_num'] = array('LT', 3);
        $negative_count = model('Comment')->where($where)->count();
        $where['star_num'] = 3;
        $chinese_count = model('Comment')->where($where)->count();
        $list = empty($list) ? [] : $list;
        $this->success('获取成功', ['list' => $list,
            'all_count' => $all_count,
            'praise_count' => $praise_count, //好评
            'chinese_count' => $chinese_count, //中评
            'negative_count' => $negative_count,//差评
        ]);
    }


    /*
     * @获取商品详情
     * @param goods_id  商品id
     * @param uid 用户id
     * @param sku_id 规格id
     * @param activity_type 活动类型 0)普通1)限时2)今日特价3)拼团4)砍价
     * @param distributor 是否是分销商 0)不是 1)一级分销商 2)二级分销商
     * @param goods_status 状态  10)上架，20)下架
     */

    public function item_details()
    {
        $data = $this->request->request();
        !$data['goods_id'] && $this->error('goods_id为空');
        if (!empty($data['uid'])) {
            //判断是否是分销商
            $this->user->getUserInfo(['id' => $data['uid']], 'distributor');
        }
        //判断是否是什么类型商品
        switch ($data['activity_type']) {
            //普通商品
            case 0:
                $field = 'goods_id,goods_name,images,content,sales_actual,images,sales_initial,sales_actual,
                    goods_price,line_price';
                $item_info = $this->item->find_data(['goods_id' => $data['goods_id']], $field);

                empty($item_info) && $this->error('参数错误');
                $item_info['images'] = config('items_url') . $item_info['images'];
            case 1:
                $where['id'] = $data['goods_id'];
                $field = 'goods_id,goods_price,discount_price,start_time,end_time,upper_num,goods_name,goods_image,goods_spec_id';
                $item_info = $this->item_discount->find_data($where, $field);
                if ($item_info) {
                    $item_info['goods_image'] = config('items_url') . $item_info['goods_image'];
                }
                break;
            case 3:
                $field = 'goods_image,group_num,group_nums,goods_price,group_price,goods_name,goods_id,groupbuy_id';
                $item_info = $this->item_groupbuy->find_data(['id' => $data['goods_id']], $field);
                if ($item_info) {
                    $sku_field = 'stock,goods_spec_id,key_name,upper_num,group_price,id';
                    $item_spec = $this->item_groupbuy_sku->find_data(['goods_id' => $item_info['goods_id']], $sku_field);
                    $item_info['goods_image'] = config('items_url') . $item_info['goods_image'];
                    $item_info['content'] = $this->item->getField(['goods_id' => $item_info['goods_id']], 'content');
                }
                break;
            case 4:
                //砍价
                $field = 'limit_discount_id,goods_price,discount_price,start_time,
                end_time,goods_spec_id,goods_name,goods_image,goods_id,floor_price,highest_price,stock,number';
                $item_info = $this->item_cut_down->getLimitDiscountGoods(['id' => $data['goods_id']], $field);
                $item_spec = [];
                break;
        }

        $data['activity_type'] == 1 && $item_spec = $this->item_spec->getLitestoreGoodsSpec($item_info['goods_id'], 'key_name');

        $this->success('', ['item_info' => $item_info, 'item_spec' => $item_spec]);
    }

    /*
     * 获得购物车信息
     */
    public function getShopingCart()
    {
        $params = $this->request->request();
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');

        //获取购物车信息
        $shoping_cart_model = model('Shopingcart');
        $field = 'id,goods_id,goods_spec_id,goods_name,image,num,key_name,limit_num,status,type,activity_id';

        $where = ['uid' => $this->auth->id, 'status' => array('neq', 3)];
        $cart_list = $shoping_cart_model->getPageList($where, $field, 'createtime desc', $params['page'], $params['pagesize']);
        //拼接图片
//        $cart_list = $this->joinArrayImages($cart_list,'image');
        foreach ($cart_list as $k => $v) {
            $sku = $this->item_spec->find_data(['goods_spec_id' => $v['goods_spec_id']]);
            $cart_list[$k]['goods_price'] = $sku['goods_price'];
        }
        $cart_list = empty($cart_list) ? [] : $cart_list;
        $this->success('获取成功', ['list' => $cart_list]);
    }

    /*
     * 删除购物车信息
     * @param ids 信息id集合
     */
    public function delShopingCart()
    {
        $params = $this->request->request();
        !$params['ids'] && $this->error('ids不能为空');

        $where['id'] = array('IN', $params['ids']);
        $shoping_cart_model = model('Shopingcart');
        $shoping_cart_model->delete_data($where) && $this->success('删除成功');
        $this->error('删除失败');
    }

    /*
     * 购物车添加数量
     * @param type 1)增加 2)减少
     *
     */
    public function addShopingCartNums()
    {
        $params = $this->request->request();
        !$params['id'] && $this->error('id不能为空');
        !$params['num'] && $this->error('num不能为空');

        $where['id'] = $params['id'];
        $where['uid'] = $this->auth->id;

        $shoping_cart_model = model('Shopingcart');
        $info = $shoping_cart_model->find_data($where, 'num ,id ,goods_spec_id,goods_id');
        !$info && $this->error('id错误');

        switch ($params['type']) {
            case 1:
                //获取库存 判断库存
                $goods_sku = model('Litestoregoodsspec');
                $map = ['goods_spec_id' => $info['goods_spec_id'], 'goods_id' => $info['goods_id']];
                $inventory = $goods_sku->find_data($map, 'stock_num');

                $inventory['stock_num'] < ($params['num'] + $info['num']) && $this->error('204', '库存不足');

                $set = $shoping_cart_model->where($where)->setInc('num', $params['num']);
                break;
            case 2:
                $info['num'] == 0 && $this->error('数量错误');
                $set = $shoping_cart_model->where($where)->setDec('num', $params['num']);
                break;
        }
        $info = $shoping_cart_model->find_data($where, 'num');
        $set && $this->success('添加成功', $info);
        $this->error('添加失败');
    }
}