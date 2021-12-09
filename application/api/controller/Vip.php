<?php

namespace app\api\controller;

use app\admin\model\user\Level;
use app\common\controller\Api;
use app\common\model\Cmsblock;
use app\common\model\Config;
use app\common\model\Litestoreordership;
use app\common\model\UserLevel;
use think\Db;

class Vip extends Api
{
    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    /** @var \app\common\model\Litestoregoods */
    private $liteStoreGoods = null;

    protected function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->liteStoreGoods = model('Litestoregoods');

    }

    /**
     * Vip专区
     */
    public function index()
    {
        $field = 'goods_id,goods_name,image,content,goods_price,vip_receive,vip_level';

        $list = $this->liteStoreGoods->select_data(['status' => '9999'], $field);
        $domain = config('url_domain_root');
        foreach ($list as $k => $item) {
            $list[$k]['image'] = $item['image'] ? $domain . $item['image'] : '';
            $item->spec;
            $list[$k]['goods_spec_id'] = $item->spec[0]->goods_spec_id;
            unset($list[$k]['spec']);
        }

        $banner = Cmsblock::get(105);

        //1）2种配送方式  2）配送方式  3）自提
        $config = config('site.delivery_methods');
        $delivery_methods = $config[0] === "delivery" &&  $config[1] === "self_mention" ? 1 : ($config[0] === "delivery" ? 2 :3);
//        $level= Level::get(2);
//        $data = $level->upgrade_price_text;
        $article_to=Config::where('name','VIP_introduction')->find();
        $article=$article_to->value;
        $result = [
            'article'=>$article,
            'list' => $list,
            'banner' => $banner->image ? $domain . $banner->image : '',
            'phone' => config('site.kf_phone'),
            'is_buy_ordinary_vip' => !$this->auth->id ? 0 : $this->auth->is_buy_ordinary_vip,
            'buy_vip_goods' => $this->auth->isLogin() ? explode(',', $this->auth->buy_vip_goods) : [],
            'delivery_methods' => $delivery_methods,
        ];
        $this->success('success', $result);
    }

    /**
     * 套餐订单确认收货
     * @param int id 发货id
     * @throws \think\exception\DbException
     */
    public function confirmReceipt()
    {
        $id = $this->request->param('id');
        !$id && $this->error('id不能为空');
        $ship = Litestoreordership::get($id);
        !$ship && $this->error('发货记录不存在');
        $ship->receipt_status = 20;
        $ship->receipt_time = time();
        Db::startTrans();
        if ($ship->save()) {
            Db::commit();
            $this->success('确认收货成功');
        }
        Db::rollback();
        $this->error('确认收货失败');
    }
}