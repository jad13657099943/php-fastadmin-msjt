<?php

namespace app\common\model;

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
  /*  protected $append = [
        'spec_type_text',
        'deduct_stock_type_text',
        'goods_status_text',
        'is_delete_text'
    ];*/


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['goods_sort' => $row[$pk]]);
        });
    }

    
/*    public function getSpecTypeList()
    {
        return ['10' => __('Spec_type 10'),'20' => __('Spec_type 20')];
    }     

    public function getDeductStockTypeList()
    {
        return ['10' => __('Deduct_stock_type 10'),'20' => __('Deduct_stock_type 20')];
    }     

    public function getGoodsStatusList()
    {
        return ['10' => __('Goods_status 10'),'20' => __('Goods_status 20')];
    }     

    public function getIsDeleteList()
    {
        return ['0' => __('Is_delete 0'),'1' => __('Is_delete 1')];
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
    }*/

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
        return $this->hasMany('Litestoregoodsspec','goods_id','goods_id');
    }

    /**
     * 关联商品规格关系表
     */
    public function specRel()
    {
        return $this->belongsToMany('Litestorespecvalue', 'litestore_goods_spec_rel','spec_value_id','goods_id');
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
    public function addGoodsSpec(&$data,$params,$specparams,$isUpdate = false)
    {
        // 更新模式: 先删除所有规格
        $model = new Litestoregoodsspec;
        $isUpdate && $model->removeAll($this['goods_id']);
        // 添加规格数据
        if ($data['spec_type'] === '10') {
            // 单规格
            $this->spec()->save($specparams);
        } else if ($data['spec_type'] === '20') {
            // 添加商品与规格关系记录
            $model->addGoodsSpecRel($this['goods_id'],$params['spec_attr']);
            // 添加商品sku
            $model->addSkuList($this['goods_id'],$params['spec_list']);
        }
    }




    public function removesku(){
        // 删除商品sku
        (new Litestoregoodsspec)->removeAll($this['goods_id']);
    }
    /**
     * 获取规格信息
     */
    public function getManySpecData($spec_rel, $skuData)
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
                    'goods_weight' => $item['goods_weight'],
                    'line_price' => $item['line_price'],
                    'stock_num' => $item['stock_num'],
                    'upper_num'=> $item['upper_num'],
                    'spec_image' => $item['spec_image'],
                ],
            ];
        }
        return ['spec_attr' => array_values($specAttrData), 'spec_list' => $specListData];
    }

    //获取商品列表
    public function getLitestoreGoodsList($condtion,$field,$page=0,$pagesize=0,$order='goods_id desc'){
        $where['goods_status'] = '10';
        return $this->field($field)->where($condtion)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
    }

    //获取商品列表 （多种排序）
    public function getLitestoreGoods($where = [] , $field = '*', $order='goods_id desc' , $page = 0, $pagesize = 10)
    {
        $where['goods_status'] = '10';
        $where['is_delete'] = '0';

        $list = $this->field($field)->where($where)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
        if($list !=null){
            foreach ($list as $k=>$v){
                $info = $this->marketing($v['marketing_id'] , $v['is_marketing'] , $v['goods_id']);
                 if($info){
                     $list[$k]['goods_price'] = $v['marketing_goods_price'] > 0 ? $v['marketing_goods_price'] : $v['goods_price'];
                     $list[$k]['marketing_id'] = $info['id'];
                 }else {
                     $list[$k]['marketing_id'] = $v['is_marketing'] = 0;
                 }
                if ($v['spec_type'] == 10){
                    $list[$k]['goods_spec_id'] = model('Litestoregoodsspec')->where(['goods_id' => $v['goods_id']])->value('goods_spec_id');
                }
                if ($v['is_news'] == 2){
                    $list[$k]['new_price'] = model('Litestoregoodsspec')->where(['goods_id' => $v['goods_id']])->value('new_price');
                    $list[$k]['nums'] = model('Litestoregoodsspec')->where(['goods_id' => $v['goods_id']])->value('nums');
                }
            }
        }
        return $this->joinArrayImages($list,'image');
    }

    //获取商品列表
    public function select_page($condtion,$field,$page=0,$pagesize=0,$order='goods_id desc'){
        $where['goods_status'] = '10';
        $list = $this->field($field)->where($condtion)->order($order)->limit(($page-1)*$pagesize, $pagesize)->select();
        return $this->joinArrayImages($list,'image');
    }


    /**
     * 判断营销商品是否过去 活动是否结束
     * @param marketing_id 活动ID
     * @param marketing_type 活动类型
     * @param gooods_id 商品id
     */
    public function marketing($marketing_id , $marketing_type ,$goods_id){
        $where['goods_id'] = $goods_id;
        $where['status'] = 10;
        switch ($marketing_type){
            case 1:
                $where['groupbuy_id'] = $marketing_id;
                $info = model('Groupbuygoods')->find_data($where ,'id');
                break;
            case 2:
                $where['limit_discount_id'] = $marketing_id;
                $info = model('Limitdiscountgoods')->find_data($where ,'id');
                if($info)
                    $info['id'] =  $marketing_id;

                break;
            case 3:
                $where['cut_down_id'] = $marketing_id;
                $info = model('Cutdowngoods')->find_data($where ,'id');
                break;
            default:
                $info = false;
                break;
        }
        return $info ? $info : 0;
    }


    /*
     * 获取单条信息
     *
     */
    public function find_data($where = [],$field='*')
    {
        $where['goods_status'] = '10';
        $where['is_delete'] = '0';
        return $this->field($field)->where($where)->find();
    }

    /*
    * 获取一维数组单条字段
    *
    */
    public function getField($where= [],$data)
    {
        $where['goods_status'] = '10';
        $where['is_delete'] = '0';
        return $this->where($where)->value($data);
    }


    /*
     * 获取单条信息 排除field字段
     *
     */
    public function find_field_data($where = [],$field='*')
    {
        $where['is_delete'] = '0';
        //field 排除某些字段
        $item_info = $this->field($field,true)->where($where)->find();
        $market_status = $this->marketing($item_info['marketing_id'] , $item_info['is_marketing'] , $item_info['goods_id']);

        if($market_status){
            $item_info['line_price'] = $item_info['goods_price'];
            $item_info['line_price_section'] = $item_info['goods_price_section'];
            $item_info['goods_price'] = $item_info['marketing_goods_price']> 0 ? $item_info['marketing_goods_price'] : $item_info['goods_price'];
            $item_info['goods_price_section'] = $item_info['marketing_goods_price_section'] > 0 ? $item_info['marketing_goods_price_section'] : $item_info['goods_price_section'];
        }else{
            $item_info['marketing_id'] = $item_info['is_marketing'] = 0;
        }
        return $item_info;
    }

    public function select_data($where = [],$field='*')
    {
        $where['goods_status'] = '10';
        $where['is_delete'] = '0';
        $list = $this->field($field)->where($where)->select();
        return $list;
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
     *
     * 加减商品销量 加减库存
     * @param $status 1) +  2) -
     * @param $goods_id
     * @param  $number 数量
    */
    public function update_sales_actual($goods_id , $number = 1 , $status = 1){

        $where = ['goods_id' => $goods_id];
        $info = $this->find_data($where, 'stock_num,sales_actual,sales_initial');
        switch ($status){ //stock_num
            case 1:
                if ($number > $info['stock_num'])
                    return false;
                $save = ['sales_initial' => $info['sales_initial'] + $number];
                $update = ['stock_num' => $info['stock_num'] - $number, 'sales_actual' => $info['sales_actual'] + $number];
                break;
            case 2:
                if ($number > $info['sales_actual'])
                    return false;
                $save = ['sales_initial' => $info['sales_initial'] - $number];
                $update = ['stock_num' => $info['stock_num'] + $number, 'sales_actual' => $info['sales_actual'] - $number];
                break;
        }
        $update['stock_num'] = $update['stock_num'] < 0 ? 0 :$update['stock_num'];
        $update['sales_actual'] = $update['sales_actual'] < 0 ? 0 :$update['sales_actual'];
        if ($update['sales_actual']){
            $this->update_data($where, $save);
        }
        return $this->update_data($where, $update);
    }

    /**
     * 获取猜你喜欢列表
     * @order
     */
    public function like_goods($uid)
    {
        $where['is_delete'] = '0';
        $goods_list = $this->where($where)->field('goods_id,image,goods_name,goods_price,line_price')->limit('4')->orderRaw('rand()')->select();
        $goods_list = empty($goods_list) ? [] : $goods_list;
        foreach ($goods_list as $k => $v){
            $goods_list[$k]['image'] = config('item_url') . $v['image'];
        }
        return $goods_list;
    }


    /*
     * hasOne  主表没有关联id  例如商品表 跟商品规格表
     * belongsTo  主表有关联ID 例如 商品表和商品分类表
     * hasMany  跟hasOne一样的用法
     * belongsToMany
     * */
    public function hass(){
        return $this->hasMany('Litestoregoodsspec', 'goods_id', 'goods_id', [], 'LEFT');
    }

    public function specss()
    {
        return $this->belongsToMany('Litestorespec', 'litestore_goods_spec_rel','spec_id','goods_id');
    }


}
