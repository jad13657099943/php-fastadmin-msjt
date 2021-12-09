<?php

namespace app\admin\model;

use think\Model;

class Litestoregoods extends Model
{
    // 表名
    protected $name = 'litestore_goods';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'spec_type_text',
        'deduct_stock_type_text',
        'goods_status_text',
        'is_delete_text',
    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['goods_sort' => $row[$pk]]);
        });
    }

    /**
     * 图片获取器
     * @param $value
     * @return string
     */
//    public function getImagesAttr($value)
//    {
//        $value = explode(',', $value);
//        foreach ($value as $k => $v) {
//            $value[$k] = config('item_url') . $v;
//        }
//        return $value;
//    }

    public function getSpecTypeList()
    {
        //return ['10' => __('Spec_type 20')];
        return ['10' => __('Spec_type 10'), '20' => __('Spec_type 20')];
    }

    public function getDeductStockTypeList()
    {
        return ['10' => __('Deduct_stock_type 10'), '20' => __('Deduct_stock_type 20')];
    }

    public function getGoodsStatusList()
    {
        return ['10' => __('Goods_status 10'), '20' => __('Goods_status 20')];
    }

    public function getIsDeleteList()
    {
        return ['0' => __('Is_delete 0'), '1' => __('Is_delete 1')];
    }


    public function getSpecTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['spec_type']) ? $data['spec_type'] : '');
        $list = $this->getSpecTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDeductStockTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deduct_stock_type']) ? $data['deduct_stock_type'] : '');
        $list = $this->getDeductStockTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGoodsStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['goods_status']) ? $data['goods_status'] : '');
        $list = $this->getGoodsStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsDeleteTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_delete']) ? $data['is_delete'] : '');
        $list = $this->getIsDeleteList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function category()
    {
        return $this->belongsTo('litestorecategory', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function freight()
    {
        return $this->belongsTo('Litestorefreight', 'delivery_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    /**
     * 关联商品规格表
     */
    public function spec()
    {
        return $this->hasMany('Litestoregoodsspec', 'goods_id', 'goods_id');
    }

    /**
     * 关联商品规格关系表
     */
    public function specRel()
    {
        return $this->belongsToMany('Litestorespecvalue', 'litestore_goods_spec_rel', 'spec_value_id', 'goods_id');
    }

    /**
     * 计算显示销量 (初始销量 + 实际销量)
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getGoodsSalesAttr($value, $data)
    {
        return $data['sales_initial'] + $data['sales_actual'];
    }

    /**
     * 添加商品规格
     * @param $data
     * @param $isUpdate
     * @throws \Exception
     */
    public function addGoodsSpec($data, $params, $specparams, $isUpdate = false)
    {

        // 更新模式: 先删除所有规格
        $model = new Litestoregoodsspec;
        //  $isUpdate && $model->removeAll($this['goods_id']);
        // 添加规格数据
        if ($data['spec_type'] === '10') {
            $specparams['spec_image'] = $data['image'];
            // 单规格
            if ($isUpdate) {
                $model->save($specparams, ['goods_id' => $this['goods_id']]);
            } else{

                $this->spec()->save($specparams);
            }


        } else if ($data['spec_type'] === '20') {

            if ($isUpdate) { //编辑信息
                // 添加商品与规格关系记录

                $model->editGoodsSpecRel($this['goods_id'], $params['spec_attr']);
                // 添加商品sku
                $model->editSkuList($this['goods_id'], $params['spec_list']);
            } else { //添加
                // 添加商品与规格关系记录
                $model->addGoodsSpecRel($this['goods_id'], $params['spec_attr']);
                // 添加商品sku
                $model->addSkuList($this['goods_id'], $params['spec_list']);
            }

        }

        //更新所有价格 市场价
        $this->editGoodsPriceSction($this['goods_id'], $data['spec_type']);
    }


    /*
  * 修改商品价格 区间价
  * @param $goods_id
  * @param $spec_type 是否是多规格
  * */
    public function editGoodsPriceSction($goods_id, $spec_type)
    {
        $goods_id = $goods_id ? $goods_id : $this['goods_id'];

        $model = new Litestoregoodsspec;

        //默认图片
        $goods_info = $this->getLitestoreGoodsInfoByID($goods_id, 'image');


        $goods_spec = $model->where(['goods_id' => $goods_id, 'spec_image' => ''])->column('goods_spec_id');

        if ($goods_spec) {
            $model->save(['spec_image' => $goods_info['image']], ['goods_spec_id' => ['in', $goods_spec]]);
        }


        //goods_price 商品最低价  line_price 商品市场价  line_price_section 市场区间价  goods_price_section 商品区间价
        $save = [];
        switch ($spec_type) {
            case 10: //单规格
                $litestoregoodsspec = $model->getLitestoreGoodsSpecInfo($goods_id, 'goods_price,vip_price,line_price,marketing_price');
                $save['goods_price'] = $save['goods_price_section'] = $litestoregoodsspec['goods_price'];

                //市场价格 市场区间价
                $save['marketing_goods_price'] = $save['marketing_goods_price_section'] = $litestoregoodsspec['marketing_price'] > 0 ?
                    $litestoregoodsspec['marketing_price'] : $litestoregoodsspec['goods_price'];

                $save['vip_price'] = $save['vip_price_section'] = $litestoregoodsspec['vip_price'];//vip区间价

                $save['line_price'] = $save['line_price_section'] = $litestoregoodsspec['line_price'];
                break;
            case 20://多规格
                $goods_price_min = $model->getLitestoreGoodsSpecInfo($goods_id, 'goods_price ,marketing_price', 'marketing_price asc,goods_price asc');
                $save['goods_price'] = $goods_price_min['goods_price'];
                $save['marketing_goods_price'] = $goods_price_min['marketing_price'] > 0 ? $goods_price_min['marketing_price'] : $goods_price_min['goods_price'];


                $line_price_min = $model->getLitestoreGoodsSpecInfo($goods_id, 'line_price', 'line_price asc');
                $line_price_max = $model->getLitestoreGoodsSpecInfo($goods_id, 'line_price', 'line_price desc');
                $save['line_price'] = $line_price_min['line_price'];

                $vip_price_min = $model->getLitestoreGoodsSpecInfo($goods_id, 'vip_price', 'vip_price asc');
                $vip_price_max = $model->getLitestoreGoodsSpecInfo($goods_id, 'vip_price', 'vip_price desc');
                $save['vip_price'] = $vip_price_min['vip_price'];


                $goods_price_max = $model->getLitestoreGoodsSpecInfo($goods_id, 'goods_price ,marketing_price', 'marketing_price desc, goods_price desc');
                $goods_max_price = $goods_price_max['goods_price'];
                $marketing_goods_max_price = $goods_price_max['marketing_price'] > 0 ? $goods_price_max['marketing_price'] : $goods_price_max['goods_price'];

                $save['goods_price_section'] = $save['goods_price'] . '-' . $goods_max_price;
                $save['marketing_goods_price_section'] = $save['marketing_goods_price'] . '-' . $marketing_goods_max_price;
                $save['line_price_section'] = $line_price_min['line_price'] . '-' . $line_price_max['line_price'];
                $save['vip_price_section'] = $vip_price_min['vip_price'] . '-' . $vip_price_max['vip_price'];
                break;
        }

//        dump($save);die;
        //统计商品总库存
        $stock_num = $model->getGooodsTotalnum($goods_id);
        $save['stock_num'] = $stock_num;
        return $this->allowField(true)->save($save, ['goods_id' => $goods_id]);
//        return $this->allowField(true) -> save(['goods_id' => $goods_id ,'stock_num'=>$stock_num],$save);
    }

    public function removesku()
    {
        // 删除商品sku
        (new Litestoregoodsspec)->removeAll($this['goods_id']);
    }

    /**
     * 获取规格信息
     * param $marketing_type 1)是营销活动
     */
    public function getManySpecData($spec_rel, $skuData, $marketing_type)
    {
        // spec_attr
        $specAttrData = [];
        foreach ($spec_rel as $item) {
            if (!isset($specAttrData[$item['spec_id']])) {
                $specAttrData[$item['spec_id']] = [
                    'group_id' => $item['spec']['id'],
                    'group_name' => $item['spec']['spec_name'],
                    'spec_items' => [],
                ];
            }
            $specAttrData[$item['spec_id']]['spec_items'][] = [
                'item_id' => $item['pivot']['spec_value_id'],
                'spec_value' => $item['spec_value'],
            ];
        }

        // spec_list
        $specListData = [];
        foreach ($skuData as $item) {
            $specListData[] = [
                'goods_spec_id' => $item['goods_spec_id'],
                'spec_sku_id' => $item['spec_sku_id'],
                'rows' => [],
                'form' => [
                    'goods_no' => $item['goods_no'],
                    'goods_price' => $item['goods_price'],
                    'marketing_price' => $item['marketing_price'],
                    'vip_price' => $item['vip_price'],
                    'goods_weight' => $item['goods_weight'],
                    'line_price' => $item['line_price'],
                    'stock_num' => $item['stock_num'],
                    'upper_num' => $item['upper_num'],
                    'spec_image' => \think\Image::setthumb($item['spec_image']),
                    'goods_id' => $item['goods_id'],
                    'goods_spec_id' => $item['goods_spec_id'],
                    'new_price'=>$item['new_price'],
                    'nums'=>$item['nums']
                ],
            ];
        }
        return ['spec_attr' => array_values($specAttrData), 'spec_list' => $specListData, 'marketing_type' => $marketing_type];
    }

    /**
     *根据GOODS_ID查询商品信息
     */
    public function getLitestoreGoodsInfoByID($goods_id, $field = '*')
    {
        return $this->field($field)->where('goods_id', 'eq', $goods_id)->find();
    }

    /**
     * 查询多条数据
     * @param array $where
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function select_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->select();
    }

    /**
     * 修改器
     * @param $value
     * @return string
     */
    public function setuppershelfTimeAttr($value)
    {
        return strtotime($value);
    }

    /**
     * 更新数据
     * @param $where
     * @param $field
     * @return Litestoregoods
     */
    public function update_data($where, $field)
    {
        return $this->where($where)->update($field);
    }

}
