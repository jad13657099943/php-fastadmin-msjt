<?php

namespace app\admin\model;

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
            $spec_sku_ids = explode('_', $item['spec_sku_id']);
            $spec_sku_names = model('litestore_spec_value')->where(['id' => ['in', $spec_sku_ids]])->column('spec_value');
            if (isset($item['form']['goods_spec_id'])) {
                unset($item['form']['goods_spec_id']);
            }
            sort($spec_sku_ids);
            $data[] = array_merge($item['form'], [
                'spec_sku_id' => implode('_',$spec_sku_ids),
                'goods_id' => $goods_id,
                'key_name' => implode(' ', $spec_sku_names),
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
                    'spec_value_id' => $item['item_id']];

            }, $val['spec_items']);
        }, $spec_attr);

        $model = new Litestoregoodsspecrel;
        return $model->saveAll($data);
    }

    /**
     * 批量添加商品sku记录
     * @param $goods_id
     * @param $spec_list
     * @return array|false
     * @throws \Exception
     */
    public function editSkuList($goods_id, $spec_list)
    {
      //  dump($spec_list); exit;
        foreach ($spec_list as $item) {
            $spec_sku_ids = explode('_', $item['spec_sku_id']);
            $spec_sku_names = model('litestore_spec_value')->where(['id' => ['in', $spec_sku_ids]])->column('spec_value');
            $data = $item['form'];
            sort($spec_sku_ids);
            $data['spec_sku_id'] = implode('_',$spec_sku_ids);
            $data['goods_id'] = $goods_id;
            $data['key_name'] = implode(' ', $spec_sku_names);

            if (!$data['goods_spec_id']) {
                $data['create_time'] = time();
                $goods_spec_id = $this->insertGetId($data);
            } else{
                $goods_spec_id = $data['goods_spec_id'];
                $map[] = $data;
            }
            $goods_spec_ids[] = $goods_spec_id;
        }
        //批量更新
        if($map){
            $this->saveAll($map);
        }
        
        $this->where(['goods_spec_id' => ['NOT IN' , $goods_spec_ids] ,'goods_id' => $goods_id])->delete();
        return true;
    }



    /**
     * 添加商品规格关系记录
     * @param $goods_id
     * @param $spec_attr
     * @return array|false
     * @throws \Exception
     */
    public function editGoodsSpecRel($goods_id, $spec_attr)
    {

        $model = new Litestoregoodsspecrel;
        $model->where('goods_id', '=', $goods_id)->delete();
        $data = [];
        array_map(function ($val) use (&$data, $goods_id) {
            array_map(function ($item) use (&$val, &$data, $goods_id) {

                $data[] = ['goods_id' => $goods_id,
                    'spec_id' => $val['group_id'],
                    'spec_value_id' => $item['item_id']];
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
        $model->where('goods_id', '=', $goods_id)->delete();
        return $this->where('goods_id', '=', $goods_id)->delete();
    }

    /**
     * 获取单条商品SKU信息
     */
    public function getLitestoreGoodsSpecInfoByID($goods_spec_id, $field = '*')
    {
        return $this->field($field)->where('goods_spec_id', 'eq', $goods_spec_id)->find();
    }

    /**
     * 获取单条商品SKU信息
     */
    public function getLitestoreGoodsSpecInfo($goods_id, $field = '*', $order = 'goods_spec_id desc')
    {
        return $this->field($field)->where('goods_id', 'eq', $goods_id)->order($order)->find();
    }


    /*
     * 统计商品总库存
     * $param $goods_id 商品ID
     * */
    public function getGooodsTotalnum($goods_id)
    {
        return $this->where(['goods_id' => $goods_id])->sum('stock_num');
    }


    /**
     * 获取多条数据
     *
     *
     */
    public function select_data($where, $field)
    {
        return $this->where($where)->field($field)->select();
    }
}
