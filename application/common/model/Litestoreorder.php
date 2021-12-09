<?php

namespace app\common\model;

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


    public function address()
    {
        return $this->hasOne('Litestoreorderaddress', 'order_id', 'id');
    }
    public function user_address()
    {
        return $this->hasOne('Litestoreorderaddress', 'user_id', 'user_id');
    }

    public function goods()
    {
        return $this->hasMany('Litestoreordergoods', 'order_id', 'id');
    }
    public function orderGoods()
    {
        return $this->belongsTo('Litestoreordergoods', 'id', 'order_id', [], 'LEFT')->setEagerlyType(0);
    }

    public function user()
    {
        return $this->belongsTo('user');
    }

    public function userAddress()
    {
        return $this->hasOne('Litestoreaddress','user_id','user_id');
    }

    /*
     * 添加数据
     * @param  $param 查询条件
     */
    public function add_data($param)
    {
        return $this->insertGetId($param);
    }

    public function getField($where = array(),$data)
    {
        return $this->where($where)->value($data);
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


    //查找一条数据

    public function find_data($where = [], $field = '*')
    {
        $where['is_del'] = 0;
        return $this->where($where)->field($field)->find();
    }

    //查找多条数据

    public function select_data($where = [], $field = '*', $order = 'id desc', $page = 1, $pagesize = 10)
    {
        $where['is_del'] = 0;
        return $this->where($where)->field($field)->order($order)
            ->limit(($page - 1) * $pagesize, $pagesize)->select();
    }


    /* 获取多条数据
     * @param $where
     * @param $field
     * @param $order
     * @return array
     */
    public function getPageDate($where = array(), $field = '*', $order = 'id desc',$page = 1, $pagesize = 10)
    {
        $where['is_del'] = '0';
        $list = $this->where($where)->field($field)->order($order)
            ->limit(($page - 1) * $pagesize, $pagesize)->select();

        if ($list != null) {
            $model = model('Litestoreordergoods');
            foreach ($list as $k => $value) {
                $order_goods = $model->select_data(['order_id' => $value['id']], 'goods_id,goods_name,images,key_name,total_num,is_refund,goods_price');
                $list[$k]['sub'] = $order_goods;
            }
        }
        return $list;
    }

    /**
     * 查询多条数据
     * @param array $where
     * @param string $field
     * @param string $order
     * @return mixed
     */
    public function select_all($where = [],$field = '*',$order ='id desc')
    {
        $where['is_del'] = 0;
        return $this->where($where)->field($field)->order($order)->select();
    }


    /*
     * 确认收货
     * @param $where
     * @return bool
     */
    public function receipt($where)
    {
        
        $where['order_status'] = '30';
        $data['order_status'] = '40';
        $data['receipt_status'] = 20;
        $data['receipt_time'] = time();
        if ($this->where($where)->update($data) !== false) {
            return true;
        } else {
            return false;
        }
    }
}
