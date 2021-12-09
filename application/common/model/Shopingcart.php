<?php

namespace app\common\model;

use think\Model;

/**
 * 购物车模型
 */
class Shopingcart Extends Model
{

    // 表名
    protected $name = 'shoping_cart';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';

    /*获取分页列表
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     * @param  $page 第几页
     * @param  $pagesize 每页几条数据
     * */

    public function getPageList($where = [], $field = '*', $order = 'createtime desc', $page = 1, $pagesize = 10)
    {
        return $this->where($where)->field($field)->order($order)->limit(($page - 1) * $pagesize, $pagesize)->select();
    }


    /*
     * 获取单条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function find_data($where = [], $field = '*', $order = 'createtime desc')
    {
        return $this->where($where)->field($field)->order($order)->find();
    }

    /*
     * 查询多条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function select_data($where, $field = '*', $order = 'createtime desc')
    {
        return $this->where($where)->field($field)->order($order)->select();
    }


    /*
     * 添加数据
     * @param  $param 查询条件
     */
    public function add_data($param)
    {
        return $this->allowField(true)->save($param);
    }

    /**
     * 修改信息
     * @param array $where
     * @param array $data
     * @return bool
     */
    public function update_data($where = [], $data = [])
    {
        if (empty($where)) {
            return false;
        }
        return $this->where($where)->update($data);
    }


    /**
     * 删除
     * @param $where
     * @return bool|mixed
     */
    public function delete_data($where)
    {
        if (empty($where)) {

            return false;
        }
        return $this->where($where)->delete();
    }


    /*
   * 统计购物车数量
   * @param $uid 用户ID
   * */
    public function getShopingCartNum($where = [] ,$school_id = '')
    {
        $where['school_id'] = 0;
        $school_id && $where['school_id'] = $school_id;
        return $this->where($where)->count();
    }

    /**
     * image获取器
     */
    public function getImageAttr($value)
    {
        return $value ? config('item_url') . $value : '';
    }


    /**
     * 获取学校购物车信息
     * @param $where
     * @return float|int
     * @throws \think\exception\DbException
     */
    public static function getShopingCartTotalPrice($where)
    {

        $total_price = 0;
        $shoping_cart_ids = '';
        $total_num = 0;
        if(!$where['uid'])
            return compact('total_price', 'shoping_cart_ids','total_num');

        $field = 'id,goods_spec_id,goods_id,num,goods_name,image,key_name';

        $list = self::where($where)->field($field)->select();

        if (count($list) > 0) {
            foreach ($list as $k => $v) {
                $wheres['goods_id'] = $v->goods_id;
                $wheres['goods_spec_id'] = $v->goods_spec_id;
                $goods_spec = model('Litestoregoodsspec')->find_data($wheres, 'goods_price');
                $total_price += $goods_spec->goods_price * $v->num;
                $v->goods_price = $goods_spec->goods_price;
                $shoping_cart_ids .=$v->id."," ;
                $total_num += $v->num;
            }
        }
        return compact('total_num','total_price', 'shoping_cart_ids','list');
    }
}