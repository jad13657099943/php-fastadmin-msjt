<?php

namespace app\common\model;
use think\Model;

class Litestoregoodsspec extends Model
{

    // 表名
    protected $name = 'litestore_goods_spec';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    /**
     * 批量添加商品sku记录
     * @param $goods_id
     * @param $spec_list
     * @return array|false
     * @throws \Exception
     */
    public function addSkuList($goods_id, $spec_list)
    {
        $data = [];
        foreach ($spec_list as $item) {
            $data[] = array_merge($item['form'], [
                'spec_sku_id' => $item['spec_sku_id'],
                'goods_id' => $goods_id,
            ]);
        }
        return $this->saveAll($data);
    }

    /**
     * 添加商品规格关系记录
     * @param $goods_id
     * @param $spec_attr
     * @return array|false
     * @throws \Exception
     */
    public function addGoodsSpecRel($goods_id, $spec_attr)
    {
        $data = [];
        array_map(function ($val) use (&$data, $goods_id) {
            array_map(function ($item) use (&$val, &$data, $goods_id) {
                $data[] = [
                    'goods_id' => $goods_id,
                    'spec_id' => $val['group_id'],
                    'spec_value_id' => $item['item_id'],
                ];
            }, $val['spec_items']);
        }, $spec_attr);
        $model = new Litestoregoodsspecrel;
        return $model->saveAll($data);
    }

    /**
     * 移除指定商品的所有sku
     * @param $goods_id
     * @return int
     */
    public function removeAll($goods_id)
    {
        $model = new Litestoregoodsspecrel;
        $model->where('goods_id','=', $goods_id)->delete();
        return $this->where('goods_id','=', $goods_id)->delete();
    }

    /**
     * @param 获取单个字段
     * @param $where
     * @param $field
     */
    public function data_column($where,$field){
        $this->where($where)->column($field);
    }
    /**
     * 获取单条商品SKU信息
     */
    public function getLitestoreGoodsSpecInfoByID($goods_spec_id, $field = '*') {
        return $this->field($field)->where('goods_spec_id','eq',$goods_spec_id)->find();
    }

    /*
     * 获取商品对应的sku信息
     */
    public function getLitestoreGoodsSpec($goods_id,$field ='*',$order='create_time desc'){
        return  $this->field($field)->where('goods_id' ,'eq',$goods_id)->order($order)->select();
    }

    /**
     * 获取单条商品SKU信息
     */
    public function getLitestoreGoodsSpecInfo($goods_id, $field = '*',$order='goods_spec_id desc') {
        return $this->field($field)->where('goods_id','eq',$goods_id)->find();
    }

    /**
     * 获取单条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function find_data($where = [], $field = '*', $order = 'create_time desc')
    {
//        dump($where);
        $item_sku =  $this->where($where)->field('*')->order($order)->find();
  

//        dump($item_sku->toArray());
        $item_sku['stock_num'] = $item_sku['stock_num'] > 0 ? $item_sku['stock_num'] : 0;
        $is_marketing = model('Litestoregoods')->where(['goods_id' => $item_sku['goods_id']])->value('is_marketing');
//        dump($is_marketing);
        if($is_marketing == 1 || $is_marketing == 2) {
            $item_sku['line_price'] = $item_sku['goods_price'];
            $item_sku['goods_price'] = $item_sku['marketing_price'];
            $item_sku['vip_price'] =$item_sku['marketing_price'];

        }
        return $item_sku;
    }

    /**
     * 获取单条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function select_data($where = [], $field = '*', $order = 'create_time desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }


    /**
     * 修改信息
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update_data($where = [],$data = [])
    {
        if (empty($where)) {
            return false;
        }
        return $this->where($where)->update($data);
    }


    /**
     * 支付后 添加销量 减少库存  取消订单 添加库存  减少销量
     * @param  $staus 1) 支付  2）取消订单
     * @param  $goods_id 商品id
     * @param  $goods_spec_id 规格id
     * @param  $number 数量
     * */
    public function updateSpec($goods_spec_id, $goods_id, $number, $status = '1')
    {
        $where = ['goods_spec_id' => $goods_spec_id, 'goods_id' => $goods_id];
        $info = $this->find_data($where, 'stock_num,goods_sales');
        switch ($status) {
            case 1: //添加销量 减少库存
                if ($number > $info['stock_num'])
                    return false;

                $update = ['stock_num' => $info['stock_num'] - $number, 'goods_sales' => $info['goods_sales'] + $number];
                break;

            case 2: //取消订单 添加库存
                if ($number > $info['goods_sales'])
                    return false;

                $update = ['stock_num' => $info['stock_num'] + $number, 'goods_sales' => $info['goods_sales'] - $number];
                break;
        }
        $update['stock_num'] = $update['stock_num'] < 0 ? 0 :$update['stock_num'];
        $update['goods_sales'] = $update['goods_sales'] < 0 ? 0 :$update['goods_sales'];
        $update_goods_spec = $this->update_data($where, $update);

        //修改销量
        $model = new Litestoregoods();
        $update_goods = $model->update_sales_actual($goods_id, $number, $status);
        if ($update_goods && $update_goods_spec)
            return true;
        else
            return false;
    }

}
