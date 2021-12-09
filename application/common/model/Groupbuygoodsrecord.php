<?php
namespace app\common\model;
use think\Model;

/**
 * 拼团管理模型
 */
class Groupbuygoodsrecord extends Model
{
    protected $name = 'groupbuy_goods_record';
    /**
     * 保存添加的拼团活动
     */

    public function addGroupbuy($params){
        return $this->allowField(true)->insertGetId($params);
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
            ->fild($field)
            ->select();

        return $groupbuy_list;
    }


    /**
     * 删除
     */
    public function delGroupbuyrecord($condition){
        return $this->where($condition)->delete();
    }


    //查询商品是否存在
    public function find_data($where ,$field){
        return $this->where($where)->field($field)->find();
    }


}
