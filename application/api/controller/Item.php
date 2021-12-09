<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\admin\model\Litestorefreight;
use app\common\model\Litestoreorder;
use think\cache\driver\Redis;
use app\common\model\Shopingcart as ShopingcartModel;
use app\common\model\School as SchoolModel;

/**
 * 商品控制器
 */
class Item extends Api
{
    protected $noNeedLogin = ['getAllEvaluate', 'getItemLists', 'getItemDetail', 'getItemSku', 'ItemDetail', 'limit_goods',
        'getGroupbuyDetail', 'getLimitDiscountDetail', 'getCateList', 'checkGoodsSpec', 'getGoodsSpec', 'sku', 'test','vip_goods'];
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
        $this->visit = model('Visit');
        $this->limit_discount_goods_model = model('Limitdiscountgoods');
        $this->litestoreGoods = model('Litestoregoods');
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
                if ($v['stock_num'] == 0) {
                    $position = strpos($v['spec_sku_id'], $sku_id);
                    $skus_id = $position == 0 ? $sku_id . '_' : '_' . $sku_id;
//                    $row[$k]['id'] = str_replace($skus_id,'',$v['spec_sku_id']);
                    $row[] = str_replace($skus_id, '', $v['spec_sku_id']);
                }

            }
        } else
            $this->error('商品不存在');

        $this->success('获取成功', $row);
    }

    /**
     * 获取会员专区商品
     */
    public function vip_goods(){
        $page = $this->request->request('page');
        $pagesize = $this->request->request('pagesize');
        $order = 'goods_sort desc';
        $field = ' goods_price , goods_id,goods_name,image,line_price , marketing_goods_price , is_marketing ,marketing_id,spec_type,vip_price,poster';
        $where['is_school'] = 20;
        $list = $this->item->getLitestoreGoods($where, $field, $order, $page, $pagesize);
        //获取VIP轮播图
        $ab_list = model('Cmsblock')->select_data(['status' => 'normal','cate_id'=> '32'], 'id,name,image');
        foreach ($ab_list as  $k => $v ){
            $ab_list[$k]['image'] = config('item_url') . $v['image'];
        }
        $this->success('获取成功',['list' =>$list,'data'=>$ab_list]);
    }



    /**
     * 获取商品列表  搜索 分类首页 首页搜索
     * 需要字段
     * 图片 名称 价格
     * 搜索进入 人气排序 销量排序 价格排序
     *
     *
     * @param  $page
     * @param  $pagesize
     * @param  $keyword 搜索字段
     * @param  $sales_order 1）销量降序
     * @param  $price_order 1）价格降序  2）价格升序
     * @paaram $goods_sort_order 1）人气降序
     * @param  $category_id  分类ID
     *
     *
     * 专
     * 限时抢购进入 轮播图 时间分类 市场价 剩余数量 倒计时  已经采购数量
     * 今日特价 轮播图 市场价
     */

    public function getItemLists()
    {
        $data = $this->request->request();
        $page = $this->request->request('page');
        $pagesize = $this->request->request('pagesize');
//        $school_id = $this->request->request('school_id');

        !is_numeric($page) && $this->error('page不能为空');
        !is_numeric($pagesize) && $this->error('pagesize不能为空');


        $where = $cate_list = $second_cate_list = [];
        $order = 'goods_sort desc';

        //排序
        $sales_order = $this->request->request('sales_order'); //销量排序
        $order = $sales_order == 1 ? 'sales_initial desc' : $order;

        $price_order = $this->request->request('price_order'); //价格排序
        if ($price_order) {
            $order = $price_order == 1 ? 'goods_price desc' : 'goods_price asc';
        }
        $goods_sort_order = $this->request->request('goods_sort_order'); //人气排序
        $order = $goods_sort_order == 1 ? 'goods_sort asc' : $order;


        //分类查询
        $category_id = $this->request->request('category_id');
        $second_cate_id = $this->request->request('second_cate_id');

        $cate_id = $this->item_cate->where(['pid' => $category_id, 'status' => 10])->column('id');//获取二级分类下商品id

        $all_cate_id = $this->item_cate->where(['status' => 10])->column('id');

        if (!$cate_id)
            $second_cate_id = $category_id;
        $where['category_id'] = empty($second_cate_id) ? ['IN', $cate_id] : $second_cate_id;
        //获取分类
        $cate_list = $this->item_cate->getLitestoreCategoryList(['pid' => 0, 'status' => 10], 'id,name')->toArray();
        //获取二级分类
        $second_cate_list = $this->item_cate->getLitestoreCategoryList(['pid' => $category_id, 'status' => 10], 'id,name')->toArray();

        if (!$category_id && !$second_cate_id) {
            $where['category_id'] = ['IN', $all_cate_id];
        }

        $arr = ['id' => 0, 'name' => '全部'];
        array_unshift($second_cate_list, $arr);

        $keyword = $this->request->request('keyword');
        if ($keyword) { //首页搜索
            $where['goods_name'] = ['LIKE', '%' . $data['keyword'] . '%'];
            unset($where['category_id']);
            $uid = $this->auth->id;
            if ($uid) //添加搜索记录
                $this->addKeyword($keyword, $uid);
        }

        //获取商品信息
        $field = 'goods_price ,goods_id,goods_name,image,line_price , marketing_goods_price , is_marketing ,marketing_id,spec_type,vip_price,is_news';
        $where['is_marketing'] = 0;
        $where['is_new']=['neq',2];
        $where['is_school'] = 10;

        $list = $this->item->getLitestoreGoods($where, $field, $order, $page, $pagesize);

//        dump($list);die;
        //获取商品在购物车的数量
        foreach ($list as $k => $v) {
            $shopping_cart_where = ['goods_id' => $v['goods_id'], 'uid' => $this->auth->id];
//            $school_id && $shopping_cart_where['school_id'] = $school_id;

            $list[$k]['shopping_cart_num'] = $this->auth->id ? model('Shopingcart')->find_data($shopping_cart_where, 'id,num')
                : 0;
        }
        $info = $this->item_cate->find_data(['id' => $category_id], 'classification_image');
        $banner = empty($info['classification_image']) ? '' : config('url_domain_root') . $info['classification_image'];

        $this->success('获取成功',
            ['banner' => $banner = empty($keyword) ? $banner : [], //专区 今日特价轮播图
                'list' => $list, //商品列表
                'cate_list' => $cate_list = empty($keyword) ? $cate_list : [], //分类列表
                'second_cate_list' => $second_cate_list = empty($keyword) ? $second_cate_list : [],
//                'ShopingCart' => $school_id ? ShopingcartModel::getShopingCartTotalPrice(['school_id' => $school_id, 'uid' => $this->auth->id]) : [],
            ]
        );
    }


    /**
     * 选择多规格商品规格
     * @param int goods_id 商品id
     *
     */
    public function getGoodsSpec()
    {
        $params = $this->request->request();
        !$params['goods_id'] && $this->error('goods_id不能为空');


        //获取商品信息
        $item_info = $this->item->find_data(['goods_id' => $params['goods_id']], 'goods_price,spec_type,stock_num,image,goods_price_section,is_news');
        $item_info['spec_type'] == '10' && $this->error('该商品是单规格商品');
        //获取多规格信息
        $item_infos=$this->item_spec->find_data(['goods_id' => $params['goods_id']],'new_price,nums');
        $item_info['new_price']=$item_infos['new_price'];
        $item_info['nums'] =$item_infos['nums'];
        $params['school_id'] && $key_name = SchoolModel::getSchoolName($params['school_id']);
        $where['goods_id'] = $params['goods_id'];

        $item_info['item_sku'] = model('Litestoregoodsspecrel')->select_spec_names($where ,$key_name);
        $item_info['spec_image'] = $item_info['image'] ? config('url_domain_root') . $item_info['image'] : '';
        unset($item_info['image']);
        //规则模型
        $item_info['spec_name'] = model('Litestoregoodsspecrel')->fieid_spec_names(['goods_id' => $params['goods_id']], 'spec_id') . '选择';

        $item_info = empty($item_info) ? [] : $item_info;
        $this->success('success', ['info' => $item_info]);

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


    /**
     * 商品详情
     * @param goods_id 商品id
     *
     * */
    public function getItemDetail()
    {

        $data = $this->request->request();
//        $data['uid'] = $this->auth->id;
        $item_info = $this->ItemDetail($data['goods_id'], $this->auth->id);
        $item_info['ShopingCart'] = $data['school_id'] ? ShopingcartModel::getShopingCartTotalPrice(['school_id' => $data['school_id'], 'uid' => $this->auth->id]) : [];

        $this->success('获取成功', $item_info);
    }


    //************************************商品详情函数开始*************//

    /**
     * 商品详情- 不是接口
     * @param $goods_id 商品id
     * @param $uid 用户id
     * @param $marketing_type 1) 团购  2）限时秒杀
     * onfig('default_fh_address') 默认发货地
     */
    public function ItemDetail($goods_id, $uid = '')
    {
        !$goods_id && $this->error('goods_id为空');

        $redis = new Redis();
        if ($redis->has('ItemDetail' . $goods_id)) {
            $item_info = $redis->get('ItemDetail' . $goods_id);

        } else {

            $item_info = $this->item->find_data(['goods_id' => $goods_id, 'goods_status' => 10], '*');

            if ($item_info['is_news'] == 2) {
                $item_info['new_price'] = model('Litestoregoodsspec')->where(['goods_id' => $item_info['goods_id']])->value('new_price');
                $item_info['nums'] = model('Litestoregoodsspec')->where(['goods_id' => $item_info['goods_id']])->value('nums');
            }



            !$item_info && $this->error('商品已下架');

            $this->item->where(['goods_id' => $goods_id])->setInc('hits');

            $item_info['place_delivery'] = $item_info['place_delivery'] ? $item_info['place_delivery'] : config('default_fh_address');
            $item_info['param'] = model('Itemattr')->select_data(['goods_id' => $goods_id, 'type' => 1], 'name title ,value');

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
            $item_info['tel'] = Config('site.tel');

            $item_info['is_collect'] = $item_info['shopping_cart_num'] = 0; //默认没有收藏


            $coupon_info = model('Couponrecord')->find_data(['status' => 1], 'title', 'coupon_price desc');
            $item_info['coupon_info'] = $coupon_info ? $coupon_info['title'] : '暂无优惠券';

            if ($item_info['spec_type'] == 20) { //获取多规格信息
                $item_info['item_sku'] = model('Litestoregoodsspecrel')->select_spec_names(['goods_id' => $item_info['goods_id']]);
            } else {
                $item_info['item_sku'] = [];

                $spec_info = $this->item_spec->find_data(['goods_id' => $goods_id], 'goods_spec_id');
                $item_info['goods_spec_id'] = $spec_info['goods_spec_id'];
            }

            //获取商品规格总库存
            $item_info['stock_nums'] = $item_info['stock_num'];

            //运费
            //  $model = new Litestorefreight();
//        $item_info['freight'] = $item_info['freight_desc'] = $model->find_one_data(config('site.freight'));
            $item_info['freight'] = 0.00;

            $item_info['sales_initial'] = $item_info['is_marketing'] == 0 ? $item_info['sales_initial'] + $item_info['sales_actual'] : $item_info['sales_initial'];

            //购买轮播
            $item_info['buyList'] = !$this->buyWheelPlanting($goods_id) ? [] : $this->buyWheelPlanting($goods_id);

            // $item_info['line_price'] = ($item_info['is_marketing'] > 0  && $item_info['marketing_goods_price'] > 0) ? $item_info['goods_price'] : $item_info['line_price'];
            $item_info['goods_price'] = ($item_info['is_marketing'] > 0 && $item_info['marketing_goods_price'] > 0) ? $item_info['marketing_goods_price'] : $item_info['goods_price'];


            //1）2种配送方式  2）配送方式  3）自提
            $config = config('site.goods_delivery_methods');
            $item_info['delivery_methods'] = $config[0] === "goods_delivery" && $config[1] === "goods_self_mention" ? 1 : ($config[0] === "goods_delivery" ? 2 : 3);
            $redis->set('ItemDetail' . $goods_id, $item_info, 300);
        }

        //判断用户是否被限制购买
//        $item_info['is_limit_buy'] = $item_info['is_marketing'] == 2 && $uid ? $this->limit_goods($goods_id, $uid) : 1;
        $item_info['is_collect'] = $item_info['shopping_cart_num'] = 0;


        if ($uid) {
            //判断是否收藏
            $count_collect = model('Collect')->getCount(['uid' => $uid, 'goods_id' => $goods_id, 'status' => 1]);
            $item_info['is_collect'] = $count_collect > 0 ? 1 : 0;

            //获取购物车数量
            $item_info['shopping_cart_num'] = model('Shopingcart')->getShopingCartNum(['uid' => $uid]);

            $item_info['nickname'] = $this->auth->nickname;
            $item_info['avatar'] = $this->auth->avatar;
            $item_info['QRcode'] = $this->auth->invite_qrcode ? config('url_domain_root') . $this->auth->invite_qrcode : controller('Distribution')->GenerateQRcode();

        }

        $item_info['is_shopingcart'] =  0; //config('site.is_shopingcart'); //0)开启购物车  1）关闭购物车
        //判断是否登入 记入有效访问数据
        if ($this->auth->id)
            $this->visit($this->auth->id ,$goods_id);
        return $item_info;
    }

    //****************************结束****************************//

    /**
     * 购买轮播 根据商品id
     * @param $goods_id
     * @return array
     */
    public function buyWheelPlanting($goods_id)
    {
        $order = new Litestoreorder();
        $order_info = $order->where(['pay_status' => 20, 'is_del' => 0])->field('id,user_id')
            ->with(['orderGoods' => function ($query) use ($goods_id) {
                $query->where(['goods_id' => $goods_id])->withField('goods_id');
            }, 'userAddress' => function ($query) {
                $query->withField('user_id,name,province,city,region');
            }, 'user' => function ($query) {
                $query->withField('avatar,id');
            }])->orderRaw('rand()')->limit(10)->select();
//        dump($order_info->toArray());die;
        $buyList = [];
        foreach ($order_info as $item) {
            if (!empty($item['orderGoods']->toArray())) {
                $details = $item['userAddress']['province'] . $item['userAddress']['city'] . $item['userAddress']['region'];
                $name = $item['userAddress']['name'];
                $name = substr($name, 0, -6);
                $res['note'] = $details . $name . '**购买了商品';
                $res['avatar'] = $item['user']['avatar'];
                $buyList[] = $res;
            }
        }
        return $buyList;
    }

    public function sku($good_id)
    {
        $result = model('Litestoregoodsspecrel')->select_spec_names(['goods_id' => $good_id]);
        $this->success('', $result);
    }

    /*
     * 编辑多规格数组
     * */
    public function edit_spec_array($array)
    {
        $map = [];
        //if (is_array($array)) {
        foreach ($array as $k => $v) {
            $map[] = $v['spec'];
            unset($array[$k]['pivot'], $v['createtime'], $array[$k]['spec']);
        }

        $map = $this->assoc_unique($map, 'id');
        foreach ($map as $key => $value) {
            unset($map[$key]['createtime']);

            $sub = [];
            foreach ($array as $kk => $vv) {
                if ($vv['spec_id'] == $value['id']) {
                    $sub[] = $vv;
                }
                $map[$key]['sub'] = $sub;
            }
        }
        //  }
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
     *
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
        $wheres['goods_id'] =['eq',$params['goods_id']];
        $item_skus =$this->item->find_data($wheres,'is_news,goods_id');
        $item_sku['is_news'] =$item_skus['is_news'];
        if (input('type') == 5) {
            $item_sku['goods_price'] = $item_sku['line_price'];
        }
        $item_sku['spec_image'] = $item_sku['spec_image'] ? config('item_url') . $item_sku['spec_image'] : '';

        $this->success('获取成功', $item_sku);
    }


    /**
     * 加入购物车 ---目前只做普通商品
     * @param $goods_id 商品ID
     * @param $num 加入数量
     * @param $goods_spec_id 规格ID
     * @param $type 1）正常商品  2）限时抢购 3）今日特价
     * @param $activity_id
     * $this->limit_goods($goods_id , $uid)
     *
     * */
    public function addShopingCart()
    {
        $params = $this->request->request();
        $uid = $this->auth->id;
        !$uid && $this->error('请登录后操作');
        !$params['goods_id'] && $this->error('goods_id不能为空');

        !$params['num'] && $this->error('num不能为空');
        !$params['goods_spec_id'] && $this->error('good_spec_id不能为空');
        $params['school_id'] = $params['school_id'] ? $params['school_id'] : 0;

        !$params['type'] && $this->error('type不能为空');
        if ($params['type'] == 2)
            !$params['activity_id'] && $this->error('activity_id 不能为空');

        $wheres['goods_id'] = $params['goods_id'];
        //判断是否下架
        $goods_info = $this->item->find_data($wheres, 'goods_name,image ,spec_type,is_marketing,upper_num');
        !$goods_info && $this->error('商品已下架');

        $shoping_cart_model = model('Shopingcart');
        //查看购物车中是否有该商品
        $where = [
            'goods_id' => $params['goods_id'],
            'goods_spec_id' => $params['goods_spec_id'],
            'uid' => $uid,
            'school_id' => $params['school_id'],
        ];
        $cart_info = $shoping_cart_model->find_data($where, 'id,num,upper_num');
        if ($cart_info['upper_num'] && ($cart_info['num'] + $params['num']) > $cart_info['upper_num'])
            $this->error('添加数量超过限购数量');

        //获取规格信息
        $item_spec_where['goods_id'] = $params['goods_id'];
        $item_spec_where['goods_spec_id'] = $params['goods_spec_id'];
        $spec_info = $this->item_spec->find_data($item_spec_where, '*');

        if ($cart_info) {
            if ($spec_info) {
                $update_item_nums = $cart_info['num'] + $params['num']; //同一商品,原有的数量+新传递过来的数量。
                if ($update_item_nums > $spec_info['stock_num']) {
                    $this->error('商品库存不足!');
                }

                $data['num'] = $update_item_nums;
                $result = $shoping_cart_model->where(array('id' => $cart_info['id']))->update($data);
            }
        } else {
            if ($goods_info['is_marketing'] == 2) { //2）限时抢购
                //判断该活动商品是否取消
                $activity_info = $this->item_discount->find_data($wheres, 'status');
                $activity_info['status'] == 0 && $this->error('活动已到期');
            }

            $params['uid'] = $uid;
            //判断库存是否充足
            $goods_spec_info = $this->item_spec->find_data($item_spec_where, 'stock_num,key_name,spec_sku_id,spec_image');
            $goods_spec_info['stock_num'] < $params['num'] && $this->error('商品库存不足');
            $params['spec_sku_id'] = $goods_spec_info['spec_sku_id'];
            $params['goods_name'] = $goods_info['goods_name'];
            $params['type'] = $goods_info['is_marketing'] ? $goods_info['is_marketing'] : 1;
            $params['upper_num'] = $goods_info['upper_num'] ? $goods_info['upper_num'] : 0;
            $params['image'] = $goods_spec_info['spec_image'];
            $params['key_name'] = $goods_spec_info['key_name'];

            $result = $shoping_cart_model->add_data($params);
        }
        if ($result) {
            $this->success('添加成功');
        } else {
            $this->error('提交失败');
        }
    }

    /**
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
            default:
                $where = '';
        }

        $where['goods_id'] = $params['goods_id'];
        $field = 'id,content,add_time,images,uid';
        $list = model('Comment')->select_page($where, $field, 'add_time desc', $params['page'], $params['pagesize']);
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['images'] = $this->setPlitJointImages($v['images']);
                $list[$k]['user_name'] = $this->user->getField(['id' => $v['uid']], 'username');
                $avatar = $this->user->getAvatar($v['uid']);
//                $list[$k]['avatar'] = $avatar ? strpos($avatar, 'http') ? $avatar : config('item_url') . $avatar : '';
                $list[$k]['avatar'] = $avatar;
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
            $distributor = $this->user->getUserInfo(['id' => $data['uid']], 'distributor');
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
            case 2:
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

        if ($data['activity_type'] == 1) {
            $item_spec = $this->item_spec->getLitestoreGoodsSpec($item_info['goods_id'], 'key_name');
        }
        $this->success('', ['item_info' => $item_info, 'item_spec' => $item_spec]);


    }

    /**
     * 获得购物车信息
     */
    public function getShopingCart()
    {
        $uid = $this->auth->id;
        $params = $this->request->request();
        !$params['page'] && $this->error('page不存在');
        !$params['pagesize'] && $this->error('pagesize不存在');
        !$uid && $this->error('请登录后操作');

        //获取购物车信息
        $shoping_cart_model = model('Shopingcart');
        $field = 'id,goods_id,goods_spec_id,goods_name,image,num,key_name,upper_num,status,type,activity_id,type';
        $cart_list = $shoping_cart_model->getPageList(['uid' => $uid, 'status' => array('neq', 3)], $field, 'createtime desc', $params['page'], $params['pagesize']);
        if ($cart_list) {
            foreach ($cart_list as $k => $v) {
                $sku = $this->item_spec->find_data(['goods_spec_id' => $v['goods_spec_id']]);
                if ($v['type'] == 1) {
                    $goodsa= new \app\common\model\Litestoregoods();
                    $goods_info = $goodsa->where('goods_id',$v['goods_id'])->find();
                    if ($goods_info['is_news'] == '1'){
                        $cart_list[$k]['goods_price'] = $this->auth->vip_type != 0 ? $sku['vip_price'] : $sku['goods_price'];
                    }else  if($goods_info['is_news'] == 2){
                        if ($sku['nums'] <= $v['num'] ){
                            $cart_list[$k]['goods_price'] = $this->auth->vip_type != 0 && $v['type'] == 1 ? $sku['vip_price'] : $sku['new_price'];
                        }else{
                            $cart_list[$k]['goods_price'] = $this->auth->vip_type != 0 ? $sku['vip_price'] : $sku['goods_price'];
                        }
                    }
//                    $cart_list[$k]['goods_price'] = $this->auth->vip_type != 0 ? $sku['vip_price'] : $sku['goods_price'];

                } else {
                    //查询秒杀获取时间 不住服务时间 下架
                    if ($v['activity_id'] && $v['type'] == 2) {
                        $limit_discount_info = $this->limit_discount_goods_model->find_data(['limit_discount_id' => $v['activity_id'], 'goods_id' => $v['goods_id']], 'start_time,end_time');
                        if ($limit_discount_info['start_time'] > time() || $limit_discount_info['end_time'] < time()) {
                            $cart_list[$k]['status'] = 20;
                        }
                    }
                    $cart_list[$k]['goods_price'] = $sku['marketing_price'];
                }
                $cart_list[$k]['stock_num'] = $sku['stock_num'];
                $cart_list[$k]['new_price'] = $sku['new_price'];
                $cart_list[$k]['nums'] = $sku['nums'];
                $cart_list[$k]['is_news'] =$goods_info['is_news'];
            }


        }
        //获取新品商品推荐到购物车下面
        $goods_field = 'goods_id,goods_name,image,goods_price,line_price,marketing_goods_price,is_marketing ,marketing_id,spec_type,vip_price,is_news';
        $where['is_delete'] = 0;
        $where['is_marketing'] = 0;
        $where['vip_level'] = 0;
        $where['status'] = '20';
        $where['is_school'] = '10';
        $item_list = $this->litestoreGoods->getLitestoreGoods($where, $goods_field, 'createtime desc', $params['page'], $params['pagesize']);

        //1）2种配送方式  2）配送方式  3）自提
        $config = config('site.goods_delivery_methods');
        $delivery_methods = $config[0] === "goods_delivery" && $config[1] === "goods_self_mention" ? 1 : ($config[0] === "goods_delivery" ? 2 : 3);
        $this->success('success', ['cart_list' => $cart_list, 'item_list' => $item_list, 'delivery_methods' => $delivery_methods]);
    }




    /*
     * 删除购物车信息
     * @param ids 信息id集合
     */
    public function delShopingCart()
    {
        $uid = $this->auth->id;
        $params = $this->request->request();
        !$params['ids'] && $this->error('ids不能为空');
        !$uid && $this->error('请登录后操作');

        $where['id'] = array('IN', $params['ids']);
        $shoping_cart_model = model('Shopingcart');
        if ($shoping_cart_model->delete_data($where))
            $this->success('删除成功');
        else
            $this->error('删除失败');
    }

    /*
     * 购物车添加数量
     * @param type 1)增加 2)减少
     *
     */
    public function addShopingCartNums()
    {
        $uid = $this->auth->id;
        $params = $this->request->request();
        !$params['id'] && $this->error('id不能为空');
        !$params['num'] && $this->error('num不能为空');
        $where['id'] = $params['id'];
        $where['uid'] = $uid;
        //验证是否存在
        $shoping_cart_model = model('Shopingcart');
        if (!$info = $shoping_cart_model->find_data($where, 'num ,id ,goods_spec_id,goods_id ,upper_num'))
            $this->error('id错误');

        if ($info['upper_num'] && $info['upper_num'] < ($params['num'] + $info['num']))
            $this->error('购买数量不能超过限购数量');

        if ($params['type'] == 1) {

            //获取库存 判断库存
            $goods_sku = model('Litestoregoodsspec');
            $map = ['goods_spec_id' => $info['goods_spec_id'], 'goods_id' => $info['goods_id']];
            $inventory = $goods_sku->find_data($map, 'stock_num');

            if ($inventory['stock_num'] < ($params['num'] + $info['num'])){
                $this->error('购买数量不能超过库存');
            }


            $set = $shoping_cart_model->where($where)->setInc('num', $params['num']);

        } elseif ($params['type'] == 2) {
            if ($info['num'] == 0)
                $this->error('数量错误');
            $set = $shoping_cart_model->where($where)->setDec('num', $params['num']);

        }

        $infos = $shoping_cart_model->find_data($where, 'num');

        $goods_sku = model('Litestoregoodsspec');
        $map = ['goods_spec_id' => $info['goods_spec_id'], 'goods_id' => $info['goods_id']];
        $inventory = $goods_sku->find_data($map, 'stock_num,new_price,nums,goods_price');

        $goodsa= new \app\common\model\Litestoregoods();
        $goodsas = $goodsa->where('goods_id',$info['goods_id'])->field('is_news')->find();

        if ($goodsas['is_news'] == 2 ){
            if ( $inventory['nums'] <= $infos['num']){
                $infos['goods_price'] =$inventory['new_price'];
            }else{
                $infos['goods_price'] =$inventory['goods_price'];
            }
        }else{
            $infos['goods_price'] =$inventory['goods_price'];
        }
        if ($set)
            $this->success('添加成功', $infos);
        else
            $this->error('添加失败');
    }


    /**
     * 不是接口
     * 限制一个用户购买一个商品 限制N天内购买N次
     * site.limit_day
     * site.limit_number
     * @param $goods_id
     * @return array
     */
    public function limit_goods($goods_id, $uid)
    {
        ///  $limituser_mode = model('Limituser');
        //  if ($limituser_mode->check_count(['goods_id' => $goods_id, 'uid' => $uid])) {
        $limit_day = Config('site.limit_day');
        $limit_number = Config('site.limit_number');

        //查询最近购买次数 购买时间
        $limit_time = strtotime('-' . $limit_day . 'day');
        $where = ['createtime' => ['egt', $limit_time], 'goods_id' => $goods_id, 'user_id' => $uid];

        $order_goods_model = model('Litestoreordergoods');
        $order_ids = $order_goods_model->where($where)->column('order_id');

        if ($order_ids != null) {
            $order = model('Litestoreorder'); //查询规定时间能购买次数 'pay_status' => 20,

            $where['id'] = ['in', $order_ids];
            unset($where['goods_id']);
            $order_count = $order->where($where)->count();

            if ($order_count >= $limit_number) {
                return 2;
            }
        }
        //   }
        return 1;
    }


    public function test()
    {
        $array = array(array('a1', 'a2'), array('b1', 'b2'));
        /*foreach ($array as $key => $value) {
            if ($key == '0') {
                unset($array[$key]);
            }
            //或者删除二维数组中二维中的元素
            if ($key == '0') {
                unset($array[$key][0]);
            }
        }*/

        $this->success('添加成功', $array);

    }
}