<?php

namespace app\common\model;

use think\Model;
use traits\model\SoftDelete;

class Litestoreorderrefund extends Model
{
    // 表名
    protected $name = 'litestore_order_refund';

    use SoftDelete;
    protected $deleteTime = 'delete_time';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $updateTime = false;

    // 定义时间戳字段名
    protected $apply_after_sale_time = 'apply_after_sale_time';

    /*获取分页列表
 * @param  $where 查询条件
 * @param  $field 需要查询字段
 * @param  $order 排序字段
 * @param  $page 第几页
 * @param  $pagesize 每页几条数据
 * */

    public function getPageList($where = [], $field = '*', $order = 'id desc', $page = 1, $pagesize = 10)
    {
        $list = $this->where($where)->field($field)
            ->order($order)->limit(($page - 1) * $pagesize, $pagesize)
            ->select();

        if ($list != null) {
            $model = model('Litestoreordergoods');
            foreach ($list as $k => $value) {
                $order_goods = $model->select_data(['order_id' => $value['order_id']], 'goods_name,images,key_name,total_num,goods_price');
                $list[$k]['sub'] = $order_goods;
            }
        }
        return $list;
    }


    /*
     * 获取单条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function find_data($where = [], $field = '*')
    {
        return $this->where($where)->field($field)->find();
    }

    /*
     * 查询多条数据
     * @param  $where 查询条件
     * @param  $field 需要查询字段
     * @param  $order 排序字段
     */
    public function select_data($where, $field = '*')
    {
        return $this->where($where)->field($field)->select();
    }


    /*
     * 添加数据
     * @param  $param 查询条件
     */
    public function add_data($param)
    {
        return $this->allowField(true)->save($param);
//         return $this->getLastInsID();

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

    public function goods()
    {
        return $this->hasMany('Litestoreordergoods', 'id', 'order_goods_id');
    }
}