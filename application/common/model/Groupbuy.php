<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 拼团管理模型
 */
class Groupbuy extends Model
{
    protected $name = 'groupbuy';
    /**
     * 保存添加的拼团活动
     */

    public function addGroupbuy($params){
        unset($params['spec_type']);
        $params['status'] = 1;
        $params['add_time'] = time();
        return $this->allowField(true)->insertGetId($params);

    }

    /**
     * 按ID查询限时活动详情
     */
    public function getLimitDiscountInfoByID($id,$field='*'){
        return $this->field($field)->where('id','eq',$id)->find();
    }
    

    /**
     * 更新
     */
    public function editLimitDiscount($params,$condition){
        return $this->where($condition)->update($params);

    }


    /**
     * 读取拼团活动列表
     */
    public function getGroupbuyList($where,$sort='',$order='',$offset=0,$limit=0,$field='*'){
        $groupbuy_list = $this->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        return $groupbuy_list;
    }

    /**
     * 获取拼团活动扩展信息，包括状态文字和是否可编辑状态
     * @param array $xianshi_info
     * @return string
     *
     */


    /**
     * 获取规格信息
     */
    public function getGroupbuyManySpecData($spec_rel, $skuData,$groupbuy_goods_arr)
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
                    'goods_id' => $item['goods_id'],
                    'goods_no' => $item['goods_no'],
                    'goods_price' => $item['goods_price'],
                    'goods_weight' => $item['goods_weight'],
                    'line_price' => $item['line_price'],
                    'stock_num' => $item['stock_num'],
                    'spec_image' => $item['spec_image'],
                    'goods_spec_id'=>$item['goods_spec_id'],
                    'group_price'=>$groupbuy_goods_arr[$item['goods_spec_id']],
                ],
            ];
        }
        return ['spec_attr' => array_values($specAttrData), 'spec_list' => $specListData];
    }



    /**
     * 更新
     */
    public function editGroupbuy($params,$condition){
        unset($params['spec_type']);
        $params['start_time']=strtotime($params['start_time']);
        $params['end_time']=strtotime($params['end_time']);
        $params['status']=1;
        return $this->where($condition)->update($params);

    }

    /**
     * 删除
     */
    public function delGroupbuy($condition){
        $list=$this->getGroupbuyList($condition);
        $groupbuy_id_string = '';
        if(!empty($list)) {
            foreach ($list as $value) {
                $groupbuy_id_string .= $value['id'] . ',';
            }
        }
        //删除团购商品
        if($groupbuy_id_string !== '') {
            $model_groupbuy_goods = Model('Groupbuygoods');
            $model_groupbuy_goods->delGroupbuyGoods(array('groupbuy_id'=>array('in', $groupbuy_id_string)));
        }
        return $this->where($condition)->delete();
    }


    /*
     * 查询信息
     *
     * */
    public function find_data($where ,$field){
        return $this->where($where)->field($field)->find();
    }










}
