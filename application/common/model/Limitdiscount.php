<?php

namespace app\common\model;

use think\Cache;
use think\Model;

/**
 * 限时折扣模型
 */
class Limitdiscount extends Model
{
    protected $name = 'limit_discount';

    /**
     * 读取限时折扣列表
     */
    public function getLimitDiscountList($where,$sort='',$order='',$offset=NUll,$limit=0,$field='*'){
            $limitdiscount_list = $this->where($where)
            ->order($sort, $order)
            ->limit($offset, $limit)
            ->select();
        foreach ($limitdiscount_list as $k=>$v){
            $limitdiscount_list[$k]=$this->getLimitDiscountExtendInfo($limitdiscount_list[$k]);
        }
        return $limitdiscount_list;
    }

    /**
     * 获取限时折扣扩展信息，包括状态文字和是否可编辑状态
     * @param array $xianshi_info
     * @return string
     *
     */
    public function getLimitDiscountExtendInfo($limmitdiscount_info) {
        if($limmitdiscount_info['start_time'] > $_SERVER['REQUEST_TIME']) {
            $limmitdiscount_info['status_text'] = '未开始';
        }else if($limmitdiscount_info['start_time'] <= $_SERVER['REQUEST_TIME']&&$limmitdiscount_info['end_time']>$_SERVER['REQUEST_TIME']){
            $limmitdiscount_info['status_text'] = '进行中';
        }else {
            $limmitdiscount_info['status_text'] = '已结束';
        }
        if($limmitdiscount_info['status'] == 1 && $limmitdiscount_info['end_time'] > $_SERVER['REQUEST_TIME']) {
            $limmitdiscount_info['editable'] = true;
        } else {
            $limmitdiscount_info['editable'] = false;
        }

        return $limmitdiscount_info;
    }

    /**
     * 保存添加的限时折扣活动
     */

    public function addLimitDiscount($params){
        $params['start_time']=strtotime($params['start_time']);
        $params['end_time']=strtotime($params['end_time']);
        $params['status']=1;
        $params['add_time'] = time();
        return $this->allowField(true)->save($params);

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
        $update =  $this->where($condition)->update($params);
        return $update;
    }

    /**
     * 删除
     */
    public function delLimitDiscount($condition){
        $list=$this->getLimitDiscountList($condition);
        $limit_discount_id_string = '';
        if(!empty($list)) {
            foreach ($list as $value) {
                $limit_discount_id_string .= $value['id'] . ',';
            }
        }
        //删除限时折扣商品
        if( $limit_discount_id_string !== '') {
            $model_limit_discount_goods = Model('limit_discount_goods');
            $model_limit_discount_goods ->delLimitDiscountGoods(array('limit_discount_id'=>array('in', $limit_discount_id_string)));
        }
        return $this->where($condition)->delete();
    }


    //获取商品列表
    public function select_data($where = [],$field = '*',$order='id desc'){
        $list = $this->field($field)->where($where)->order($order)->select();
        return $list;
    }


}
