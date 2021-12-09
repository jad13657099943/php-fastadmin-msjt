<?php


namespace app\api\services\msjt;


use app\api\model\msjt\Collection;
use app\api\model\msjt\Goods;
use app\api\model\msjt\Hot;
use app\api\model\msjt\Order;
use app\api\model\msjt\OrderGoods;
use app\api\model\msjt\Search;
use app\api\services\PublicService;

class GoodsService extends PublicService
{
    /**
     * 商品列表
     * @param $params
     * @return false|string
     * @throws \think\exception\DbException
     */
    public function lists($uid, $params)
    {
        $where['status'] = 1;

        if (!empty($params['type_id'])) $where['type_id'] = $params['type_id'];

        if (!empty($params['name'])) {

            $where['name'] = ['like', '%' . $params['name'] . '%'];

            $service = new SearchService();
            if (!empty($uid)) {
                $service->search($uid, $params['name']);
            }
            $service->addHotNum($params['name']);
        }

        if (!empty($params['is_recommend_data'])) $where['is_recommend_data'] = $params['is_recommend_data'];

        $filed = 'id,name,simages,configjson,createtime';
        $list = Goods::wherePaginate($where, $filed, $params['limit'] ?? 10, 'weigh');

        foreach ($list->items() as $item) {
            $item->configjson = json_decode($item->configjson, true);
        }

        return $this->success('商品列表', $list);
    }


    /**
     * 商品详情
     * @param $id
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info($uid, $id)
    {
        $info = Goods::whereFind(['id' => $id]);
        $info['sales'] = $this->sales($id);
        $info['configjson'] = json_decode($info['configjson'], true);
        if ($info['typesetting'] == 2) {
            $info['configjson'] = $this->typeSetting($info['configjson']);
        }
        if (empty($uid)) {
            $info['collection'] = 0;
        } else {
            $info['collection'] = Collection::whereFind(['user_id' => $uid, 'type' => 1, 'collection_id' => $id], 'id')->id;
        }
        $service = new NumService();
        $info['sales_num'] += $service->goodsNum($id);
        return $this->success('商品详情', $info);
    }

    /**
     * 套盒排版
     * @param $json
     * @return array
     */
    private function typeSetting($json)
    {
        $data = [];
        $hang = [];
        $num = 0;
        $wei = 0;
        foreach ($json as $k => $item) {
            if (strpos($item['name'], '套盒')) {
                $data[$num] = $item;
                $wei = $num;
                $num++;
            } else if (!strpos($item['name'], 'A')) {
                $data[$wei]['json'][] = $item;
            } else {
                $item['name'] = str_replace('A', '', $item['name']);
                $hang[] = $item;
            }
        }
        $data[]['json'] = $hang;
        return $data;
    }

    /**
     * 商品销量
     * @param $id
     * @return int|string
     * @throws \think\Exception
     */
    public function sales($id)
    {
        $order_no = Order::whereColumn(['status' => 6], 'order_no');
        return OrderGoods::where(['order_no' => ['in', $order_no], 'goods_id' => $id])->count();
    }
}