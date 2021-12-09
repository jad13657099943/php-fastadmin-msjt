<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\Config;
use app\common\model\Litestorecoupon;
use app\common\model\Litestoreorder;
use app\common\model\User as UserModel;
use think\Db;
use app\common\model\Area;
use think\cache\driver\Redis;
use app\api\library\WXBizDataCrypt as WXBizDataCrypt;

/**
 * 首页接口
 */
class Index extends Api
{

    protected $noNeedLogin = ['index', 'activity_details', 'test', 'sendError', 'guidePages', 'search', 'keyword_list', 'index2', 'visit', 'text'];
    protected $noNeedRight = ['*'];


    // protected $failException = true;
    public function _initialize()
    {
        parent::_initialize();
        $this->activity = model('ActivityCate');
        $this->litestoreGoods = model('Litestoregoods');
        $this->litestoreGoodsSpec = model('Litestoregoodsspec');
        $this->search = model('Searchrecord');
        $this->block = model('Cmsblock');
        $this->config = model('Config');
        $this->visit = model('Visit');
        $this->article = model('Cmsarchives');//文章模型表
        $this->Litestorecategory = model('Litestorecategory');

    }


    //解析手机号码
    public function getPhone()
    {
        $encryptedData = $this->request->post('encryptedData');
        $iv = $this->request->post('iv');
        $config = config('wx');
        $pc = new WXBizDataCrypt($config['appid'], $this->auth->session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        $data = json_decode($data, true);
        if (!$this->auth->mobile && $data['purePhoneNumber'])
            UserModel::update(['mobile' => $data['purePhoneNumber']], ['id' => $this->auth->id]);

        $this->success("获取成功", $data);
    }


    /**
     * 首页
     * $this->request 这样调用基类函数
     */

    public function index2()
    {
        $this->request->request();
        $uid = $this->auth->id;

        $redis = new Redis();

        //判断redis是否存在
        if ($redis->get('home_list')) {
            $list = $redis->get('home_list');

        } else {
            //获取首页广告 分类图片信息
            $ad_list = model('Cmsblock')->select_data(['status' => 'normal'], 'id,name,image,cate_id,url,jump_status');
            if ($ad_list != null) {
                $banner = $soreItemDatas = $bargainDatas = [];
                foreach ($ad_list as $k => $v) {
                    $ad_list[$k]['image'] = config('item_url') . $v['image'];
                    switch ($v['cate_id']) {
                        case 19: //轮播图
                            $result = model('Cmsblock')->checkAdListJump($v['url'], $v['jump_status']);
                            $v['goods_id'] = $result['goods_id'];
                            $v['activity_id'] = $result['activity_id'];
                            $banner[] = $v;
                            break;
                        case 21://获取首页分类
                            unset($v['url'], $v['cate_id']);
                            unset($v['images']);
                            $soreItemDatas[] = $v;
                            break;
                        case 34:
                            $homepage_chart = $ad_list[$k]['image'];
                            break;
                    }
                }
            }
            unset($ad_list);
            //商城头条
            $news = $this->article->select_data(['status' => 'normal', 'channel_id' => 26], 'id,title', 'createtime desc');



            //会员套餐
            $vipItem = $this->litestoreGoods->select_data(['is_recommend' => 1], 'goods_id,goods_price,goods_name,image,vip_level');
            foreach ($vipItem as $k => $item) {
                $vipItem[$k]['image'] = empty($item['image']) ? '' : config('item_url') . $item['image'];
                $vipItem[$k]['goods_spec_id'] = $item->spec[0]->goods_spec_id;
                unset($vipItem[$k]['spec']);
            }

            //1）2种配送方式  2）配送方式  3）自提
            $config = config('site.delivery_methods');
            $delivery_methods = $config[0] === "delivery" && $config[1] === "self_mention" ? 1 : ($config[0] === "delivery" ? 2 : 3);
        }

        //限时秒杀
        $limit_discount = model('Limitdiscount');
        $limit_where = ['status' => 1, 'start_time' => ['lt', time()], 'end_time' => ['gt', time()]];
        $discount = $limit_discount->where($limit_where)->find();//获取限时秒杀时间
        $miao = $discount->end_time;

        if ($redis->has('spike_item')) {
            $spike_item = $redis->get('spike_item');
        } else {
            if ($miao > time()) {
                //获取限时秒杀商品
                $this->Limitdiscountgoods = model('Limitdiscountgoods');
                $field = 'id,goods_price,line_price,goods_name,poster image,goods_id,stock_num,upper_num,limit_discount_id';
                $limit_where = ['status' => 10, 'start_time' => ['lt', time()], 'end_time' => ['gt', time()]];
                $spike_item = $this->Limitdiscountgoods->select_page_data($limit_where, $field,
                    $this->request->request('page'), $this->request->request('pagesize'), 'id desc');

                foreach ($spike_item as $k => $v) {
                    //查询商品规格id
                    $spike_item[$k]['spec_sku_id'] = $this->litestoreGoodsSpec->data_column(['goods_id' => $v['goods_id']], 'spec_sku_id');
                }
                $spike_item = $this->setArrayImage($spike_item, 'image');
            } else {
                $spike_item = [];
            }
            $redis->set('spike_item', $spike_item, 60);
        }

        //$redis->rm('item_list' . $this->request->request('pagesize') . $this->request->request('page'));
        //商品列表存储redis
        if ($redis->has('item_list' . $this->request->request('pagesize') . $this->request->request('page'))) {
            $item_list = $redis->get('item_list' . $this->request->request('pagesize') . $this->request->request('page'));
        } else {
            //获取精品推荐下面的商品
            $where['is_delete'] = '0';
            $where['is_marketing'] = 0;
            $where['vip_level'] = 0;
            $where['status'] = '20';
            $goods_field = 'goods_id,goods_name,image,goods_price,line_price,marketing_goods_price,is_marketing ,marketing_id,spec_type,vip_price';

            $order = 'goods_sort desc';
            $item_list = $this->litestoreGoods->getLitestoreGoods($where, $goods_field, $order, $this->request->request('page'), $this->request->request('pagesize'));
            $redis->set('item_list' . $this->request->request('pagesize') . $this->request->request('page'), $item_list, 300);
        }

        if (!$redis->has('home_list')) {
            $list = ['banner' => $banner, //轮播图   -3600
                'homepage_chart' => $homepage_chart, //首页横图-3600
                'soreItemDatas' => $soreItemDatas, //分类-3600
                'news' => $news,//商城头条-3600
                'vipItem' => $vipItem,//vip套餐-3600
                'distribution_apply_money' => config('site.distribution_apply_money'), //申请代理费用-3600
                'delivery_methods' => $delivery_methods,//3600
                "tel" => config('site.kf_phone'), //客服电话-3600,
            ];
            $redis->set('home_list', $list, 3600);
        }


        $list['spike_item'] = $spike_item; //限时秒杀
        $list['miao'] = $miao;
        $list['item_list'] = $item_list;//商品列表
        $list['is_buy_ordinary_vip'] = !$this->auth->id ? 0 : $this->auth->is_buy_ordinary_vip;

        //申请状态 0）待审核 1）审核通过 2）审核失败 3)未审核或没传uid-60
        $list['is_store'] = empty($this->auth->id) ? 3 : controller('Store')->is_Apply($this->auth->id, 1);
        //判断可以不可以买秒杀东西
        $this->success('success', $list);
    }

    public function text()
    {
//        $iv = base64_decode($this->request->param('iv'));
//        $encryptedData = base64_decode($this->request->param('encryptedData'));
//        $data = openssl_decrypt($encryptedData, "AES-128-CBC", base64_decode($this->auth->session_key), 1, $iv);
//        $data = json_decode($data, true);
//        dump($data);

        $longitude = $this->request->request('longitude');
        $latitude = $this->request->request('latitude');
        dump($latitude);
        dump($longitude);
        $shuju = Area::getAreaFromLngLat($longitude, $latitude);
        dump($shuju->toArray());
        die;
    }


    /**
     * 记入有效访问人数
     */
    public function visit()
    {
        //记入访问次数 （先查询今日是否记入过）
        $day_time = strtotime(date('Y-m-d'));
        $data_id = $this->visit->where(['create_time' => ['gt', $day_time], 'status' => 2, 'user_id' => $this->auth->id])->value('id');

        if ($data_id) { //修改最后一次访客时间
            $this->visit->save(['create_time' => time()], ['id' => $data_id, 'user_id' => $this->auth->id]);
        } else {
            //添加一条记录
            $visit_data = ['status' => 2, 'visit' => 1, 'user_id' => $this->auth->id];
            $this->visit->save($visit_data);
        }
        return true;
    }

//    }

    public function index()
    {
        $address = $this->request->request('address');
        //获取首页广告 分类图片信息
        $ad_list = model('Cmsblock')->select_data(['status' => 'normal'], 'id,name,image,cate_id,url,jump_status');
        if ($ad_list != null) {

            $banner = $soreItemDatas = $bargainDatas = [];
            $enjoy = $background = '';
            foreach ($ad_list as $k => $v) {
                $ad_list[$k]['image'] = config('item_url') . $v['image'];
                switch ($v['cate_id']) {
                    case 19: //轮播图
                        $result = model('Cmsblock')->checkAdListJump($v['url'], $v['jump_status']);
                        $v['goods_id'] = $result['goods_id'];
                        $v['activity_id'] = $result['activity_id'];
                        $banner[] = $v;
                        break;
                    case 21://获取首页分类
                        unset($v['url'], $v['cate_id']);
                        unset($v['images']);
                        $soreItemDatas[] = $v;
                        break;
                    case 34:
                        $homepage_chart = $ad_list[$k]['image'];
                        break;

                }
            }
        }
        unset($ad_list);

        //商城头条
        $news = $this->article->select_data(['status' => 'normal', 'channel_id' => 26],'id,title', 'createtime desc');

        //限时秒杀
        $limit_discount = model('Limitdiscount');
        $limit_where = ['status' => 1, 'start_time' => ['lt', time()], 'end_time' => ['gt', time()]];
        $discount = $limit_discount->where($limit_where)->find();//获取限时秒杀时间
        $miao = $discount->end_time;
        if ($miao > time()) {
            //获取限时秒杀商品
            $this->Limitdiscountgoods = model('Limitdiscountgoods');
            $field = 'id,goods_price,line_price,goods_name,poster,image,goods_id,stock_num,upper_num,limit_discount_id';
            $limit_where = ['status' => 10, 'start_time' => ['lt', time()], 'end_time' => ['gt', time()]];
            $spike_item = $this->Limitdiscountgoods->select_page_data($limit_where, $field,
                $this->request->request('page'), $this->request->request('pagesize'), 'id desc');
            foreach ($spike_item as $k => $v) {
                //查询商品规格id
                $spike_item[$k]['spec_sku_id'] = $this->litestoreGoodsSpec->data_column(['goods_id' => $v['goods_id']], 'spec_sku_id');
            }

            $spike_item = $this->setArrayImage($spike_item, 'image');

        } else {
            $spike_item = [];
        }

        //会员套餐
        $vipItem = $this->litestoreGoods->select_data(['is_recommend' => 1], 'goods_id,goods_price,goods_name,image,vip_level,poster');
        foreach ($vipItem as $k => $item) {
            $vipItem[$k]['image'] = empty($item['image']) ? '' : config('item_url') . $item['image'];
            $vipItem[$k]['poster'] = empty($item['poster']) ? '' : config('item_url') . $item['poster'];
            $vipItem[$k]['goods_spec_id'] = $item->spec[0]->goods_spec_id;
            unset($vipItem[$k]['spec']);
        }
        //获取精品推荐下面的商品
        $where['is_delete'] = '0';
        $where['is_marketing'] = 0;
        $where['vip_level'] = 0;
        $where['status'] = '20';
        $where['is_new']=['neq',2];
        $goods_field = 'goods_id,goods_name,image,goods_price,line_price,marketing_goods_price,is_marketing ,marketing_id,spec_type,vip_price,is_news';

        $order = 'goods_sort desc';
        $where['is_school'] = '10';
        $item_list = $this->litestoreGoods->getLitestoreGoods($where, $goods_field, $order, $this->request->request('page'), $this->request->request('pagesize'));

        $is_store = empty($this->auth->id) ? 3 : controller('Store')->is_Apply($this->auth->id, 1);

        //1）2种配送方式  2）配送方式  3）自提
        $config = config('site.delivery_methods');
        $delivery_methods = $config[0] === "delivery" && $config[1] === "self_mention" ? 1 : ($config[0] === "delivery" ? 2 : 3);
//        dump($this->auth->id);die;


       // if ($this->auth->id) //统计访客人数
         //   $this->visit();
    //获取分类类型
        $field = 'id,name,image,classification_image';
        $lassificationname = $this->Litestorecategory->getLitestoreCategoryLists(['recommendation' => 1], $field)->toArray();
        foreach ($lassificationname as $key => $v) {
            $field = 'goods_id,goods_name,image,goods_price,is_marketing,marketing_id,is_news';
            $lassificationname[$key]['name_list'] = $this->Litestorecategory->lassificationnamelist($field)
                ->where('category_id', $v['id'])
                ->where('is_new','neq','2')
                ->where('goods_status',10)
//                    ->where('is_recommend','1') //用来看是否有那个推荐
                    ->order('goods_id desc')->limit(6)
                ->select()->toArray();;
        }
        foreach ($lassificationname as $k=>$v){

            foreach ($v['name_list'] as $key=>$val){
                if ($val['is_news'] == 2) {
                    $lassificationname[$k]['name_list'][$key]['new_price'] = model('Litestoregoodsspec')->where(['goods_id' => $val['goods_id']])->value('new_price');
                    $lassificationname[$k]['name_list'][$key]['nums'] = model('Litestoregoodsspec')->where(['goods_id' => $val['goods_id']])->value('nums');
                }

            }

        }


        $is_news = $this->litestoreGoods->field('goods_id,goods_name,image,goods_price,line_price,marketing_goods_price,is_marketing ,marketing_id,spec_type,vip_price,is_news')->where('is_new',2)->select();
         $mobile = config('site.kf_phone');
        $this->success('success',
            [
                'banner' => $banner, //轮播图
                'homepage_chart' => $homepage_chart, //首页横图
                'soreItemDatas' => $soreItemDatas, //分类
                'lassificationname'=>$lassificationname,//类型商品
                'news' => $news,//商城头条
                'miao' => $miao,//限时抢购倒计时
                'spike_item' => $spike_item,//显示抢购列表
                'vipItem' => $vipItem,//vip套餐
                'item_list' => $item_list, //商品列表
                'is_store' => $is_store,//申请状态 0）待审核 1）审核通过 2）审核失败 3)未审核或没传uid
                'is_buy_ordinary_vip' => !$this->auth->id ? 0 : $this->auth->is_buy_ordinary_vip,
                'buy_vip_goods' => $this->auth->isLogin() && $this->auth->buy_vip_goods ? explode(',', (string)$this->auth->buy_vip_goods) : [],
                'distribution_apply_money' => config('site.distribution_apply_money'), //申请代理费用
                'delivery_methods' => $delivery_methods,
                "tel" => config('site.kf_phone'), //客服电话
                "coupon_recommend" => Litestorecoupon::checkCoupon($this->auth->id), //推荐优惠券
                'is_new'=>$is_news,
                'mobile'=>$mobile,
            ]);
    }
    public function yznew(){
        $uid =$this->auth->id;
        $ord=new Litestoreorder();
        $data=$ord->where('user_id',$uid)->select();
        if (!$data){

            $this->success('新人可以购买,但是只可以选择一个');
        }else{
            $this->error('不是新人');
        }
    }

    public function getRecommendGoods()
    {
        $page = $this->request->param('page');
        $pageSize = $this->request->param('page_size');

        $where['is_delete'] = '0';
        $where['is_marketing'] = 0;
        $where['vip_level'] = 0;
        $where['status'] = '20';
        $goods_field = 'goods_id,goods_name,image,goods_price,line_price,marketing_goods_price,is_marketing ,marketing_id,spec_type,vip_price';

        $item_list = $this->litestoreGoods->getLitestoreGoods($where, $goods_field, 'createtime desc', $page, $pageSize);
        $this->success('获取成功', ['list' => $item_list]);
    }

    public function search()
    {
        $keyword = $this->request->param('keyword');
        $order = $this->request->param('order');
        $page = $this->request->param('page');
        $pageSize = $this->request->param('page_size');

        !$keyword && $this->error('keyword不能为空');
        !$page && $this->error('page不能为空');
        !$pageSize && $this->error('page_size不能为空');

        $where = ['goods_name' => ['like', "%$keyword%"]];
        $field = 'goods_id,goods_name,goods_price,vip_price,image';
        $list = $this->litestoreGoods->select_page($where, $field, $page, $pageSize, $order);

        if ($this->auth->isLogin()) {
            $this->search->search($keyword, $this->auth->id);
        }
        $this->success('搜索成功', ['list' => $list]);
    }

    /**
     * 搜索记录
     * @return array
     */
    public function keyword_list()
    {
        $user_id = $this->auth->id;
        //历史搜索
        if (empty($user_id)) {
            $history = [];
        } else {
            $history = $this->search->field('search_name')->where(['uid' => $user_id, 'type' => 1, 'serch_type' => 0])->order('id desc')->limit('10')->select();
        }
        //热门搜索
        $hotkey = $this->search->field('search_name')->where(['serch_type' => 0, 'type' => 2])->order('ordid desc')->limit('10')->select();
        $this->success('success', ['history' => $history, 'hotkey' => $hotkey]);
    }

    /**
     * 删除历史搜索
     * @return array
     */
    public function delhistory()
    {
        $user_id = $this->auth->id;
        if (empty($user_id)) {
            $this->error('user_id参数错误');
        }
        $op = $this->search->where(['uid' => $user_id, 'serch_type' => 0, 'type' => 1])->delete();
        if ($op) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }

    }

    public function sendError()
    {
        $this->error('请登录后操作');
    }

    /**
     * 引导页接口
     */
    public function guidePages()
    {
        //获取引导页图片组
        $result = $this->block->find_data(['cate_id' => 30], 'images');
        $images = explode(',', $result['images']);

        //拼接域名
        foreach ($images as $k => $img) {
            $images[$k] = config('item_url') . $img;
        }
        $this->success('获取成功', ['guidePage' => $images]);
    }
}
