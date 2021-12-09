<?php

namespace app\admin\model;

use think\Model;

class Litestoreorder extends Model
{
    // 表名
    protected $name = 'litestore_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'pay_status_text',
        'pay_time_text',
        'freight_status_text',
        'freight_time_text',
        'receipt_status_text',
        'receipt_time_text',
        'order_status_text',
        'is_status_text'
    ];

    private $isStatus = [
        '1'=>'配送订单',
        '2'=>'自提订单',
    ];

    public function getIsStatusTextAttr($value, $data)
    {
        return $this->isStatus[$data['is_status']];
    }

    public function getPayStatusList()
    {
        return ['10' => __('Pay_status 10'), '20' => __('Pay_status 20')];
    }

    public function getFreightStatusList()
    {
        return ['10' => __('Freight_status 10'), '20' => __('Freight_status 20')];
    }

    public function getReceiptStatusList()
    {
        return ['10' => __('Receipt_status 10'), '20' => __('Receipt_status 20')];
    }

    public function getOrderStatusList()
    {
        return ['0' => __('Order_status 0'), '10' => __('Order_status 10'), '20' => __('Order_status 20'), '30' => __('Order_status 30'), '40' => __('Order_status 40'), '50' => __('Order_status 50')];
    }


    public function getPayStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_status']) ? $data['pay_status'] : '');
        $list = $this->getPayStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_time']) ? $data['pay_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getFreightStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['freight_status']) ? $data['freight_status'] : '');
        $list = $this->getFreightStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFreightTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['freight_time']) ? $data['freight_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getReceiptStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['receipt_status']) ? $data['receipt_status'] : '');
        $list = $this->getReceiptStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReceiptTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['receipt_time']) ? $data['receipt_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getOrderStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['order_status']) ? $data['order_status'] : '');
        $list = $this->getOrderStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setPayTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setFreightTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }

    protected function setReceiptTimeAttr($value)
    {
        return $value && !is_numeric($value) ? strtotime($value) : $value;
    }


    public function address()
    {
        return $this->hasOne('Litestoreorderaddress', 'order_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function addresss()
    {
        return $this->hasOne('Litestoreorderaddress', 'order_id', 'id')->bind('phone,site,name');
    }


    public function goods()
    {
        return $this->hasMany('Litestoreordergoods', 'order_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo('user');
    }

    public function users()
    {
        return $this->hasOne('User', 'id', 'user_id', [], 'LEFT')->setEagerlyType(0);
    }

    public function rebate()
    {
        return $this->hasOne('app\common\model\UserRebate', 'uid', 'user_id', [], 'LEFT')->setEagerlyType(0);
    }


    /**
     * 修改数据
     * */
    public function update_data($where, $save)
    {
        return $this->where($where)->update($save);
    }


    /**
     * 统计数据
     * @param  $where
     */
    public function goodsCount($where = [],$where2=[])
    {
        return $this->where($where)->where($where2)->count();
    }

    /**
     * 静态-统计数据
     * @param  $where
     */
    public static function getOrderCount($where = [],$where2=[])
    {
        return self::where($where)->where($where2)->count();
    }

    /**
     * 统计金额
     * @param  $where
     */
    public static function getOrderPayprice($where = [] ,$where2=[])
    {
        $res =  self::where($where)->where($where2)->sum('pay_price');
        return $res > 0 ? $res : 0;
    }


    /**
     * 统计订单
     *0=已取消,10=待付款,20=待发货，30待收货，40待评价，50交易完成 ,60 待分享
     * @param $order_type 10)普通订单 20）拼团订单
     */
    public function statisticsCount($order_type ,$school_id = 0)
    {
        //统计订单数量
        $order_where['order_type'] = $order_type;
        $order_where['type'] = 0;
        $school_id && $order_where['school_id'] = $school_id;
        $order_where['refund_status'] = array('neq', '10');
        $order_where['total_price'] = ['egt' ,0];
        
        $result['total_number'] = $this->goodsCount($order_where); //全部

        $order_where['order_status'] = 10;
        $result['no_pay_number'] = $this->goodsCount($order_where);//带支付

        //订单状态:
        $order_where['order_status'] = 20;
        $result['no_send_goods_number'] = $this->goodsCount($order_where);//待发货

        $order_where['order_status'] = 40;
        $result['no_evaluate_number'] = $this->goodsCount($order_where);//待评价

        $order_where['order_status'] = 50;
        $result['finish_number'] = $this->goodsCount($order_where);//已完成

        $order_where['order_status'] = 0;
        $result['cancel_number'] = $this->goodsCount($order_where);//已取消

        $order_where['order_status'] = 60;
        $result['no_share_number'] = $this->goodsCount($order_where);//待分享

        //待收货
        $order_where['order_status'] = 30;
        $order_where['is_status'] = 1;
        $result['no_take_over_number'] = $this->goodsCount($order_where);//待收货

        $order_where['is_status'] = 2;
        $result['no_mention'] = $this->goodsCount($order_where);//待收货

        return $result;
    }
}
